<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\View\Provider\ContentTranslateView;

use Ibexa\AdminUi\View\ContentTranslateView;
use Ibexa\Core\MVC\Symfony\Matcher\MatcherFactoryInterface;
use Ibexa\Core\MVC\Symfony\View\View;
use Ibexa\Core\MVC\Symfony\View\ViewProvider;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * View provider based on configuration.
 */
class Configured implements ViewProvider
{
    public function __construct(protected readonly MatcherFactoryInterface $matcherFactory)
    {
    }

    /**
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentType
     */
    public function getView(View $view): ?ContentTranslateView
    {
        $configHash = $this->matcherFactory->match($view);
        if ($configHash === null) {
            return null;
        }

        return $this->buildContentTranslateView($configHash);
    }

    /**
     * @param array<string, mixed> $viewConfig
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentType
     */
    protected function buildContentTranslateView(array $viewConfig): ContentTranslateView
    {
        $view = new ContentTranslateView();
        $view->setConfigHash($viewConfig);
        if (isset($viewConfig['template'])) {
            $view->setTemplateIdentifier($viewConfig['template']);
        }
        if (isset($viewConfig['controller'])) {
            $view->setControllerReference(new ControllerReference($viewConfig['controller']));
        }
        if (isset($viewConfig['params']) && is_array($viewConfig['params'])) {
            $view->addParameters($viewConfig['params']);
        }

        return $view;
    }
}
