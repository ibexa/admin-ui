<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Form\Data\Content\CustomUrl\CustomUrlAddData;
use Ibexa\AdminUi\Form\Data\Content\CustomUrl\CustomUrlRemoveData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Specification\Location\IsRoot;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\Helper\TranslationHelper;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class UrlsTab extends AbstractEventDispatchingTab implements OrderedTabInterface
{
    public const string URI_FRAGMENT = 'ibexa-tab-location-view-urls';

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        protected readonly URLAliasService $urlAliasService,
        protected readonly FormFactory $formFactory,
        protected readonly DatasetFactory $datasetFactory,
        protected readonly LocationService $locationService,
        protected readonly PermissionResolver $permissionResolver,
        EventDispatcherInterface $eventDispatcher,
        private readonly TranslationHelper $translationHelper
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);
    }

    public function getIdentifier(): string
    {
        return 'urls';
    }

    public function getName(): string
    {
        /** @Desc("URL") */
        return $this->translator->trans('tab.name.urls', [], 'ibexa_locationview');
    }

    public function getOrder(): int
    {
        return 400;
    }

    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/urls.html.twig';
    }

    public function getTemplateParameters(array $contextParameters = []): array
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
        $location = $contextParameters['location'];

        $customUrlsPaginationParams = $contextParameters['custom_urls_pagination_params'];
        $systemUrlsPaginationParams = $contextParameters['system_urls_pagination_params'];

        $customUrlsDataset = $this->datasetFactory->customUrls();
        $customUrlsDataset->load($location);

        $customUrlPagerfanta = new Pagerfanta(
            new ArrayAdapter($customUrlsDataset->getCustomUrlAliases())
        );

        $customUrlPagerfanta->setMaxPerPage($customUrlsPaginationParams['limit']);
        $customUrlPagerfanta->setCurrentPage(min(
            $customUrlsPaginationParams['page'],
            $customUrlPagerfanta->getNbPages()
        ));

        $systemUrlPagerfanta = new Pagerfanta(
            new ArrayAdapter(
                iterator_to_array(
                    $this->urlAliasService->listLocationAliases($location, false, null, true)
                )
            )
        );

        $systemUrlPagerfanta->setMaxPerPage($systemUrlsPaginationParams['limit']);
        $systemUrlPagerfanta->setCurrentPage(min(
            $systemUrlsPaginationParams['page'],
            $systemUrlPagerfanta->getNbPages()
        ));

        $customUrlAddForm = $this->createCustomUrlAddForm($location);
        $customUrlRemoveForm = $this->createCustomUrlRemoveForm(
            $location,
            iterator_to_array($customUrlPagerfanta->getCurrentPageResults())
        );

        $canEditCustomUrl = $this->permissionResolver->hasAccess('content', 'urltranslator');

        $viewParameters = [
            'form_custom_url_add' => $customUrlAddForm->createView(),
            'form_custom_url_remove' => $customUrlRemoveForm->createView(),
            'custom_urls_pager' => $customUrlPagerfanta,
            'custom_urls_pagination_params' => $customUrlsPaginationParams,
            'system_urls_pager' => $systemUrlPagerfanta,
            'system_urls_pagination_params' => $systemUrlsPaginationParams,
            'can_edit_custom_url' => $canEditCustomUrl,
            'parent_name' => null,
        ];

        try {
            $parentLocation = $this->locationService->loadLocation($location->parentLocationId);
            if (!(new IsRoot())->isSatisfiedBy($location)) {
                $viewParameters['parent_name'] = $this->translationHelper->getTranslatedContentName(
                    $parentLocation->getContent()
                );
            }
        } catch (UnauthorizedException) {
            // do nothing
        }

        return array_replace($contextParameters, $viewParameters);
    }

    private function createCustomUrlAddForm(Location $location): FormInterface
    {
        $customUrlAddData = new CustomUrlAddData($location);

        return $this->formFactory->addCustomUrl($customUrlAddData);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\URLAlias[] $customUrlAliases
     */
    private function createCustomUrlRemoveForm(
        Location $location,
        array $customUrlAliases
    ): FormInterface {
        $customUrlRemoveData = new CustomUrlRemoveData($location, $this->getChoices($customUrlAliases));

        return $this->formFactory->removeCustomUrl($customUrlRemoveData);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\URLAlias[] $customUrlAliases
     *
     * @return mixed[]
     */
    private function getChoices(array $customUrlAliases): array
    {
        $urlAliasIdList = array_column($customUrlAliases, 'id');

        return array_combine($urlAliasIdList, array_fill_keys($urlAliasIdList, false));
    }
}
