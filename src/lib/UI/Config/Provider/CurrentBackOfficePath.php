<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\Repository\Repository;
use Symfony\Component\Routing\RouterInterface;

final class CurrentBackOfficePath implements ProviderInterface
{
    private RouterInterface $router;

    private Repository $repository;

    public function __construct(
        RouterInterface $router,
        Repository $repository
    ) {
        $this->router = $router;
        $this->repository = $repository;
    }

    public function getConfig(): string
    {
        /*
         * We need base path here, to properly set backoffice cookies only,
         * so they do not interfere with front end SiteAccesses.
         * Generating route for `/` allows us to make sure that we do not have any additional parameters
         * and we get raw path to back office.
         * We are not using reverse matchers here as they work in context of current request.
         *
         * Sudo is used to avoid insufficient permissions on reading root Location.
         */
        return $this->repository->sudo(
            function (Repository $repository): string {
                $routeInfo = $this->router->match('/');
                $path = $this->router->generate($routeInfo['_route'], $routeInfo);
                $pathInfo = pathinfo($path);

                return $pathInfo['dirname'];
            }
        );
    }
}
