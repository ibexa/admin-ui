<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\User;

use Ibexa\AdminUi\Specification\UserProfile\IsProfileAvailable;
use Ibexa\AdminUi\UserProfile\UserProfileConfigurationInterface;
use Ibexa\ContentForms\Data\Mapper\UserUpdateMapper;
use Ibexa\ContentForms\Form\ActionDispatcher\ActionDispatcherInterface;
use Ibexa\ContentForms\Form\Type\User\UserUpdateType;
use Ibexa\ContentForms\User\View\UserUpdateView;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProviderInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Core\FieldType\User\Type as UserFieldType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ProfileEditController extends Controller
{
    public function __construct(
        private readonly Repository $repository,
        private readonly UserService $userService,
        private readonly LocationService $locationService,
        private readonly UserProfileConfigurationInterface $configuration,
        private readonly PermissionResolver $permissionResolver,
        private readonly LanguageService $languageService,
        private readonly ActionDispatcherInterface $userActionDispatcher,
        private readonly GroupedContentFormFieldsProviderInterface $groupedContentFormFieldsProvider
    ) {
    }

    public function editAction(Request $request, ?string $languageCode): UserUpdateView|Response
    {
        $user = $this->userService->loadUser(
            $this->permissionResolver->getCurrentUserReference()->getUserId()
        );

        if (!$this->isUserProfileAvailable($user)) {
            throw $this->createNotFoundException();
        }

        if (!$this->permissionResolver->canUser('user', 'selfedit', $user)) {
            throw $this->createAccessDeniedException();
        }

        $languageCode ??= $user->getContentInfo()->getMainLanguageCode();

        $data = (new UserUpdateMapper())->mapToFormData($user, $user->getContentType(), [
            'languageCode' => $languageCode,
            'filter' => static fn (Field $field): bool => $field->getFieldTypeIdentifier() !== UserFieldType::FIELD_TYPE_IDENTIFIER,
        ]);

        $form = $this->createForm(
            UserUpdateType::class,
            $data,
            [
                'languageCode' => $languageCode,
                'mainLanguageCode' => $user->getContentInfo()->getMainLanguageCode(),
                'struct' => $data,
                //This is **the only permissible case for nullable content**. It stems from the fact that
                //one's own user profile editing shouldn't undergo permissions checks. Otherwise, it
                //would be impossible to composer role that would allow that out of the box for all users
                //that are assigned to it.
                'content' => null,
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $form instanceof Form && null !== $form->getClickedButton()) {
            $this->userActionDispatcher->dispatchFormAction($form, $data, $form->getClickedButton()->getName());
            if ($response = $this->userActionDispatcher->getResponse()) {
                return $response;
            }
        }

        $location = $this->repository->sudo(
            fn (): Location => $this->locationService->loadLocation(
                (int)$user->getVersionInfo()->getContentInfo()->getMainLocationId()
            )
        );

        $parentLocation = null;
        try {
            $parentLocation = $this->locationService->loadLocation($location->parentLocationId);
        } catch (UnauthorizedException) {
            //do nothing
        }

        return new UserUpdateView(
            null,
            [
                'form' => $form->createView(),
                'language_code' => $languageCode,
                'language' => $this->languageService->loadLanguage($languageCode),
                'content_type' => $user->getContentType(),
                'user' => $user,
                'location' => $location,
                'parent_location' => $parentLocation,
                'grouped_fields' => $this->groupedContentFormFieldsProvider->getGroupedFields(
                    $form->get('fieldsData')->all()
                ),
            ]
        );
    }

    private function isUserProfileAvailable(User $user): bool
    {
        return (new IsProfileAvailable($this->configuration))->isSatisfiedBy($user);
    }
}
