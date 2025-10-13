<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ObjectState;

use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<\Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState>
 */
class ObjectStateChoiceType extends AbstractType
{
    public function __construct(protected readonly ObjectStateService $objectStateService)
    {
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choice_loader' => new CallbackChoiceLoader(function (): array {
                    $objectStates = [];
                    $objectStateGroups = $this->objectStateService->loadObjectStateGroups();
                    foreach ($objectStateGroups as $objectStateGroup) {
                        $objectStates[$objectStateGroup->identifier] = $this->objectStateService->loadObjectStates(
                            $objectStateGroup
                        );
                    }

                    return $objectStates;
                }),
                'choice_label' => 'name',
                'choice_name' => 'identifier',
                'choice_value' => 'id',
            ]);
    }
}
