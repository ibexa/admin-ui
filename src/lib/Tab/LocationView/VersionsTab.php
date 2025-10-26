<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Form\Data\Content\Draft\ContentEditData;
use Ibexa\AdminUi\Form\Data\Version\VersionRemoveData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Specification\ContentIsUser;
use Ibexa\AdminUi\Specification\UserMode\IsFocusModeEnabled;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\AdminUi\UI\Value\Content\VersionInfo;
use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\ConditionalTabInterface;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\User\UserSetting\UserSettingService;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class VersionsTab extends AbstractEventDispatchingTab implements OrderedTabInterface, ConditionalTabInterface
{
    public const FORM_REMOVE_DRAFT = 'version_remove_draft';
    public const FORM_REMOVE_ARCHIVED = 'version_remove_archived';
    public const URI_FRAGMENT = 'ibexa-tab-location-view-versions';

    /** @var DatasetFactory */
    protected $datasetFactory;

    /** @var FormFactory */
    protected $formFactory;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /** @var PermissionResolver */
    protected $permissionResolver;

    /** @var UserService */
    private $userService;

    private UserSettingService $userSettingService;

    /**
     * @param Environment $twig
     * @param TranslatorInterface $translator
     * @param DatasetFactory $datasetFactory
     * @param FormFactory $formFactory
     * @param UrlGeneratorInterface $urlGenerator
     * @param PermissionResolver $permissionResolver
     * @param UserService $userService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        DatasetFactory $datasetFactory,
        FormFactory $formFactory,
        UrlGeneratorInterface $urlGenerator,
        PermissionResolver $permissionResolver,
        UserService $userService,
        UserSettingService $userSettingService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);

        $this->datasetFactory = $datasetFactory;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
        $this->userSettingService = $userSettingService;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'versions';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        /** @Desc("Versions") */
        return $this->translator->trans('tab.name.versions', [], 'ibexa_locationview');
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 300;
    }

    /**
     * Get information about tab presence.
     *
     * @param array $parameters
     *
     * @return bool
     *
     * @throws BadStateException
     * @throws InvalidArgumentException
     */
    public function evaluate(array $parameters): bool
    {
        $isFocusModeOff = IsFocusModeEnabled::fromUserSettings($this->userSettingService)->isSatisfiedBy(FocusMode::FOCUS_MODE_OFF);
        if ($isFocusModeOff) {
            return $this->permissionResolver->canUser('content', 'versionread', $parameters['content']);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/versions/tab.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateParameters(array $contextParameters = []): array
    {
        /** @var Content $content */
        $content = $contextParameters['content'];
        /** @var Location $location */
        $location = $contextParameters['location'];

        $draftPaginationParams = $contextParameters['draft_pagination_params'];

        $versionInfo = $content->getVersionInfo();
        $contentInfo = $versionInfo->getContentInfo();
        $versionsDataset = $this->datasetFactory->versions();
        $versionsDataset->load($contentInfo);

        $draftPagerfanta = new Pagerfanta(
            new ArrayAdapter($versionsDataset->getDraftVersions())
        );

        $draftPagerfanta->setMaxPerPage($draftPaginationParams['limit']);
        $draftPagerfanta->setCurrentPage(min($draftPaginationParams['page'], $draftPagerfanta->getNbPages()));

        /** @var VersionInfo[] $policies */
        $draftVersions = $draftPagerfanta->getCurrentPageResults();

        $archivedVersions = $versionsDataset->getArchivedVersions();

        $removeVersionDraftForm = $this->createVersionRemoveForm(
            $location,
            $draftVersions,
            true
        );
        $removeVersionArchivedForm = $this->createVersionRemoveForm(
            $location,
            $archivedVersions,
            false
        );
        $archivedVersionRestoreForm = $this->formFactory->contentEdit(
            new ContentEditData($contentInfo, null, null, $location),
            'archived_version_restore'
        );

        $parameters = [
            'versions_dataset' => $versionsDataset,
            'published_versions' => $versionsDataset->getPublishedVersions(),
            'archived_versions' => $archivedVersions,
            'form_version_remove_draft' => $removeVersionDraftForm->createView(),
            'form_version_remove_archived' => $removeVersionArchivedForm->createView(),
            'form_archived_version_restore' => $archivedVersionRestoreForm->createView(),
            'draft_pager' => $draftPagerfanta,
            'draft_pagination_params' => $draftPaginationParams,
            'content_is_user' => (new ContentIsUser($this->userService))->isSatisfiedBy($content),
        ];

        return array_replace($contextParameters, $parameters);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo[] $versions
     *
     * @return array
     */
    private function getVersionNumbers(array $versions): array
    {
        $versionNumbers = array_column($versions, 'versionNo');

        return array_combine($versionNumbers, array_fill_keys($versionNumbers, false));
    }

    /**
     * @param Location $location
     * @param array $versions
     * @param bool $isDraftForm
     *
     * @return FormInterface
     */
    private function createVersionRemoveForm(
        Location $location,
        array $versions,
        bool $isDraftForm
    ): FormInterface {
        $contentInfo = $location->getContentInfo();
        $data = new VersionRemoveData($contentInfo, $this->getVersionNumbers($versions));

        $formName = sprintf(
            'version-remove-%s',
            $isDraftForm
            ? self::FORM_REMOVE_DRAFT
            : self::FORM_REMOVE_ARCHIVED
        );

        return $this->formFactory->removeVersion($data, $formName);
    }
}

class_alias(VersionsTab::class, 'EzSystems\EzPlatformAdminUi\Tab\LocationView\VersionsTab');
