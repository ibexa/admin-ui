<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\TrashLocationOptionProvider;

use Ibexa\AdminUi\Specification\Content\ContentHaveAssetRelation;
use Ibexa\AdminUi\Specification\Content\ContentHaveUniqueRelation;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class HasAssetRelation implements TrashLocationOptionProvider
{
    private ContentService $contentService;

    private TranslatorInterface $translator;

    public function __construct(
        ContentService $contentService,
        TranslatorInterface $translator
    ) {
        $this->contentService = $contentService;
        $this->translator = $translator;
    }

    public function supports(Location $location): bool
    {
        return (new ContentHaveAssetRelation($this->contentService))
            ->and((new ContentHaveUniqueRelation($this->contentService))->not())
            ->isSatisfiedBy($location->getContent());
    }

    public function addOptions(FormInterface $form, Location $location): void
    {
        $form->add('trash_assets_non_unique', ChoiceType::class, [
            'label' =>
                /** @Desc("Asset Fields(s)") */
                $this->translator->trans('form.trash_assets_non_unique.label', [], 'forms'),
            'help_multiline' => [
                /** @Desc("You are about to delete a Content item that has one or more asset Field(s) used by other Content items. These assets will remain available in system.") */
                $this->translator->trans('trash_asset.modal.message_header'),
                /** @Desc("If you wish to delete these assets too, first make sure they are not used by other content. To check, go to the asset preview and look at its content Relations in the Relations tab.") */
                $this->translator->trans('trash_asset.modal.message_body'),
            ],
        ]);
    }
}
