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
use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\ConditionalTabInterface;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\User\UserSetting\UserSettingService;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class VersionsTab extends AbstractEventDispatchingTab implements OrderedTabInterface, ConditionalTabInterface
{
    public const string FORM_REMOVE_DRAFT = 'version_remove_draft';
    public const string FORM_REMOVE_ARCHIVED = 'version_remove_archived';
    public const string URI_FRAGMENT = 'ibexa-tab-location-view-versions';

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        private readonly DatasetFactory $datasetFactory,
        private readonly FormFactory $formFactory,
        private readonly PermissionResolver $permissionResolver,
        protected readonly UserService $userService,
        protected readonly UserSettingService $userSettingService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);
    }

    public function getIdentifier(): string
    {
        return 'versions';
    }

    public function getName(): string
    {
        /** @Desc("Versions") */
        return $this->translator->trans('tab.name.versions', [], 'ibexa_locationview');
    }

    public function getOrder(): int
    {
        return 300;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function evaluate(array $parameters): bool
    {
        $isFocusModeOff = IsFocusModeEnabled
            ::fromUserSettings($this->userSettingService)
            ->isSatisfiedBy(FocusMode::FOCUS_MODE_OFF);

        if ($isFocusModeOff) {
            return $this->permissionResolver->canUser(
                'content',
                'versionread',
                $parameters['content']
            );
        }

        return false;
    }

    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/versions/tab.html.twig';
    }

    public function getTemplateParameters(array $contextParameters = []): array
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
        $content = $contextParameters['content'];
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
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
        $draftPagerfanta->setCurrentPage(
            min($draftPaginationParams['page'], $draftPagerfanta->getNbPages())
        );

        /** @var \Ibexa\AdminUi\UI\Value\Content\VersionInfo[] $draftVersions */
        $draftVersions = iterator_to_array($draftPagerfanta->getCurrentPageResults());

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
     * @return mixed[]
     */
    private function getVersionNumbers(array $versions): array
    {
        $versionNumbers = array_column($versions, 'versionNo');

        return array_combine($versionNumbers, array_fill_keys($versionNumbers, false));
    }

    /**
     * @param mixed[] $versions
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
