<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Policy;

final class PolicyCreateData
{
    private ?string $module = null;

    private ?string $function = null;

    /** @var \Ibexa\Contracts\Core\Repository\Values\User\Limitation[] */
    private array $limitations = [];

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function setModule(?string $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getFunction(): ?string
    {
        return $this->function;
    }

    public function setFunction(?string $function): self
    {
        $this->function = $function;

        return $this;
    }

    /**
     * @param array<string, string|null> $policy
     */
    public function setPolicy(array $policy): self
    {
        $this->setModule($policy['module']);
        $this->setFunction($policy['function']);

        return $this;
    }

    /**
     * @return array<string, string|null>
     */
    public function getPolicy(): array
    {
        return [
            'module' => $this->getModule(),
            'function' => $this->getFunction(),
        ];
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Limitation[]
     */
    public function getLimitations(): array
    {
        return $this->limitations;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Limitation[] $limitations
     */
    public function setLimitations(array $limitations): void
    {
        $this->limitations = $limitations;
    }
}
