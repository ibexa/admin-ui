<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider\Module;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;

final class ImagePicker implements ProviderInterface
{
    /** @var array<string> */
    private array $imageFieldDefinitionIdentifiers;

    /**
     * @param array<string> $imageFieldDefinitionIdentifiers
     */
    public function __construct(array $imageFieldDefinitionIdentifiers)
    {
        $this->imageFieldDefinitionIdentifiers = $imageFieldDefinitionIdentifiers;
    }

    /**
     * @return array{
     *     imageFieldDefinitionIdentifiers: array<string>,
     * }
     */
    public function getConfig(): array
    {
        return [
            'imageFieldDefinitionIdentifiers' => $this->imageFieldDefinitionIdentifiers,
        ];
    }
}
