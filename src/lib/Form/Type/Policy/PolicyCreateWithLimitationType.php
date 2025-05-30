<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Policy;

use Ibexa\AdminUi\Form\Data\Policy\PolicyCreateData;
use Ibexa\AdminUi\Form\Type\Role\LimitationType;
use Ibexa\Contracts\Core\Repository\RoleService;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PolicyCreateWithLimitationType extends AbstractType
{
    private RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'policy',
                PolicyChoiceType::class,
                [
                    'label' => /** @Desc("Type") */ 'role.policy.type',
                    'placeholder' => /** @Desc("Choose a type") */ 'role.policy.type.choose',
                    'disabled' => true,
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                ['label' => /** @Desc("Create") */ 'policy_create.save']
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data instanceof PolicyCreateData) {
                $availableLimitationTypes = $this->roleService->getLimitationTypesByModuleFunction(
                    $data->getModule(),
                    $data->getFunction()
                );

                $form->add('limitations', CollectionType::class, [
                    'label' => false,
                    'translation_domain' => 'ibexa_content_forms_role',
                    'entry_type' => LimitationType::class,
                    'data' => $this->generateLimitationList(
                        $data->getLimitations(),
                        $availableLimitationTypes
                    ),
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'ibexa_content_forms_role',
            'data_class' => PolicyCreateData::class,
        ]);
    }

    /**
     * Generates the limitation list from existing limitations (already configured for current policy) and
     * available limitation types available for current policy (i.e. current module/function combination).
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Limitation[] $existingLimitations
     * @param \Ibexa\Contracts\Core\Limitation\Type[] $availableLimitationTypes
     *
     * @return array|\Ibexa\Contracts\Core\Repository\Values\User\Limitation[]
     */
    private function generateLimitationList(array $existingLimitations, array $availableLimitationTypes): array
    {
        $limitations = [];
        foreach ($existingLimitations as $limitation) {
            $limitations[$limitation->getIdentifier()] = $limitation;
        }

        foreach ($availableLimitationTypes as $identifier => $limitationType) {
            if (isset($limitations[$identifier])) {
                continue;
            }

            $limitations[$identifier] = $limitationType->buildValue([]);
        }

        ksort($limitations);

        return $limitations;
    }
}
