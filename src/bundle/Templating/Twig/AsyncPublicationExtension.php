<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\Contracts\Core\Container\ApiLoader\RepositoryConfigurationProviderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AsyncPublicationExtension extends AbstractExtension
{
    public function __construct(
        private readonly RepositoryConfigurationProviderInterface $repositoryConfigurationProvider,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('async_publish_enabled', $this->isEnabled(...)),
        ];
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->repositoryConfigurationProvider->getRepositoryConfig()['async_content_publish'] ?? false);
    }
}
