<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use DateTime;
use DateTimeInterface;
use Ibexa\AdminUi\Specification\SiteAccess\IsAdmin;
use Ibexa\Contracts\AdminUi\Notification\NotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Core\MVC\Symfony\Security\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CredentialsExpirationWarningListener implements EventSubscriberInterface
{
    private NotificationHandlerInterface $notificationHandler;

    private TranslatorInterface $translator;

    private UrlGeneratorInterface $urlGenerator;

    private UserService $userService;

    /** @var string[][] */
    private array $siteAccessGroups;

    public function __construct(
        NotificationHandlerInterface $notificationHandler,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        UserService $userService,
        array $siteAccessGroups
    ) {
        $this->notificationHandler = $notificationHandler;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->siteAccessGroups = $siteAccessGroups;
        $this->userService = $userService;
    }

    public function onAuthenticationSuccess(InteractiveLoginEvent $event): void
    {
        if (!$this->isAdminSiteAccess($event->getRequest())) {
            return;
        }

        $user = $event->getAuthenticationToken()->getUser();
        if (!($user instanceof UserInterface)) {
            return;
        }

        $apiUser = $user->getAPIUser();

        $passwordInfo = $this->userService->getPasswordInfo($apiUser);
        if ($passwordInfo->hasExpirationWarningDate()) {
            $expirationWarningDate = $passwordInfo->getExpirationWarningDate();
            if ($expirationWarningDate <= new DateTime()) {
                $this->generateNotification($passwordInfo->getExpirationDate());
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => ['onAuthenticationSuccess', 12],
        ];
    }

    private function generateNotification(DateTimeInterface $passwordExpiresAt): void
    {
        $passwordExpiresIn = (new DateTime())->diff($passwordExpiresAt);

        if ($passwordExpiresIn->d > 0) {
            $warning = $this->translator->trans(
                /** @Desc("Your current password will expire in %days% day(s). You can change it in User settings/My account settings.") */
                'authentication.credentials_expire_in.warning',
                [
                    '%days%' => $passwordExpiresIn->d + ($passwordExpiresIn->h >= 12 ? 1 : 0),
                    '%url%' => $this->urlGenerator->generate('ibexa.user_profile.change_password'),
                ],
                'messages'
            );
        } else {
            $warning = $this->translator->trans(
                /** @Desc("Your current password will expire today. You can change it in User settings/My account settings.") */
                'authentication.credentials_expire_today.warning',
                [
                    '%url%' => $this->urlGenerator->generate('ibexa.user_profile.change_password'),
                ],
                'messages'
            );
        }

        $this->notificationHandler->warning($warning);
    }

    private function isAdminSiteAccess(Request $request): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy($request->attributes->get('siteaccess'));
    }
}
