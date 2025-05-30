<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Form\Data\Content\Location\ContentLocationAddData;
use Ibexa\AdminUi\Form\Data\Content\Location\ContentLocationRemoveData;
use Ibexa\AdminUi\Form\Data\Content\Location\ContentMainLocationUpdateData;
use Ibexa\AdminUi\Form\Data\Location\LocationSwapData;
use Ibexa\AdminUi\Form\Data\Location\LocationUpdateVisibilityData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Specification\UserMode\IsFocusModeEnabled;
use Ibexa\AdminUi\UI\Value\Content\Location\Mapper;
use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\ConditionalTabInterface;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Pagination\Pagerfanta\LocationSearchAdapter;
use Ibexa\User\UserSetting\UserSettingService;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class LocationsTab extends AbstractEventDispatchingTab implements OrderedTabInterface, ConditionalTabInterface
{
    public const URI_FRAGMENT = 'ibexa-tab-location-view-locations';
    private const PAGINATION_PARAM_NAME = 'locations-tab-page';

    protected FormFactory $formFactory;

    protected UrlGeneratorInterface $urlGenerator;

    protected PermissionResolver $permissionResolver;

    private RequestStack $requestStack;

    private SearchService $searchService;

    private Mapper $locationToUILocationMapper;

    private ConfigResolverInterface $configResolver;

    private UserSettingService $userSettingService;

    /**
     * @param \Twig\Environment $twig
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     * @param \Ibexa\AdminUi\Form\Factory\FormFactory $formFactory
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     * @param \Ibexa\Contracts\Core\Repository\PermissionResolver $permissionResolver
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Ibexa\Contracts\Core\Repository\SearchService $searchService
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Ibexa\AdminUi\UI\Value\Content\Location\Mapper$locationToUILocationMapper
     * @param \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolver
     */
    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        FormFactory $formFactory,
        UrlGeneratorInterface $urlGenerator,
        PermissionResolver $permissionResolver,
        EventDispatcherInterface $eventDispatcher,
        SearchService $searchService,
        RequestStack $requestStack,
        Mapper $locationToUILocationMapper,
        ConfigResolverInterface $configResolver,
        UserSettingService $userSettingService
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);

        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->permissionResolver = $permissionResolver;
        $this->requestStack = $requestStack;
        $this->configResolver = $configResolver;
        $this->searchService = $searchService;
        $this->locationToUILocationMapper = $locationToUILocationMapper;
        $this->userSettingService = $userSettingService;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'locations';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        /** @Desc("Locations") */
        return $this->translator->trans('tab.name.locations', [], 'ibexa_locationview');
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 400;
    }

    public function evaluate(array $parameters): bool
    {
        return IsFocusModeEnabled::fromUserSettings($this->userSettingService)->isSatisfiedBy(FocusMode::FOCUS_MODE_OFF);
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/locations/tab.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateParameters(array $contextParameters = []): array
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
        $content = $contextParameters['content'];
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
        $location = $contextParameters['location'];
        $versionInfo = $content->getVersionInfo();
        $contentInfo = $versionInfo->getContentInfo();
        $locations = [];
        $pagination = null;
        $defaultPaginationLimit = $this->configResolver->getParameter('pagination.location_limit');

        if ($contentInfo->published) {
            $currentPage = $this->requestStack->getCurrentRequest()->query->getInt(
                self::PAGINATION_PARAM_NAME,
                1
            );

            $locationQuery = new LocationQuery([
                'filter' => new Query\Criterion\ContentId($contentInfo->id),
            ]);

            $pagination = new Pagerfanta(
                new LocationSearchAdapter(
                    $locationQuery,
                    $this->searchService
                )
            );

            $pagination->setMaxPerPage($defaultPaginationLimit);
            $pagination->setCurrentPage(max($currentPage, 1));
            $locationsArray = iterator_to_array($pagination);
            $locations = $this->locationToUILocationMapper->map($locationsArray);
        }

        $formLocationAdd = $this->createLocationAddForm($location);
        $formLocationRemove = $this->createLocationRemoveForm($location, $locations);
        $formLocationSwap = $this->createLocationSwapForm($location);
        $formLocationUpdateVisibility = $this->createLocationUpdateVisibilityForm($location);
        $formLocationMainUpdate = $this->createLocationUpdateMainForm($contentInfo, $location);
        $canManageLocations = $this->permissionResolver->canUser(
            'content',
            'manage_locations',
            $location->getContentInfo()
        );
        // We grant access to choose a valid Location from UDW. Now it is not possible to filter locations
        // and show only those which user has access to
        $canCreate = false !== $this->permissionResolver->hasAccess('content', 'create');
        $canEdit = $this->permissionResolver->canUser(
            'content',
            'edit',
            $location->getContentInfo()
        );
        $canHide = [];
        foreach ($locations as $location) {
            $canHide[$location->id] = $this->permissionResolver->canUser(
                'content',
                'hide',
                $location->getContentInfo(),
                [$location]
            );
        }

        $viewParameters = [
            'pager' => $pagination,
            'pager_options' => [
                'pageParameter' => sprintf('[%s]', self::PAGINATION_PARAM_NAME),
            ],
            'locations' => $locations,
            'form_content_location_add' => $formLocationAdd->createView(),
            'form_content_location_remove' => $formLocationRemove->createView(),
            'form_content_location_swap' => $formLocationSwap->createView(),
            'form_content_location_update_visibility' => $formLocationUpdateVisibility->createView(),
            'form_content_location_main_update' => $formLocationMainUpdate->createView(),
            'can_swap' => $canEdit,
            'can_add' => $canManageLocations && $canCreate,
            'can_hide' => $canHide,
        ];

        return array_replace($contextParameters, $viewParameters);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createLocationAddForm(Location $location): FormInterface
    {
        return $this->formFactory->addLocation(
            new ContentLocationAddData($location->getContentInfo())
        );
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     * @param \Ibexa\AdminUi\UI\Value\Content\Location[] $contentLocations
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createLocationRemoveForm(Location $location, array $contentLocations): FormInterface
    {
        return $this->formFactory->removeLocation(
            new ContentLocationRemoveData($location->getContentInfo(), $this->getLocationChoices($contentLocations))
        );
    }

    /**
     * @param \Ibexa\AdminUi\UI\Value\Content\Location[] $locations
     *
     * @return array
     */
    private function getLocationChoices(array $locations): array
    {
        $locationIds = array_column($locations, 'id');

        return array_combine($locationIds, array_fill_keys($locationIds, false));
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createLocationSwapForm(Location $location): FormInterface
    {
        return $this->formFactory->swapLocation(
            new LocationSwapData($location)
        );
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createLocationUpdateVisibilityForm(Location $location): FormInterface
    {
        return $this->formFactory->updateVisibilityLocation(
            new LocationUpdateVisibilityData($location)
        );
    }

    /**
     * @param $contentInfo
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createLocationUpdateMainForm($contentInfo, Location $location): FormInterface
    {
        return $this->formFactory->updateContentMainLocation(
            new ContentMainLocationUpdateData($contentInfo, $location)
        );
    }
}
