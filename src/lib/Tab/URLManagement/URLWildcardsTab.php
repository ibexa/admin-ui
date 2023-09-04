<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\URLManagement;

use Ibexa\AdminUi\Form\Data\URLWildcard\URLWildcardDeleteData;
use Ibexa\AdminUi\Form\Data\URLWildcard\URLWildcardListData;
use Ibexa\AdminUi\Form\Type\URLWildcard\URLWildcardDeleteType;
use Ibexa\AdminUi\Form\Type\URLWildcard\URLWildcardListType;
use Ibexa\AdminUi\Form\Type\URLWildcard\URLWildcardType;
use Ibexa\AdminUi\Pagination\Pagerfanta\URLWildcardAdapter;
use Ibexa\Contracts\AdminUi\Tab\AbstractTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\URLWildcardService;
use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard\Query\SortClause;
use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard\URLWildcardQuery;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class URLWildcardsTab extends AbstractTab implements OrderedTabInterface
{
    private const PAGINATION_PARAM_NAME = 'url-wildcards-page';

    public const URI_FRAGMENT = 'ibexa-tab-link-manager-url-wildcards';

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    protected $permissionResolver;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    /** @var \Ibexa\Contracts\Core\Repository\URLWildcardService */
    private $urlWildcardService;

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $formFactory;

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        PermissionResolver $permissionResolver,
        ConfigResolverInterface $configResolver,
        RequestStack $requestStack,
        URLWildcardService $urlWildcardService,
        FormFactoryInterface $formFactory
    ) {
        parent::__construct($twig, $translator);

        $this->permissionResolver = $permissionResolver;
        $this->configResolver = $configResolver;
        $this->requestStack = $requestStack;
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
            $this->translator->trans('tab.name.url_wildcards', [], 'ibexa_url_wildcard');
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
        $limit = $this->configResolver->getParameter('pagination.url_wildcards');
        $currentRequest = $this->requestStack->getCurrentRequest();
        $page = $this->requestStack->getCurrentRequest()->query->getInt(
            self::PAGINATION_PARAM_NAME,
            1
        );

        $data = new URLWildcardListData();
        $data->setLimit($limit);

        $searchUrlWildcardForm = $this->formFactory->create(
            URLWildcardListType::class,
            $data,
            [
                'method' => Request::METHOD_GET,
                'csrf_protection' => false,
            ]
        );

        $searchUrlWildcardForm->handleRequest($currentRequest);

        if ($searchUrlWildcardForm->isSubmitted() && !$searchUrlWildcardForm->isValid()) {
            throw new BadRequestHttpException('The search form is not valid');
        }

        $urlWildcardLists = new Pagerfanta(
            new URLWildcardAdapter(
                $this->buildListQuery($data),
                $this->urlWildcardService
            )
        );

        $urlWildcardLists->setMaxPerPage($data->getLimit());
        $urlWildcardLists->setCurrentPage(min($page, $urlWildcardLists->getNbPages()));

        $urlWildcards = $urlWildcardLists->getCurrentPageResults();
        $urlWildcardsChoices = [];
        foreach ($urlWildcards as $urlWildcardItem) {
            $urlWildcardsChoices[$urlWildcardItem->id] = false;
        }

        $deleteUrlWildcardDeleteForm = $this->formFactory->create(
            URLWildcardDeleteType::class,
            new URLWildcardDeleteData($urlWildcardsChoices)
        );

        $addUrlWildcardForm = $this->formFactory->create(URLWildcardType::class);
        $urlWildcardsEnabled = $this->configResolver->getParameter('url_wildcards.enabled');
        $canManageWildcards = $this->permissionResolver->hasAccess('content', 'urltranslator');

        return $this->twig->render('@ibexadesign/url_wildcard/list.html.twig', [
            'url_wildcards' => $urlWildcardLists,
            'pager_options' => [
                'pageParameter' => '[' . self::PAGINATION_PARAM_NAME . ']',
            ],
            'form' => $deleteUrlWildcardDeleteForm->createView(),
            'form_list' => $searchUrlWildcardForm->createView(),
            'form_add' => $addUrlWildcardForm->createView(),
            'url_wildcards_enabled' => $urlWildcardsEnabled,
            'can_manage' => $canManageWildcards,
        ]);
    }

    private function buildListQuery(URLWildcardListData $data): URLWildcardQuery
    {
        $query = new URLWildcardQuery();
        $query->sortClauses = [
            new SortClause\DestinationUrl(),
        ];

        $criteria = [];

        if ($data->searchQuery !== null) {
            $urlCriterion = [
                new Criterion\DestinationUrl($data->searchQuery),
                new Criterion\SourceUrl($data->searchQuery),
            ];

            $criteria[] = new Criterion\LogicalOr($urlCriterion);
        }

        if ($data->type !== null) {
            $criteria[] = new Criterion\Type($data->type);
        }

        if (empty($criteria)) {
            $criteria[] = new Criterion\MatchAll();
        }

        $query->filter = new Criterion\LogicalAnd($criteria);

        return $query;
    }
}

class_alias(URLWildcardsTab::class, 'EzSystems\EzPlatformAdminUi\Tab\URLManagement\URLWildcardsTab');
