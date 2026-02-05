<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Specification\UserExists;
use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\ConditionalTabInterface;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class AuthorsTab extends AbstractEventDispatchingTab implements OrderedTabInterface, ConditionalTabInterface
{
    public const string URI_FRAGMENT = 'ibexa-tab-location-view-authors';

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        private readonly UserService $userService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);
    }

    public function getIdentifier(): string
    {
        return 'authors';
    }

    public function getName(): string
    {
        /** @Desc("Authors") */
        return $this->translator->trans('tab.name.authors', [], 'ibexa_locationview');
    }

    public function getOrder(): int
    {
        return 650;
    }

    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/authors.html.twig';
    }

    public function getTemplateParameters(array $contextParameters = []): array
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
        $content = $contextParameters['content'];

        $versionInfo = $content->getVersionInfo();
        $contentInfo = $versionInfo->getContentInfo();

        $viewParameters = [
            'content_info' => $contentInfo,
            'version_info' => $versionInfo,
        ];

        $this->supplyCreator($viewParameters, $contentInfo);
        $this->supplyLastContributor($viewParameters, $versionInfo);

        return array_replace($contextParameters, $viewParameters);
    }

    public function evaluate(array $parameters): bool
    {
        return true;
    }

    /**
     * @param array<string, mixed|null> $parameters
     */
    private function supplyLastContributor(array &$parameters, VersionInfo $versionInfo): void
    {
        $parameters['last_contributor'] = null;
        if ((new UserExists($this->userService))->isSatisfiedBy($versionInfo->creatorId)) {
            $parameters['last_contributor'] = $this->userService->loadUser($versionInfo->creatorId);
        }
    }

    /**
     * @param array<string, mixed|null> $parameters
     */
    private function supplyCreator(array &$parameters, ContentInfo $contentInfo): void
    {
        $parameters['creator'] = null;

        try {
            $ownerId = $contentInfo->getOwner()->getUserId();
        } catch (NotFoundException $exception) {
            return;
        }

        if ((new UserExists($this->userService))->isSatisfiedBy($ownerId)) {
            $parameters['creator'] = $this->userService->loadUser($ownerId);
        }
    }
}
