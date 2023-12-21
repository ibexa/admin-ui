<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider\Module;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;

/**
 * @template TConfig of array{
 *      imageFieldDefinitionIdentifiers: array<string>,
 *      imageContentTypeIdentifiers: array<string>,
 *      aggregations: array<string, array<string, string>>,
 *  }
 */
final class ImagePicker implements ProviderInterface
{
    /** @phpstan-var TConfig */
    private array $config;

    /**
     * @phpstan-param TConfig $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @phpstan-return TConfig
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
