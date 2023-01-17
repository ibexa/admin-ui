<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\AdminUi\View\ContentTranslateView;
use Ibexa\ContentForms\Content\View\ContentCreateView;
use Ibexa\ContentForms\Content\View\ContentEditView;
use Ibexa\ContentForms\User\View\UserUpdateView;
use Ibexa\Contracts\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProviderInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\MVC\Symfony\Event\PreContentViewEvent;
use Ibexa\Core\MVC\Symfony\MVCEvents;
use Ibexa\Core\MVC\Symfony\View\View;
use Ibexa\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @todo It should use ViewEvents::FILTER_VIEW_PARAMETERS event instead.
 */
class SetViewParametersListener implements EventSubscriberInterface
{
    protected LocationService $locationService;

    protected UserService $userService;

    private Repository $repository;

    private GroupedContentFormFieldsProviderInterface $groupedContentFormFieldsProvider;

    private ConfigResolverInterface $configResolver;

    public function __construct(
        LocationService $locationService,
        UserService $userService,
        Repository $repository,
        ConfigResolverInterface $configResolver,
        GroupedContentFormFieldsProviderInterface $groupedContentFormFieldsProvider
    ) {
        $this->locationService = $locationService;
        $this->userService = $userService;
        $this->repository = $repository;
        $this->configResolver = $configResolver;
        $this->groupedContentFormFieldsProvider = $groupedContentFormFieldsProvider;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [
            MVCEvents::PRE_CONTENT_VIEW => [
                ['setContentEditViewTemplateParameters', 10],
                ['setUserUpdateViewTemplateParameters', 5],
                ['setContentTranslateViewTemplateParameters', 10],
                ['setContentCreateViewTemplateParameters', 10],
                ['setContentFieldsParameters', 20],
            ],
        ];
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\Event\PreContentViewEvent $event
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function setContentEditViewTemplateParameters(PreContentViewEvent $event): void
    {
        $contentView = $event->getContentView();

        if (!$contentView instanceof ContentEditView) {
            return;
        }

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
        $content = $contentView->getParameter('content');
        $location = $contentView->hasParameter('location') ? $contentView->getParameter('location') : null;
        $isPublished = null !== $content->contentInfo->mainLocationId && $content->contentInfo->published;

        $contentView->addParameters([
            'parent_location' => $this->resolveParentLocation($content, $location, $isPublished),
            'is_published' => $isPublished,
        ]);

        if (!$isPublished) {
            $contentView->addParameters([
                'parent_locations' => $this->locationService->loadParentLocationsForDraftContent($content->versionInfo),
            ]);
        }

        $contentInfo = $content->versionInfo->contentInfo;

        $this->processCreator($contentInfo, $contentView);
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\Event\PreContentViewEvent $event
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function setContentTranslateViewTemplateParameters(PreContentViewEvent $event): void
    {
        $contentView = $event->getContentView();

        if (!$contentView instanceof ContentTranslateView) {
            return;
        }

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
        $content = $contentView->getContent();
        $location = $contentView->getLocation();
        $isPublished = null !== $content->contentInfo->mainLocationId && $content->contentInfo->published;

        $contentView->addParameters([
            'parent_location' => $this->resolveParentLocation($content, $location, $isPublished),
            'is_published' => $isPublished,
        ]);

        if (!$isPublished) {
            $contentView->addParameters([
                'parent_locations' => $this->locationService->loadParentLocationsForDraftContent($content->versionInfo),
            ]);
        }

        $contentInfo = $content->versionInfo->contentInfo;

        $this->processCreator($contentInfo, $contentView);
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\Event\PreContentViewEvent $event
     */
    public function setUserUpdateViewTemplateParameters(PreContentViewEvent $event): void
    {
        $contentView = $event->getContentView();

        if (!$contentView instanceof UserUpdateView) {
            return;
        }

        /** @var \Ibexa\Contracts\Core\Repository\Values\User\User $user */
        $user = $contentView->getParameter('user');
        $contentInfo = $user->versionInfo->contentInfo;

        $this->processCreator($contentInfo, $contentView);
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\Event\PreContentViewEvent $event
     */
    public function setContentCreateViewTemplateParameters(PreContentViewEvent $event): void
    {
        $contentView = $event->getContentView();

        if (!$contentView instanceof ContentCreateView) {
            return;
        }

        $contentView->addParameters([
            'content_create_struct' => $contentView->getForm()->getData(),
        ]);
    }

    public function setContentFieldsParameters(PreContentViewEvent $event): void
    {
        $view = $event->getContentView();
        if (!$view instanceof ContentEditView) {
            return;
        }

        $parameters = $view->getParameters();
        $parameters['grouped_fields'] = $this->groupedContentFormFieldsProvider->getGroupedFields(
            $view->getForm()->get('fieldsData')->all()
        );
        $parameters['ignored_content_fields'] = $this->getIgnoredContentFields($view->getForm()->get('fieldsData')->all());

        $view->setParameters($parameters);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo
     * @param \Ibexa\Core\MVC\Symfony\View\View $contentView
     */
    private function processCreator(ContentInfo $contentInfo, View $contentView): void
    {
        try {
            $creator = $this->userService->loadUser($contentInfo->ownerId);
        } catch (NotFoundException $exception) {
            $creator = null;
        }

        $contentView->addParameters([
            'creator' => $creator,
        ]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     * @param bool $isPublished
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function resolveParentLocation(Content $content, ?Location $location, bool $isPublished): Location
    {
        if (!$isPublished) {
            $parentLocations = $this->repository->sudo(
                static function (Repository $repository) use ($content) {
                    return $repository->getLocationService()->loadParentLocationsForDraftContent($content->getVersionInfo());
                }
            );

            return reset($parentLocations);
        }

        if (null === $location) {
            throw new InvalidArgumentException('$location', 'You must provide a Location for the published Content item');
        }

        return $this->repository->sudo(
            static function (Repository $repository) use ($location) {
                return $repository->getLocationService()->loadLocation($location->parentLocationId);
            }
        );
    }

    /**
     * @param array<\Symfony\Component\Form\FormInterface> $fieldsData
     *
     * @return array<string>
     */
    private function getIgnoredContentFields(array $fieldsData): array
    {
        $fieldTypeConfig = $this->configResolver->getParameter('admin_ui_forms.content_edit.fieldtypes');

        $ignoredFieldTypes = array_keys(
            array_filter(
                $fieldTypeConfig,
                static fn (array $config): bool => true === $config['meta']
            )
        );

        $ignoredFieldIdentifiers = [];
        foreach ($fieldsData as $fieldIdentifier => $fieldData) {
            if (!in_array($fieldData->getData()->fieldDefinition->fieldTypeIdentifier, $ignoredFieldTypes, true)) {
                continue;
            }

            $ignoredFieldIdentifiers[] = $fieldIdentifier;
        }

        $metaFieldGroups = $this->configResolver->getParameter(
            'admin_ui_forms.content_edit.meta_fieldgroup_list'
        );
        $metaFieldIdentifiers = array_keys(
            array_filter(
                $fieldsData,
                static fn (FormInterface $field): bool => true === in_array($field->getData()->fieldDefinition->fieldGroup, $metaFieldGroups)
            )
        );

        return array_merge($ignoredFieldIdentifiers, $metaFieldIdentifiers);
    }
}

class_alias(SetViewParametersListener::class, 'EzSystems\EzPlatformAdminUi\EventListener\SetViewParametersListener');
