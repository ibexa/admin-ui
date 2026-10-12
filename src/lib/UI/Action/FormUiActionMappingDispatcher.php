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
    /** @var FormUiActionMapperInterface[] */
    protected $mappers;

    /** @var FormUiActionMapperInterface */
    protected $defaultMapper;

    /**
     * @param Traversable $mappers
     * @param FormUiActionMapperInterface $defaultMapper
     */
    public function __construct(
        Traversable $mappers,
        FormUiActionMapperInterface $defaultMapper
    ) {
        $this->mappers = $mappers;
        $this->defaultMapper = $defaultMapper;
    }

    /**
     * @return FormUiActionMapperInterface[]
     */
    public function getMappers(): array
    {
        return $this->mappers;
    }

    /**
     * @param FormUiActionMapperInterface[] $mappers
     */
    public function setMappers(array $mappers): void
    {
        $this->mappers = $mappers;
    }

    /**
     * @return FormUiActionMapperInterface
     */
    public function getDefaultMapper(): FormUiActionMapperInterface
    {
        return $this->defaultMapper;
    }

    /**
     * @param FormUiActionMapperInterface $defaultMapper
     */
    public function setDefaultMapper(FormUiActionMapperInterface $defaultMapper): void
    {
        $this->defaultMapper = $defaultMapper;
    }

    /**
     * @param FormInterface $form
     *
     * @return UiActionEvent
     */
    public function dispatch(FormInterface $form): UiActionEvent
    {
        /** @var FormUiActionMapperInterface[] $mappers */
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
