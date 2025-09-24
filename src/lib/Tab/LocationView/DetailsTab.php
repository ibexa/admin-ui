<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\LocationView;

use ArrayObject;
use Ibexa\AdminUi\Form\Data\Location\LocationAssignSubtreeData;
use Ibexa\AdminUi\Form\Data\Location\LocationUpdateData;
use Ibexa\AdminUi\Form\Data\ObjectState\ContentObjectStateUpdateData;
use Ibexa\AdminUi\Form\Type\Location\LocationAssignSectionType;
use Ibexa\AdminUi\Form\Type\Location\LocationUpdateType;
use Ibexa\AdminUi\Form\Type\ObjectState\ContentObjectStateUpdateType;
use Ibexa\AdminUi\Specification\UserMode\IsFocusModeEnabled;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\ConditionalTabInterface;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\User\UserSetting\UserSettingService;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class DetailsTab extends AbstractEventDispatchingTab implements OrderedTabInterface, ConditionalTabInterface
{
    public const string URI_FRAGMENT = 'ibexa-tab-location-view-details';

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        private readonly SectionService $sectionService,
        private readonly DatasetFactory $datasetFactory,
        private readonly FormFactoryInterface $formFactory,
        private readonly PermissionResolver $permissionResolver,
        private readonly UserSettingService $userSettingService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);
    }

    public function getIdentifier(): string
    {
        return 'details';
    }

    public function getName(): string
    {
        /** @Desc("Technical details") */
        return $this->translator->trans('tab.name.details', [], 'ibexa_locationview');
    }

    public function getOrder(): int
    {
        return 750;
    }

    public function evaluate(array $parameters): bool
    {
        return IsFocusModeEnabled
            ::fromUserSettings($this->userSettingService)
            ->isSatisfiedBy(FocusMode::FOCUS_MODE_OFF);
    }

    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/details.html.twig';
    }

    public function getTemplateParameters(array $contextParameters = []): array
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
        $content = $contextParameters['content'];
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
        $location = $contextParameters['location'];

        $versionInfo = $content->getVersionInfo();
        $contentInfo = $versionInfo->getContentInfo();

        $viewParameters = new ArrayObject([
            'content_info' => $contentInfo,
            'version_info' => $versionInfo,
        ]);

        $this->supplySectionParameters($viewParameters, $contentInfo, $location);
        $this->supplyObjectStateParameters($viewParameters, $contentInfo);
        $this->supplyTranslations($viewParameters, $versionInfo);
        $this->supplyFormLocationUpdate($viewParameters, $location);
        $this->supplySortFieldClauseMap($viewParameters);

        return array_replace($contextParameters, $viewParameters->getArrayCopy());
    }

    /**
     * @param \ArrayObject<string, mixed> $parameters
     */
    private function supplySortFieldClauseMap(ArrayObject $parameters): void
    {
        $parameters['sort_field_clause_map'] = [
            Location::SORT_FIELD_PATH => 'LocationPath',
            Location::SORT_FIELD_PUBLISHED => 'DatePublished',
            Location::SORT_FIELD_MODIFIED => 'DateModified',
            Location::SORT_FIELD_SECTION => 'SectionIdentifier',
            Location::SORT_FIELD_DEPTH => 'LocationDepth',
            Location::SORT_FIELD_PRIORITY => 'LocationPriority',
            Location::SORT_FIELD_NAME => 'ContentName',
            Location::SORT_FIELD_NODE_ID => 'LocationId',
            Location::SORT_FIELD_CONTENTOBJECT_ID => 'ContentId',
        ];
    }

    /**
     * @param \ArrayObject<string, mixed> $parameters
     */
    private function supplyObjectStateParameters(ArrayObject $parameters, ContentInfo $contentInfo): void
    {
        $objectStatesDataset = $this->datasetFactory->objectStates();
        $objectStatesDataset->load($contentInfo);

        $canAssignObjectState = $this->canUserAssignObjectState();

        $parameters['object_states'] = $objectStatesDataset->getObjectStates();
        $parameters['can_assign'] = $canAssignObjectState;
        $parameters['form_state_update'] = [];

        if ($canAssignObjectState) {
            foreach ($objectStatesDataset->getObjectStates() as $objectState) {
                $objectStateGroup = $objectState->getObjectStateGroup();
                $objectStateUpdateForm = $this->formFactory->create(
                    ContentObjectStateUpdateType::class,
                    new ContentObjectStateUpdateData(
                        $contentInfo,
                        $objectStateGroup,
                        $objectState
                    )
                )->createView();

                $parameters['form_state_update'][$objectStateGroup->id] = $objectStateUpdateForm;
            }
        }
    }

    /**
     * Specifies if the User has access to assigning a given Object State to Content Info.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function canUserAssignObjectState(): bool
    {
        return $this->permissionResolver->hasAccess('state', 'assign') !== false;
    }

    /**
     * @param \ArrayObject<string, mixed> $parameters
     */
    private function supplySectionParameters(ArrayObject $parameters, ContentInfo $contentInfo, Location $location): void
    {
        $canSeeSection = $this->permissionResolver->canUser('section', 'view', $contentInfo);

        $parameters['section'] = null;
        $parameters['can_see_section'] = $canSeeSection;
        $parameters['form_assign_section'] = null;

        if ($canSeeSection) {
            $section = $this->sectionService->loadSection($contentInfo->getSectionId());
            $parameters['section'] = $section;

            $canAssignSection = $this->permissionResolver->hasAccess('section', 'assign');
            if ($canAssignSection) {
                $assignSectionToSubtreeForm = $this->formFactory->create(
                    LocationAssignSectionType::class,
                    new LocationAssignSubtreeData(
                        $section,
                        $location
                    )
                )->createView();

                $parameters['form_assign_section'] = $assignSectionToSubtreeForm;
            }
        }
    }

    /**
     * @param \ArrayObject<string, mixed> $parameters
     */
    private function supplyFormLocationUpdate(ArrayObject $parameters, Location $location): void
    {
        $parameters['form_location_update'] = $this->formFactory->create(
            LocationUpdateType::class,
            new LocationUpdateData($location)
        )->createView();
    }

    /**
     * @param \ArrayObject<string, mixed> $parameters
     */
    private function supplyTranslations(ArrayObject $parameters, VersionInfo $versionInfo): void
    {
        $translationsDataset = $this->datasetFactory->translations();
        $translationsDataset->load($versionInfo);

        $parameters['translations'] = $translationsDataset->getTranslations();
    }
}
