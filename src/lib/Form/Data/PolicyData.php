<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data;

use Ibexa\Contracts\Core\Repository\Values\User\Policy;

class PolicyData
{
    /** @var string */
    private $module;

    /** @var string */
    private $function;

    /** @var array */
    private $limitations;

    /**
     * PolicyData constructor.
     *
     * @param string $module
     * @param string $function
     * @param array $limitations
     */
    public function __construct($module = null, $function = null, array $limitations = [])
    {
        $this->module = $module;
        $this->function = $function;
        $this->limitations = $limitations;
    }

    /**
     * @return array
     */
    public function getModuleFunction(): array
    {
        return [
            'module' => $this->getModule(),
            'function' => $this->getFunction(),
        ];
    }

    public function setModuleFunction(array $moduleFunction): void
    {
        $this->module = $moduleFunction['module'];
        $this->function = $moduleFunction['function'];
    }

    public function getLimitations(): array
    {
        return $this->limitations;
    }

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
        $data->limitations = $policy->limitations;

        return $data;
    }
}
