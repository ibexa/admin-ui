<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Tab;

use Symfony\Bridge\Twig\Extension\HttpKernelRuntime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Base class for Tabs based on a route.
 */
abstract class AbstractRouteBasedTab extends AbstractTab
{
    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    protected $urlGenerator;

    /** @var \Symfony\Bridge\Twig\Extension\HttpKernelRuntime */
    private $httpKernelRuntime;

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        HttpKernelRuntime $httpKernelRuntime
    ) {
        parent::__construct($twig, $translator);

        $this->urlGenerator = $urlGenerator;
        $this->httpKernelRuntime = $httpKernelRuntime;
    }

    public function renderView(array $parameters): string
    {
        $route = $this->urlGenerator->generate(
            $this->getRouteName($parameters),
            $this->getRouteParameters($parameters)
        );

        return $this->httpKernelRuntime->renderFragment($route);
    }

    /**
     * Returns route name used to generate path to the resource.
     *
     * @param array<string, mixed> $parameters
     */
    abstract public function getRouteName(array $parameters): string;

    /**
     * Returns parameters array required to generate path using the router.
     *
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    abstract public function getRouteParameters(array $parameters): array;
}
