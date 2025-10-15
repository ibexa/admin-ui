<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Policy;

use Ibexa\Contracts\Core\Repository\Values\User\Policy;

final class PolicyUpdateData
{
    private ?string $module = null;

    private ?string $function = null;

    /** @var \Ibexa\Contracts\Core\Repository\Values\User\Limitation[] */
    private array $limitations = [];

    public function __construct(?Policy $policy = null)
    {
        if (null === $policy) {
            return;
        }

        $this->module = $policy->module;
        $this->function = $policy->function;
        $this->limitations = iterator_to_array($policy->getLimitations());
    }

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function setModule(?string $module): void
    {
        $this->module = $module;
    }

    public function getFunction(): ?string
    {
        return $this->function;
    }

    public function setFunction(?string $function): void
    {
        $this->function = $function;
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

    /**
     * @param array<string, string|null> $policy
     */
    public function setPolicy(array $policy): void
    {
        $this->setModule($policy['module']);
        $this->setFunction($policy['function']);
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
}
