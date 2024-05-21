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
    /**
     * @var \Ibexa\Core\MVC\Symfony\Matcher\MatcherFactoryInterface
     */
    protected $matcherFactory;

    /**
     * @param \Ibexa\Core\MVC\Symfony\Matcher\MatcherFactoryInterface $matcherFactory
     */
    public function __construct(MatcherFactoryInterface $matcherFactory)
    {
        $this->matcherFactory = $matcherFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentType
     */
    public function getView(View $view)
    {
        if (($configHash = $this->matcherFactory->match($view)) === null) {
            return null;
        }

        return $this->buildContentTranslateView($configHash);
    }

    /**
     * Builds a ContentTranslateView object from $viewConfig.
     *
     * @param array $viewConfig
     *
     * @return \Ibexa\AdminUi\View\ContentTranslateView
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
