<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\View\Filter;

use Ibexa\AdminUi\Form\Type\Search\SearchType;
use Ibexa\AdminUi\Specification\SiteAccess\IsAdmin;
use Ibexa\Bundle\Search\Form\Data\SearchData;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent;
use Ibexa\Core\MVC\Symfony\View\ViewEvents;
use Ibexa\Search\View\SearchViewFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class AdminSearchViewFilter implements EventSubscriberInterface
{
    /**
     * @param array<string, string[]> $siteAccessGroups
     */
    public function __construct(
        private ConfigResolverInterface $configResolver,
        private FormFactoryInterface $formFactory,
        private SectionService $sectionService,
        private ContentTypeService $contentTypeService,
        private array $siteAccessGroups,
        private SearchViewFilter $innerFilter,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [ViewEvents::FILTER_BUILDER_PARAMETERS => 'handleSearchForm'];
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\NoSuchOptionException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function handleSearchForm(FilterViewBuilderParametersEvent $event): void
    {
        $controllerAction = $event->getParameters()->get('_controller');

        if (
            'Ibexa\Bundle\Search\Controller\SearchController::searchAction' !== $controllerAction
        ) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->isAdminSiteAccess($request)) {
            $this->innerFilter->handleSearchForm($event);

            return;
        }

        $search = $request->query->all('search');
        $limit = isset($search['limit'])
            ? (int)$search['limit']
            : $this->configResolver->getParameter('search.pagination.limit');

        $page = isset($search['page']) ? (int)$search['page'] : 1;
        $query = $search['query'] ?? '';
        $section = null;
        $creator = null;
        $contentTypes = [];
        $lastModified = $search['last_modified'] ?? [];
        $created = $search['created'] ?? [];
        $subtree = $search['subtree'] ?? null;
        $searchLanguage = null;

        if (!empty($search['section'])) {
            try {
                $section = $this->sectionService->loadSection((int)$search['section']);
            } catch (NotFoundException) {
                // do nothing
            }
        }

        if (!empty($search['content_types']) && is_array($search['content_types'])) {
            foreach ($search['content_types'] as $identifier) {
                $contentTypes[] = $this->contentTypeService->loadContentTypeByIdentifier($identifier);
            }
        }

        $form = $this->formFactory->create(
            SearchType::class,
            new SearchData(
                $limit,
                $page,
                $query,
                $section,
                $contentTypes,
                $lastModified,
                $created,
                $creator,
                $subtree,
                $searchLanguage
            ),
            [
                'method' => Request::METHOD_GET,
                'csrf_protection' => false,
                'action' => $this->urlGenerator->generate('ibexa.search'),
            ]
        );

        $event->getParameters()->add([
            'form' => $form->handleRequest($request),
        ]);
    }

    private function isAdminSiteAccess(Request $request): bool
    {
        return (new IsAdmin($this->siteAccessGroups))
            ->isSatisfiedBy(
                $request->attributes->get('siteaccess')
            );
    }
}
