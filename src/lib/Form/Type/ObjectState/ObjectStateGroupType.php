<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ObjectState;

use Ibexa\AdminUi\Form\DataTransformer\ObjectStateGroupTransformer;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends \Symfony\Component\Form\AbstractType<mixed>
 */
final class ObjectStateGroupType extends AbstractType
{
    public function __construct(private readonly ObjectStateService $objectStateService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(
            new ObjectStateGroupTransformer($this->objectStateService)
        );
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
