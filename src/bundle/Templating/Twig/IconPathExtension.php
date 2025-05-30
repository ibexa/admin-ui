<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\Contracts\AdminUi\Resolver\IconPathResolverInterface;
use Twig\DeprecatedCallableInfo;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class IconPathExtension extends AbstractExtension
{
    private IconPathResolverInterface $iconPathResolver;

    public function __construct(IconPathResolverInterface $iconPathResolver)
    {
        $this->iconPathResolver = $iconPathResolver;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ez_icon_path',
                $this->getIconPath(...),
                [
                    'is_safe' => ['html'],
                    'deprecation_info' => new DeprecatedCallableInfo('ibexa/admin-ui', '4.6', 'ibexa_icon_path'),
                ],
            ),
            new TwigFunction(
                'ibexa_icon_path',
                $this->getIconPath(...),
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }

    public function getIconPath(string $icon, string $set = null): string
    {
        return $this->iconPathResolver->resolve($icon, $set);
    }
}
