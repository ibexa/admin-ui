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

class ContentTypeIconExtension extends AbstractExtension
{
    private ContentTypeIconResolver $contentTypeIconResolver;

    public function __construct(ContentTypeIconResolver $contentTypeIconResolver)
    {
        $this->contentTypeIconResolver = $contentTypeIconResolver;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ez_content_type_icon',
                [$this->contentTypeIconResolver, 'getContentTypeIcon'],
                [
                    'is_safe' => ['html'],
                    'deprecated' => '4.0',
                    'alternative' => 'ibexa_content_type_icon',
                ]
            ),
            new TwigFunction(
                'ibexa_content_type_icon',
                [$this->contentTypeIconResolver, 'getContentTypeIcon'],
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }
}

class_alias(ContentTypeIconExtension::class, 'EzSystems\EzPlatformAdminUiBundle\Templating\Twig\ContentTypeIconExtension');
