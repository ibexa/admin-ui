<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Symfony\Component\Routing\RouterInterface;

final class CurrentBackOfficePath implements ProviderInterface
{
    private RouterInterface $router;

    public function __construct(
        RouterInterface $router
    ) {
        $this->router = $router;
    }

    public function getConfig(): string
    {
        /*
         * We need base path here, to properly set backoffice cookies only,
         *  so they do not interfere with front end site accesses.
         * Generating route for `/` allows us to make sure that we do not have any additional parameters
         *  and we get raw path to back office.
         * We are not using reverse matchers here as they work in context of current request.
         */
        $routeInfo = $this->router->match('/');
        $path = $this->router->generate($routeInfo['_route'], $routeInfo);
        $pathInfo = pathinfo($path);

        return $pathInfo['dirname'];
    }
}
