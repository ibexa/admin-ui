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

    protected FormUiActionMapperInterface $defaultMapper;

    /**
     * @param Traversable<FormUiActionMapperInterface> $mappers
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

    public function getDefaultMapper(): FormUiActionMapperInterface
    {
        return $this->defaultMapper;
    }

    public function setDefaultMapper(FormUiActionMapperInterface $defaultMapper): void
    {
        $this->defaultMapper = $defaultMapper;
    }

    /**
     * @param FormInterface<mixed> $form
     */
    public function dispatch(FormInterface $form): UiActionEvent
    {
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
