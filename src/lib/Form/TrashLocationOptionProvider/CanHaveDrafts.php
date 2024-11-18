<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\TrashLocationOptionProvider;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CanHaveDrafts implements TrashLocationOptionProvider
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function supports(Location $location): bool
    {
        return true;
    }

    public function addOptions(FormInterface $form, Location $location): void
    {
        $form
            ->add('can_have_drafts', ChoiceType::class, [
                'label' =>
                    /** @Desc("Drafts") */
                    $this->translator->trans('drafts.list', [], 'ibexa_drafts'),
                'help_multiline' => [
                    /** @Desc("Sending this content item to Trash will also delete all drafts of content items that haven’t been published yet, and belong to the trashed subtree.") */
                    $this->translator->trans('trash.modal.send_to_trash_draft_warning.message', [], 'ibexa_drafts'),
                ],
            ]);
    }
}
