<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data;

use Ibexa\Contracts\Core\Repository\Values\User\Policy;

final class PolicyData
{
    /**
     * @param array<string, mixed> $limitations
     */
    public function __construct(
        private ?string $module = null,
        private ?string $function = null,
        private array $limitations = []
    ) {
    }

    /**
     * @return array<string, string|null>
     */
    public function getModuleFunction(): array
    {
        return [
            'module' => $this->getModule(),
            'function' => $this->getFunction(),
        ];
    }

    /**
     * @param array<string, string|null> $moduleFunction
     */
    public function setModuleFunction(array $moduleFunction): void
    {
        $this->module = $moduleFunction['module'];
        $this->function = $moduleFunction['function'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getLimitations(): array
    {
        return $this->limitations;
    }

    /**
     * @param array<string, mixed> $limitations
     */
    public function setLimitations(array $limitations): void
    {
        $this->limitations = $limitations;
    }

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function getFunction(): ?string
    {
        return $this->function;
    }

    public static function factory(Policy $policy): self
    {
        $data = new self();
        $data->module = $policy->module;
        $data->function = $policy->function;
        $data->limitations = iterator_to_array($policy->getLimitations());

        return $data;
    }
}
