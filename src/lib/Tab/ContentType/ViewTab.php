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
    public const string URI_FRAGMENT = 'ibexa-tab-content-type-view-details';

    public function getIdentifier(): string
    {
        return 'view';
    }

    public function getName(): string
    {
        /** @Desc("View") */
        return $this->translator->trans('tab.name.view', [], 'ibexa_content_type');
    }

    public function getOrder(): int
    {
        return 100;
    }

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
