<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\LocationView;

use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\LocationService;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class SubItemsTab extends AbstractEventDispatchingTab implements OrderedTabInterface
{
    private LocationService $locationService;

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        LocationService $locationService
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);

        $this->locationService = $locationService;
    }

    public const URI_FRAGMENT = 'ibexa-tab-location-view-sub-items';

    public function getIdentifier(): string
    {
        return 'sub_items';
    }

    public function getName(): string
    {
        /** @Desc("Sub-items") */
        return $this->translator->trans('tab.name.sub_items', [], 'ibexa_locationview');
    }

    public function getOrder(): int
    {
        return 200;
    }

    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/sub_items.html.twig';
    }

    public function isEnabled(array $parameters): bool
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType */
        $contentType = $parameters['contentType'];

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
        $location = $parameters['location'];

        $hasChildren = $this->locationService->getLocationChildCount($location) > 0;

        return $contentType->isContainer && !$hasChildren;
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

        return array_replace($contextParameters, $viewParameters);
    }
}
