<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component\Search;

use Ibexa\AdminUi\Form\Type\Search\GlobalSearchType;
use Ibexa\Contracts\AdminUi\Component\Renderable;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class GlobalSearchTwigComponent implements Renderable
{
    /** @var \Twig\Environment */
    private $twig;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $formFactory;

    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(
        Environment $twig,
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
    }

    public function render(array $parameters = []): string
    {
        $form = $this->formFactory->createNamed(
            'search',
            GlobalSearchType::class,
            null,
            [
                'action' => $this->urlGenerator->generate('ibexa.search'),
                'method' => Request::METHOD_GET,
                'csrf_protection' => false,
            ]
        );

        return $this->twig->render('@ibexadesign/ui/global_search.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
