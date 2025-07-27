<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Policy;

use Ibexa\Contracts\Core\Repository\Values\User\Policy;

class PolicyUpdateData
{
    /** @var string */
    private ?string $module = null;

    /** @var string */
    private ?string $function = null;

    /** @var array */
    private $limitations;

    public function __construct(?Policy $policy = null)
    {
        if (null === $policy) {
            return;
        }

        $this->module = $policy->module;
        $this->function = $policy->function;
        $this->limitations = $policy->limitations;
    }

    /**
     * @return string
     */
    public function getModule(): ?string
    {
        return $this->module;
    }

    /**
     * @param string $module
     */
    public function setModule(string $module): void
    {
        $this->module = $module;
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
     */
    public function setFunction(string $function): void
    {
        $this->function = $function;
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

    /**
     * @param array $policy
     */
    public function setPolicy(array $policy): void
    {
        $this->setModule($policy['module']);
        $this->setFunction($policy['function']);
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
}
