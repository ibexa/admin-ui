<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Policy;

class PolicyCreateData
{
    private ?string $module = null;

    private ?string $function = null;

    private array $limitations = [];

    /**
     * @return string
     */
    public function getModule(): ?string
    {
        return $this->module;
    }

    /**
     * @param string $module
     *
     * @return PolicyCreateData
     */
    public function setModule(string $module): self
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return string
     */
    public function getFunction(): ?string
    {
        return $this->function;
    }

    /**
     * @param string $function
     *
     * @return PolicyCreateData
     */
    public function setFunction(string $function): self
    {
        $this->function = $function;

        return $this;
    }

    /**
     * @param array $policy
     *
     * @return PolicyCreateData
     */
    public function setPolicy(array $policy): self
    {
        $this->setModule($policy['module']);
        $this->setFunction($policy['function']);

        return $this;
    }

    /**
     * @return array
     */
    public function getPolicy(): ?array
    {
        return [
            'module' => $this->getModule(),
            'function' => $this->getFunction(),
        ];
    }

    /**
     * @return array
     */
    public function getLimitations(): ?array
    {
        return $this->limitations;
    }

    /**
     * @param array $limitations
     */
    public function setLimitations(array $limitations): void
    {
        $this->limitations = $limitations;
    }
}
