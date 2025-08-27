<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component\Search;

use Ibexa\AdminUi\Form\Type\Search\GlobalSearchType;
use Ibexa\Contracts\TwigComponents\ComponentInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final readonly class GlobalSearchTwigComponent implements ComponentInterface
{
    public function __construct(
        private Environment $twig,
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator
    ) {
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
