<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;

final class SuggestionSetting implements ProviderInterface
{
    private int $minQueryLength;

    private int $resultLimit;

    public function __construct(int $minQueryLength, int $resultLimit)
    {
        $this->minQueryLength = $minQueryLength;
        $this->resultLimit = $resultLimit;
    }

    /**
     * @return array<string,int>
     */
    public function getConfig(): array
    {
        return [
            'minQueryLength' => $this->minQueryLength,
            'resultLimit' => $this->resultLimit,
        ];
    }
}
