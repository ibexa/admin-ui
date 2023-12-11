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
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

final class ProfileEditController extends Controller
{
    private Repository $repository;

    private UserService $userService;

    private LocationService $locationService;

    private UserProfileConfigurationInterface $configuration;

    private PermissionResolver $permissionResolver;

    private LanguageService $languageService;

    private ActionDispatcherInterface $userActionDispatcher;

    private GroupedContentFormFieldsProviderInterface $groupedContentFormFieldsProvider;

    public function __construct(
        Repository $repository,
        UserService $userService,
        LocationService $locationService,
        UserProfileConfigurationInterface $configuration,
        PermissionResolver $permissionResolver,
        LanguageService $languageService,
        ActionDispatcherInterface $userActionDispatcher,
        GroupedContentFormFieldsProviderInterface $groupedContentFormFieldsProvider
    ) {
        $this->repository = $repository;
        $this->userService = $userService;
        $this->locationService = $locationService;
        $this->configuration = $configuration;
        $this->permissionResolver = $permissionResolver;
        $this->languageService = $languageService;
        $this->userActionDispatcher = $userActionDispatcher;
        $this->groupedContentFormFieldsProvider = $groupedContentFormFieldsProvider;
    }

    /**
     * @return \Ibexa\ContentForms\User\View\UserUpdateView|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, ?string $languageCode)
    {
        $user = $this->userService->loadUser($this->permissionResolver->getCurrentUserReference()->getUserId());
        if (!$this->isUserProfileAvailable($user)) {
            throw $this->createNotFoundException();
        }

        if (!$this->permissionResolver->canUser('user', 'selfedit', $user)) {
            throw $this->createAccessDeniedException();
        }

        $languageCode ??= $user->contentInfo->mainLanguageCode;

        $data = (new UserUpdateMapper())->mapToFormData($user, $user->getContentType(), [
            'languageCode' => $languageCode,
            'filter' => static fn (Field $field): bool => $field->fieldTypeIdentifier !== 'ezuser',
        ]);

        $form = $this->createForm(
            UserUpdateType::class,
            $data,
            [
                'languageCode' => $languageCode,
                'mainLanguageCode' => $user->contentInfo->mainLanguageCode,
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
                (int)$user->versionInfo->contentInfo->mainLocationId
            )
        );

        $parentLocation = null;
        try {
            $parentLocation = $this->locationService->loadLocation($location->parentLocationId);
        } catch (UnauthorizedException $e) {
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
