<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\LocationView;

use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class PreviewTab extends AbstractEventDispatchingTab implements OrderedTabInterface
{
    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);
    }

    public function getIdentifier(): string
    {
        return 'preview';
    }

    public function getName(): string
    {
        /** @Desc("Quick preview") */
        return $this->translator->trans('tab.name.preview', [], 'ibexa_locationview');
    }

    public function getOrder(): int
    {
        return 90;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/preview.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateParameters(array $contextParameters = []): array
    {
        return array_replace($contextParameters, [
            'language' => 'eng-GB', // TODO: get this language from user settings Kuba's working on
            'siteaccess' => 'site', // TODO: get from somewhere some kind of default siteaccess
        ]);
    }
}

class_alias(PreviewTab::class, 'EzSystems\EzPlatformAdminUi\Tab\LocationView\PreviewTab');
