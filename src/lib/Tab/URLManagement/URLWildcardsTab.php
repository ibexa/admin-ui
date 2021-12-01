<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\URLManagement;

use Ibexa\AdminUi\Form\Data\URLWildcard\URLWildcardDeleteData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\Contracts\AdminUi\Tab\AbstractTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\URLWildcardService;
use Ibexa\Core\MVC\ConfigResolverInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class URLWildcardsTab extends AbstractTab implements OrderedTabInterface
{
    public const URI_FRAGMENT = 'ibexa-tab-link-manager-url-wildcards';

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    protected $permissionResolver;

    /** @var \Ibexa\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \Ibexa\Contracts\Core\Repository\URLWildcardService */
    private $urlWildcardService;

    /** @var \Ibexa\AdminUi\Form\Factory\FormFactory */
    private $formFactory;

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        PermissionResolver $permissionResolver,
        ConfigResolverInterface $configResolver,
        URLWildcardService $urlWildcardService,
        FormFactory $formFactory
    ) {
        parent::__construct($twig, $translator);

        $this->permissionResolver = $permissionResolver;
        $this->configResolver = $configResolver;
        $this->urlWildcardService = $urlWildcardService;
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'url-wildcards';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return /** @Desc("URL wildcards") */
            $this->translator->trans('tab.name.url_wildcards', [], 'url_wildcard');
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder(): int
    {
        return 20;
    }

    /**
     * @param array $parameters
     *
     * @return string
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function renderView(array $parameters): string
    {
        $urlWildcards = $this->urlWildcardService->loadAll();

        $urlWildcardsChoices = [];
        foreach ($urlWildcards as $urlWildcardItem) {
            $urlWildcardsChoices[$urlWildcardItem->id] = false;
        }

        $deleteUrlWildcardDeleteForm = $this->formFactory->deleteURLWildcard(
            new URLWildcardDeleteData($urlWildcardsChoices)
        );

        $addUrlWildcardForm = $this->formFactory->createURLWildcard();
        $urlWildcardsEnabled = $this->configResolver->getParameter('url_wildcards.enabled');
        $canManageWildcards = $this->permissionResolver->hasAccess('content', 'urltranslator');

        return $this->twig->render('@ezdesign/url_wildcard/list.html.twig', [
            'url_wildcards' => $urlWildcards,
            'form' => $deleteUrlWildcardDeleteForm->createView(),
            'form_add' => $addUrlWildcardForm->createView(),
            'url_wildcards_enabled' => $urlWildcardsEnabled,
            'can_manage' => $canManageWildcards,
        ]);
    }
}

class_alias(URLWildcardsTab::class, 'EzSystems\EzPlatformAdminUi\Tab\URLManagement\URLWildcardsTab');
