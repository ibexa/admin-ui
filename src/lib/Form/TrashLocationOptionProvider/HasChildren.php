<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\TrashLocationOptionProvider;

use Ibexa\AdminUi\Specification\Location\HasChildren as HasChildrenSpec;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class HasChildren implements TrashLocationOptionProvider
{
    public function __construct(
        private LocationService $locationService,
        private TranslatorInterface $translator,
        private ConfigResolverInterface $configResolver
    ) {
    }

    public function supports(Location $location): bool
    {
        return (new HasChildrenSpec($this->locationService))->isSatisfiedBy($location);
    }

    public function addOptions(FormInterface $form, Location $location): void
    {
        $limit = (int) $this->configResolver->getParameter('subtree_operations.query_subtree.limit');

        $useLimit = $limit > 0;
        $childCount = $this->locationService->getLocationChildCount($location, $useLimit ? $limit + 1 : null);

        $translatorParameters = [
            '%children_count%' => ($useLimit && $childCount >= $limit) ?
                sprintf('%d+', $limit) :
                $childCount,
            '%content%' => $location->getContent()->getName(),
        ];

        $form
            ->add('has_children', ChoiceType::class, [
                'label' =>
                    /** @Desc("Sub-items") */
                    $this->translator->trans('form.has_children.label', [], 'forms'),
                'help_multiline' => [
                    /** @Desc("Sending '%content%' and its %children_count% Content item(s) to Trash will also send the sub-items of this Location to Trash.") */
                    $this->translator->trans('trash_container.modal.message_main', $translatorParameters, 'messages'),
                ],
            ]);
    }
}
