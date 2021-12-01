<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Action;

use Ibexa\Contracts\AdminUi\UI\Action\FormUiActionMapperInterface;
use Symfony\Component\Form\FormInterface;
use Traversable;

class FormUiActionMappingDispatcher
{
    /** @var \Ibexa\Contracts\AdminUi\UI\Action\FormUiActionMapperInterface[] */
    protected $mappers;

    /** @var \Ibexa\Contracts\AdminUi\UI\Action\FormUiActionMapperInterface */
    protected $defaultMapper;

    /**
     * @param \Traversable $mappers
     * @param \Ibexa\Contracts\AdminUi\UI\Action\FormUiActionMapperInterface $defaultMapper
     */
    public function __construct(
        Traversable $mappers,
        FormUiActionMapperInterface $defaultMapper
    ) {
        $this->mappers = $mappers;
        $this->defaultMapper = $defaultMapper;
    }

    /**
     * @return \Ibexa\Contracts\AdminUi\UI\Action\FormUiActionMapperInterface[]
     */
    public function getMappers(): array
    {
        return $this->mappers;
    }

    /**
     * @param \Ibexa\Contracts\AdminUi\UI\Action\FormUiActionMapperInterface[] $mappers
     */
    public function setMappers(array $mappers): void
    {
        $this->mappers = $mappers;
    }

    /**
     * @return \Ibexa\Contracts\AdminUi\UI\Action\FormUiActionMapperInterface
     */
    public function getDefaultMapper(): FormUiActionMapperInterface
    {
        return $this->defaultMapper;
    }

    /**
     * @param \Ibexa\Contracts\AdminUi\UI\Action\FormUiActionMapperInterface $defaultMapper
     */
    public function setDefaultMapper(FormUiActionMapperInterface $defaultMapper): void
    {
        $this->defaultMapper = $defaultMapper;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     *
     * @return \Ibexa\AdminUi\UI\Action\UiActionEvent
     */
    public function dispatch(FormInterface $form): UiActionEvent
    {
        /** @var \Ibexa\Contracts\AdminUi\UI\Action\FormUiActionMapperInterface[] $mappers */
        foreach ($this->mappers as $mapper) {
            if ($mapper === $this->defaultMapper) {
                continue;
            }

            if ($mapper->supports($form)) {
                return $mapper->map($form);
            }
        }

        return $this->defaultMapper->map($form);
    }
}

class_alias(FormUiActionMappingDispatcher::class, 'EzSystems\EzPlatformAdminUi\UI\Action\FormUiActionMappingDispatcher');
