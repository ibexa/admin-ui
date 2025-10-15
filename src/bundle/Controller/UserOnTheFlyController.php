<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\ActionDispatcher\CreateUserOnTheFlyDispatcher;
use Ibexa\AdminUi\Form\ActionDispatcher\EditUserOnTheFlyDispatcher;
use Ibexa\AdminUi\View\CreateUserOnTheFlyView;
use Ibexa\AdminUi\View\EditContentOnTheFlySuccessView;
use Ibexa\AdminUi\View\EditUserOnTheFlyView;
use Ibexa\ContentForms\Data\Mapper\UserCreateMapper;
use Ibexa\ContentForms\Data\Mapper\UserUpdateMapper;
use Ibexa\ContentForms\Form\Type\User\UserCreateType;
use Ibexa\ContentForms\Form\Type\User\UserUpdateType;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\Exceptions as ApiException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Core\MVC\Symfony\View\BaseView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UserOnTheFlyController extends Controller
{
    public function __construct(
        private readonly LanguageService $languageService,
        private readonly LocationService $locationService,
        private readonly UserService $userService,
        private readonly PermissionResolver $permissionResolver,
        private readonly CreateUserOnTheFlyDispatcher $createUserActionDispatcher,
        private readonly EditUserOnTheFlyDispatcher $editUserActionDispatcher
    ) {
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function createUserAction(
        Request $request,
        string $languageCode,
        ContentType $contentType,
        Location $parentLocation
    ): BaseView|Response {
        $language = $this->languageService->loadLanguage($languageCode);
        $parentGroup = $this->userService->loadUserGroup($parentLocation->getContentId());

        $data = (new UserCreateMapper())->mapToFormData($contentType, [$parentGroup], [
            'mainLanguageCode' => $language->languageCode,
        ]);
        $form = $this->createForm(UserCreateType::class, $data, [
            'languageCode' => $language->languageCode,
            'mainLanguageCode' => $language->languageCode,
            'struct' => $data,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->getClickedButton()) {
            $this->createUserActionDispatcher->dispatchFormAction($form, $data, $form->getClickedButton()->getName());
            if ($response = $this->createUserActionDispatcher->getResponse()) {
                return $response;
            }
        }

        return new CreateUserOnTheFlyView('@ibexadesign/ui/on_the_fly/user_create_on_the_fly.html.twig', [
            'form' => $form->createView(),
            'language' => $language,
            'content_type' => $contentType,
            'parent_location' => $parentLocation,
        ]);
    }

    public function hasCreateAccessAction(
        string $languageCode,
        ContentType $contentType,
        Location $parentLocation
    ): JsonResponse {
        $response = new JsonResponse();

        try {
            $userCreateStruct = $this->createContentCreateStruct($contentType, $languageCode);
            $locationCreateStruct = $this->locationService->newLocationCreateStruct(
                $parentLocation->getId()
            );

            if (!$this->permissionResolver->canUser('content', 'create', $userCreateStruct, [$locationCreateStruct])) {
                throw new UnauthorizedException(
                    'content',
                    'create',
                    [
                        'contentTypeIdentifier' => $contentType->getIdentifier(),
                        'parentLocationId' => $locationCreateStruct->parentLocationId,
                        'languageCode' => $languageCode,
                    ]
                );
            }

            if (!$this->permissionResolver->canUser('content', 'publish', $userCreateStruct, [$locationCreateStruct])) {
                throw new UnauthorizedException(
                    'content',
                    'publish',
                    [
                        'contentTypeIdentifier' => $contentType->getIdentifier(),
                        'parentLocationId' => $locationCreateStruct->parentLocationId,
                        'languageCode' => $languageCode,
                    ]
                );
            }

            $response->setData([
                'access' => true,
            ]);
        } catch (ApiException\UnauthorizedException $exception) {
            $response->setData([
                'access' => false,
                'message' => $exception->getMessage(),
            ]);
        }

        return $response;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function editUserAction(
        Request $request,
        string $languageCode,
        int $contentId,
        int $versionNo,
        int $locationId
    ): BaseView {
        $user = $this->userService->loadUser($contentId, [$languageCode]);
        $location = $this->locationService->loadLocation($locationId);
        $contentType = $user->getContentType();
        $language = $this->languageService->loadLanguage($languageCode);

        $contentUpdate = (new UserUpdateMapper())->mapToFormData($user, $contentType, [
            'languageCode' => $languageCode,
        ]);

        $form = $this->createForm(
            UserUpdateType::class,
            $contentUpdate,
            [
                'location' => $location,
                'languageCode' => $languageCode,
                'mainLanguageCode' => $user->getContentInfo()->getMainLanguageCode(),
                'struct' => $contentUpdate,
            ]
        );

        $form->handleRequest($request);

        if (null === $location && $user->getContentInfo()->isPublished()) {
            // assume main location if no location was provided
            $location = $user->getContentInfo()->getMainLocation();
        }

        if (null !== $location && $location->getContentId() !== $user->getUserId()) {
            throw new InvalidArgumentException(
                'Location',
                'The provided Location does not belong to the selected content'
            );
        }

        if ($form->isSubmitted() && $form->isValid() && null !== $form->getClickedButton()) {
            $this->editUserActionDispatcher->dispatchFormAction(
                $form,
                $form->getData(),
                $form->getClickedButton()->getName(),
                ['referrerLocation' => $location]
            );

            if ($this->editUserActionDispatcher->getResponse()) {
                $view = new EditContentOnTheFlySuccessView('@ibexadesign/ui/on_the_fly/user_edit_response.html.twig');
                $view->addParameters([
                    'locationId' => $location->getId(),
                ]);

                return $view;
            }
        }

        return $this->buildEditView($user, $language, $location, $form, $contentType);
    }

    private function createContentCreateStruct(
        ContentType $contentType,
        string $language
    ): ContentCreateStruct {
        return $this->userService->newUserCreateStruct(
            'permission_check',
            'permission_check',
            'permission_check',
            $language,
            $contentType
        );
    }

    private function buildEditView(
        Content $content,
        Language $language,
        ?Location $location,
        FormInterface $form,
        ContentType $contentType
    ): EditUserOnTheFlyView {
        $view = new EditUserOnTheFlyView('@ibexadesign/ui/on_the_fly/user_edit_on_the_fly.html.twig');

        $view->setContent($content);
        $view->setLanguage($language);
        $view->setLocation($location);
        $view->setForm($form);

        $view->addParameters([
            'content' => $content,
            'location' => $location,
            'language' => $language,
            'content_type' => $contentType,
            'form' => $form->createView(),
        ]);

        return $view;
    }
}
