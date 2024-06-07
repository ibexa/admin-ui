<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\ContentType;

use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use JMS\TranslationBundle\Annotation\Desc;

class ViewTab extends AbstractEventDispatchingTab implements OrderedTabInterface
{
    public const URI_FRAGMENT = 'ibexa-tab-content-type-view-details';

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'view';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        /** @Desc("View") */
        return $this->translator->trans('tab.name.view', [], 'ibexa_content_type');
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 100;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return '@ibexadesign/content_type/tab/view.html.twig';
    }

    /**
     * @param mixed[] $contextParameters
     *
     * @return mixed[]
     */
    public function getTemplateParameters(array $contextParameters = []): array
    {
        return $contextParameters;
    }
}

class_alias(ViewTab::class, 'EzSystems\EzPlatformAdminUi\Tab\ContentType\ViewTab');
