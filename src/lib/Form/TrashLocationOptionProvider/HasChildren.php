<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\TrashLocationOptionProvider;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\AdminUi\Specification\Location\HasChildren as HasChildrenSpec;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class HasChildren implements TrashLocationOptionProvider
{
    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    public function __construct(LocationService $locationService, TranslatorInterface $translator)
    {
        $this->locationService = $locationService;
        $this->translator = $translator;
    }

    public function supports(Location $location): bool
    {
        return (new HasChildrenSpec($this->locationService))->isSatisfiedBy($location);
    }

    public function addOptions(FormInterface $form, Location $location): void
    {
        $childCount = $this->locationService->getLocationChildCount($location);

        $translatorParameters = [
            '%children_count%' => $childCount,
            '%content_name%' => $location->getContent()->getName(),
        ];

        $form
            ->add('has_children', ChoiceType::class, [
                'label' =>
                    /** @Desc("Sub-items") */
                    $this->translator->trans('form.has_children.label', [], 'forms'),
                'help_multiline' => [
                    /** @Desc("Sending '%content_name%' and its %children_count% Content item(s) to Trash will also send the sub-items of this Location to Trash.") */
                    $this->translator->trans('trash_container.modal.message_main', $translatorParameters, 'messages'),
                ],
            ]);
    }
}

class_alias(HasChildren::class, 'EzSystems\EzPlatformAdminUi\Form\TrashLocationOptionProvider\HasChildren');
