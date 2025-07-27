<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\URLManagement;

use Ibexa\AdminUi\Form\Data\URL\URLListData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Pagination\Pagerfanta\URLSearchAdapter;
use Ibexa\Contracts\AdminUi\Tab\AbstractTab;
use Ibexa\Contracts\AdminUi\Tab\ConditionalTabInterface;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\URLService;
use Ibexa\Contracts\Core\Repository\Values\URL\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\URL\Query\SortClause;
use Ibexa\Contracts\Core\Repository\Values\URL\URLQuery;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class LinkManagerTab extends AbstractTab implements OrderedTabInterface, ConditionalTabInterface
{
    public const string URI_FRAGMENT = 'ibexa-tab-link-manager-link-manager';
    private const int DEFAULT_MAX_PER_PAGE = 10;

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        private readonly URLService $urlService,
        private readonly FormFactory $formFactory,
        private readonly RequestStack $requestStack,
        private readonly PermissionResolver $permissionResolver
    ) {
        parent::__construct($twig, $translator);
    }

    public function getIdentifier(): string
    {
        return 'link-manager';
    }

    public function getName(): string
    {
        return /** @Desc("Link manager") */
            $this->translator->trans('tab.name.link_manager', [], 'ibexa_linkmanager');
    }

    public function getOrder(): int
    {
        return 10;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function evaluate(array $parameters): bool
    {
        return false !== $this->permissionResolver->hasAccess('url', 'view');
    }

    public function renderView(array $parameters): string
    {
        $data = new URLListData();

        $form = $this->formFactory->createUrlListForm($data, '', [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
        ]);

        $request = $this->requestStack->getCurrentRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            throw new BadRequestHttpException();
        }

        $urls = new Pagerfanta(
            new URLSearchAdapter(
                $this->buildListQuery($data),
                $this->urlService
            )
        );

        $page = $request !== null
            ? $request->query->getInt('page', 1)
            : 1;

        $urls->setCurrentPage($page);
        $urls->setMaxPerPage(self::DEFAULT_MAX_PER_PAGE);

        return $this->twig->render('@ibexadesign/link_manager/list.html.twig', [
            'form' => $form->createView(),
            'can_edit' => $this->permissionResolver->hasAccess('url', 'update'),
            'urls' => $urls,
        ]);
    }

    private function buildListQuery(URLListData $data): URLQuery
    {
        $query = new URLQuery();
        $query->sortClauses = [
            new SortClause\URL(),
        ];

        $criteria = [
            new Criterion\VisibleOnly(),
        ];

        if ($data->searchQuery !== null) {
            $criteria[] = new Criterion\Pattern($data->searchQuery);
        }

        if ($data->status !== null) {
            $criteria[] = new Criterion\Validity($data->status);
        }

        $query->filter = new Criterion\LogicalAnd($criteria);

        return $query;
    }
}
