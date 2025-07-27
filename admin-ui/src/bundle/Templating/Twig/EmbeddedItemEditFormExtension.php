<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\AdminUi\Form\Data\Content\Draft\ContentEditData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class EmbeddedItemEditFormExtension extends AbstractExtension
{
    private FormFactory $formFactory;

    private RouterInterface $router;

    public function __construct(
        FormFactory $formFactory,
        RouterInterface $router
    ) {
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ibexa_render_embedded_item_edit_form',
                $this->renderEmbeddedItemEditForm(...)
            ),
        ];
    }

    public function renderEmbeddedItemEditForm(): FormView
    {
        return $this->formFactory->contentEdit(
            new ContentEditData(),
            'embedded_item_edit',
            [
                'action' => $this->router->generate('ibexa.content.edit'),
                'attr' => [
                    'class' => 'ibexa-embedded-item-edit',
                ],
            ]
        )->createView();
    }
}
