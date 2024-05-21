<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Dashboard;

use Ibexa\AdminUi\Pagination\Pagerfanta\ContentDraftAdapter;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\AdminUi\Tab\AbstractTab;
use Ibexa\Contracts\AdminUi\Tab\ConditionalTabInterface;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class MyDraftsTab extends AbstractTab implements OrderedTabInterface, ConditionalTabInterface
{
    private const PAGINATION_PARAM_NAME = 'mydrafts-page';

    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    protected $contentService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    protected $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    protected $permissionResolver;

    /** @var \Ibexa\AdminUi\UI\Dataset\DatasetFactory */
    protected $datasetFactory;

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        PermissionResolver $permissionResolver,
        DatasetFactory $datasetFactory,
        RequestStack $requestStack,
        ConfigResolverInterface $configResolver
    ) {
        parent::__construct($twig, $translator);

        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->permissionResolver = $permissionResolver;
        $this->datasetFactory = $datasetFactory;
        $this->requestStack = $requestStack;
        $this->configResolver = $configResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'my-drafts';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return /** @Desc("Drafts") */
            $this->translator->trans('tab.name.my_drafts', [], 'ibexa_dashboard');
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder(): int
    {
        return 100;
    }

    /**
     * Get information about tab presence.
     *
     * @param array $parameters
     *
     * @return bool
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function evaluate(array $parameters): bool
    {
        // hide tab if user has absolutely no access to content/versionread
        return false !== $this->permissionResolver->hasAccess('content', 'versionread');
    }

    /**
     * @param array $parameters
     *
     * @return string
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderView(array $parameters): string
    {
        $currentPage = $this->requestStack->getCurrentRequest()->query->getInt(
            self::PAGINATION_PARAM_NAME,
            1
        );

        $pagination = new Pagerfanta(
            new ContentDraftAdapter($this->contentService, $this->datasetFactory)
        );
        $pagination->setMaxPerPage($this->configResolver->getParameter('pagination.content_draft_limit'));
        $pagination->setCurrentPage(min(max($currentPage, 1), $pagination->getNbPages()));

        return $this->twig->render('@ibexadesign/ui/dashboard/tab/my_draft_list.html.twig', [
            'data' => $pagination->getCurrentPageResults(),
            'pager' => $pagination,
            // merge pager options, prioritizing the ones passed via $parameters
            'pager_options' => ($parameters['pager_options'] ?? []) + [
                'pageParameter' => '[' . self::PAGINATION_PARAM_NAME . ']',
            ],
        ]);
    }
}
