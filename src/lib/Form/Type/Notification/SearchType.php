<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Notification;

use Ibexa\Bundle\AdminUi\Form\Data\SearchQueryData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<\Ibexa\Bundle\AdminUi\Form\Data\SearchQueryData>
 */
final class SearchType extends AbstractType
{
    public function buildView(
        FormView $view,
        FormInterface $form,
        array $options
    ): void {
        /** @var SearchQueryData|null $data */
        $data = $form->getData();
        $view->vars['is_any_filter_set'] = false;

        if ($data !== null) {
            $statuses = $data->getStatuses();
            $type = $data->getType();
            $createdRange = $data->getCreatedRange();

            $view->vars['is_any_filter_set'] =
                (!empty($statuses)) ||
                (!empty($type)) ||
                ($createdRange !== null && ($createdRange->getMin() !== null || $createdRange->getMax() !== null));
        }
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('type', NotificationTypeChoiceType::class, [
                'required' => false,
            ])
            ->add('statuses', NotificationStatusChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ])
            ->add('createdRange', NotificationCreatedRangeType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchQueryData::class,
            'translation_domain' => 'ibexa_notifications',
        ]);
    }
}
