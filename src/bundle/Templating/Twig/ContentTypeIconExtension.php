<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\AdminUi\UI\Service\ContentTypeIconResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ContentTypeIconExtension extends AbstractExtension
{
    public function __construct(
        private readonly ContentTypeIconResolver $contentTypeIconResolver
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ibexa_content_type_icon',
                $this->contentTypeIconResolver->getContentTypeIcon(...),
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }
}
