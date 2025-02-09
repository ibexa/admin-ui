<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\AdminUi\UI\Service\ContentTypeGroupIconResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @internal
 */
final class ContentTypeGroupIconExtension extends AbstractExtension
{
    private ContentTypeGroupIconResolver $contentTypeGroupIconResolver;

    public function __construct(ContentTypeGroupIconResolver $contentTypeGroupIconResolver)
    {
        $this->contentTypeGroupIconResolver = $contentTypeGroupIconResolver;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ibexa_content_type_group_icon',
                $this->contentTypeGroupIconResolver->getContentTypeGroupIcon(...),
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }
}
