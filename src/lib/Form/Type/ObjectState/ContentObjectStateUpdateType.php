<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ObjectState;

use Ibexa\AdminUi\Form\Data\ObjectState\ContentObjectStateUpdateData;
use Ibexa\AdminUi\Form\Type\Content\ContentInfoType;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentObjectStateUpdateType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\ObjectStateService */
    protected $objectStateService;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private $permissionResolver;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ObjectStateService $objectStateService
     * @param \Ibexa\Contracts\Core\Repository\PermissionResolver $permissionResolver
     */
    public function __construct(ObjectStateService $objectStateService, PermissionResolver $permissionResolver)
    {
        $this->objectStateService = $objectStateService;
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contentInfo', ContentInfoType::class, [
                'label' => false,
            ])
            ->add('objectStateGroup', ObjectStateGroupType::class, [
                'label' => false,
            ])
            ->add('set', SubmitType::class, [
                'label' => /** @Desc("Set") */ 'object_state.button.set',
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ibexa\AdminUi\Form\Data\ObjectState\ContentObjectStateUpdateData $contentObjectStateUpdateData */
            $contentObjectStateUpdateData = $event->getData();
            $objectStateGroup = $contentObjectStateUpdateData->getObjectStateGroup();
            $contentInfo = $contentObjectStateUpdateData->getContentInfo();
            $form = $event->getForm();

            $form->add('objectState', ObjectStateChoiceType::class, [
                'label' => false,
                'choice_loader' => new CallbackChoiceLoader(function () use ($objectStateGroup, $contentInfo) {
                    $contentState = $this->objectStateService->getContentState($contentInfo, $objectStateGroup);

                    return array_filter(
                        $this->objectStateService->loadObjectStates($objectStateGroup),
                        function (ObjectState $objectState) use ($contentInfo, $contentState) {
                            return $this->permissionResolver->canUser('state', 'assign', $contentInfo, [$objectState]);
                        }
                    );
                }),
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContentObjectStateUpdateData::class,
            'translation_domain' => 'ibexa_object_state',
        ]);
    }
}
