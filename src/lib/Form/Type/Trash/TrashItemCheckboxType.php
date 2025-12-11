<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Trash;

use Ibexa\AdminUi\Form\DataTransformer\TrashItemTransformer;
use Ibexa\Contracts\Core\Repository\TrashService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<mixed>
 */
final class TrashItemCheckboxType extends AbstractType
{
    public function __construct(private readonly TrashService $trashService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(
            new TrashItemTransformer($this->trashService)
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars = array_replace($view->vars, [
            'value' => $form->getViewData(),
            'checked' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => false,
        ]);
    }
}
