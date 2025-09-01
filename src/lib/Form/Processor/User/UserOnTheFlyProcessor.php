<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Processor\User;

use Ibexa\ContentForms\Data\User\UserCreateData;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\ContentForms\Form\Processor\User\UserUpdateFormProcessor;
use Ibexa\Contracts\AdminUi\Event\UserOnTheFlyEvents;
use Ibexa\Contracts\Core\Repository\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final readonly class UserOnTheFlyProcessor implements EventSubscriberInterface
{
    public function __construct(
        private UserService $userService,
        private Environment $twig,
        private UserUpdateFormProcessor $innerUserUpdateFormProcessor
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserOnTheFlyEvents::USER_CREATE_PUBLISH => ['processCreate', 10],
            UserOnTheFlyEvents::USER_EDIT_PUBLISH => ['processEdit', 10],
        ];
    }

    public function processCreate(FormActionEvent $event): void
    {
        $data = $event->getData();

        if (!$data instanceof UserCreateData) {
            return;
        }

        $form = $event->getForm();
        $languageCode = $form->getConfig()->getOption('languageCode');

        $this->setContentFields($data, $languageCode);
        $user = $this->userService->createUser($data, $data->getParentGroups());

        $event->setResponse(
            new Response(
                $this->twig->render('@ibexadesign/ui/on_the_fly/user_create_response.html.twig', [
                    'locationId' => $user->getContentInfo()->getMainLocationId(),
                ])
            )
        );
    }

    public function processEdit(FormActionEvent $event): void
    {
        // Rely on User Form Processor from ContentForms to avoid unncessary code duplication
        $this->innerUserUpdateFormProcessor->processUpdate($event);

        $referrerLocation = $event->getOption('referrerLocation');

        // We only need to change the response so it's compatible with UDW
        $event->setResponse(
            new Response(
                $this->twig->render('@ibexadesign/ui/on_the_fly/user_edit_response.html.twig', [
                    'locationId' => $referrerLocation->id,
                ])
            )
        );
    }

    private function setContentFields(UserCreateData $data, string $languageCode): void
    {
        foreach ($data->getFieldsData() as $fieldDefIdentifier => $fieldData) {
            $data->setField($fieldDefIdentifier, $fieldData->getValue(), $languageCode);
        }
    }
}
