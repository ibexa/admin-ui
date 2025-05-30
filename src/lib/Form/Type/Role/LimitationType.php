<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Type\Role;

use Ibexa\AdminUi\Limitation\LimitationFormMapperInterface;
use Ibexa\AdminUi\Limitation\LimitationFormMapperRegistryInterface;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LimitationType extends AbstractType
{
    private LimitationFormMapperRegistryInterface $limitationFormMapperRegistry;

    private LimitationFormMapperInterface $nullMapper;

    public function __construct(LimitationFormMapperRegistryInterface $limitationFormMapperRegistry, LimitationFormMapperInterface $nullMapper)
    {
        $this->limitationFormMapperRegistry = $limitationFormMapperRegistry;
        $this->nullMapper = $nullMapper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var \Ibexa\Contracts\Core\Repository\Values\User\Limitation $data */
            $data = $event->getData();
            $form = $event->getForm();

            if ($this->limitationFormMapperRegistry->hasMapper($data->getIdentifier())) {
                $this->limitationFormMapperRegistry->getMapper($data->getIdentifier())->mapLimitationForm($form, $data);
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            /** @var \Ibexa\Contracts\Core\Repository\Values\User\Limitation $data */
            $data = $event->getData();
            if ($this->limitationFormMapperRegistry->hasMapper($data->getIdentifier())) {
                $this->limitationFormMapperRegistry->getMapper($data->getIdentifier())->filterLimitationValues($data);
            }
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $data = $view->vars['value'];
        if (!$data instanceof Limitation) {
            return;
        }

        if ($this->limitationFormMapperRegistry->hasMapper($data->getIdentifier())) {
            $mapper = $this->limitationFormMapperRegistry->getMapper($data->getIdentifier());
        } else {
            $mapper = $this->nullMapper;
        }
        $view->vars['mapper'] = $mapper;
        $view->vars['template'] = $mapper->getFormTemplate();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => '\\Ibexa\\Contracts\\Core\\Repository\\Values\\User\\Limitation',
            'translation_domain' => 'ibexa_content_forms_policies',
        ]);
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_content_forms_policy_limitation_edit';
    }
}
