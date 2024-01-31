<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\AdminUi\Specification\UserProfile\IsProfileAvailable;
use Ibexa\AdminUi\UserProfile\UserProfileConfigurationInterface;
use Ibexa\ContentForms\Data\User\UserUpdateData;
use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\Repository\Values\User\UserUpdateStruct;
use Ibexa\Core\FieldType\User\Type as UserFieldType;
use Ibexa\Core\Repository\Repository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserProfileListener implements EventSubscriberInterface
{
    private UrlGeneratorInterface $urlGenerator;

    private Repository $repository;

    private PermissionResolver $permissionResolver;

    private ContentService $contentService;

    private UserService $userService;

    private UserProfileConfigurationInterface $configuration;

    private RequestStack $requestStack;

    public function __construct(
        Repository $repository,
        PermissionResolver $permissionResolver,
        ContentService $contentService,
        UserService $userService,
        UrlGeneratorInterface $urlGenerator,
        UserProfileConfigurationInterface $configuration,
        RequestStack $requestStack
    ) {
        $this->repository = $repository;
        $this->permissionResolver = $permissionResolver;
        $this->contentService = $contentService;
        $this->userService = $userService;
        $this->urlGenerator = $urlGenerator;
        $this->configuration = $configuration;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentFormEvents::USER_UPDATE => ['onUserUpdate', 30],
            ContentFormEvents::USER_CANCEL => ['onUserCancel', 30],
        ];
    }

    public function onUserUpdate(FormActionEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!($data instanceof UserUpdateData) || !$this->isSupported($data)) {
            return;
        }

        $user = $data->user;
        $updateStruct = $this->createUpdateStruct($data, $form->getConfig()->getOption('languageCode'));

        // user / selfedit policy is enough to edit own profile (checked in
        $this->repository->sudo(function () use ($user, $updateStruct): void {
            $this->userService->updateUser($user, $updateStruct);
        });

        $event->setResponse($this->createRedirectToUserProfile($user));
        $event->stopPropagation();
    }

    public function onUserCancel(FormActionEvent $event): void
    {
        $data = $event->getData();
        if (!($data instanceof UserUpdateData) || !$this->isSupported($data)) {
            return;
        }

        $event->setResponse($this->createRedirectToUserProfile($data->user));
        $event->stopPropagation();
    }

    private function createRedirectToUserProfile(User $user): RedirectResponse
    {
        return new RedirectResponse(
            $this->urlGenerator->generate(
                'ibexa.user.profile.view',
                [
                    'userId' => $user->getUserId(),
                ]
            )
        );
    }

    private function isSupported(UserUpdateData $data): bool
    {
        return $this->isUserProfileUpdate($data) &&
            $this->canEditUserProfile($data->user) &&
            $this->doesOriginateFromProfileEditing();
    }

    private function createUpdateStruct(UserUpdateData $data, string $languageCode): UserUpdateStruct
    {
        $updateStruct = $this->userService->newUserUpdateStruct();
        $updateStruct->contentUpdateStruct = $this->contentService->newContentUpdateStruct();

        foreach ($data->fieldsData as $fieldDefIdentifier => $fieldData) {
            $updateStruct->contentUpdateStruct->setField($fieldDefIdentifier, $fieldData->value, $languageCode);
        }

        return $updateStruct;
    }

    private function isUserProfileUpdate(UserUpdateData $data): bool
    {
        $currentUserId = $this->permissionResolver->getCurrentUserReference()->getUserId();
        if ($currentUserId !== $data->user->getUserId()) {
            return false;
        }

        foreach ($data->fieldsData as $fieldData) {
            if ($fieldData->getFieldTypeIdentifier() === UserFieldType::class) {
                return false;
            }
        }

        return true;
    }

    private function canEditUserProfile(User $user): bool
    {
        return
            $this->permissionResolver->canUser('user', 'selfedit', $user)
            && (new IsProfileAvailable($this->configuration))->isSatisfiedBy($user);
    }

    private function doesOriginateFromProfileEditing(): bool
    {
        $request = $this->requestStack->getMainRequest();
        if ($request === null) {
            return false;
        }

        return $request->attributes->get('_route') === 'ibexa.user.profile.edit';
    }
}
