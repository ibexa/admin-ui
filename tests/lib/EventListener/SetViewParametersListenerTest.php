<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\EventListener;

use Ibexa\AdminUi\EventListener\SetViewParametersListener;
use Ibexa\ContentForms\Content\View\ContentEditView;
use Ibexa\ContentForms\User\View\UserUpdateView;
use Ibexa\Contracts\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProviderInterface;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content as API;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\User\User as APIUser;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Event\PreContentViewEvent;
use Ibexa\Core\MVC\Symfony\MVCEvents;
use Ibexa\Core\MVC\Symfony\View\View;
use Ibexa\Core\Repository\Values\Content as Core;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\User\User as CoreUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;

final class SetViewParametersListenerTest extends TestCase
{
    private const EXAMPLE_LOCATION_A_ID = 1;
    private const EXAMPLE_LOCATION_B_ID = 2;
    private const EXAMPLE_OWNER_ID = 14;

    /** @var \Ibexa\Core\MVC\Symfony\Event\PreContentViewEvent */
    private $event;

    /** @var \Ibexa\AdminUi\EventListener\SetViewParametersListener */
    private $viewParametersListener;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService|\PHPUnit\Framework\MockObject\MockObject */
    private $locationService;

    /** @var \Ibexa\Contracts\Core\Repository\UserService|\PHPUnit\Framework\MockObject\MockObject */
    private $userService;

    /** @var \Ibexa\Contracts\Core\Repository\Repository|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private ConfigResolverInterface $configResolver;

    /** @var \Ibexa\Contracts\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $groupedContentFormFieldsProvider;

    public function setUp(): void
    {
        $contentInfo = $this->generateContentInfo();

        $versionInfo = $this->generateVersionInfo($contentInfo);

        $contentView = new ContentEditView();
        $contentView->setParameters(['content' => $this->generateContent($versionInfo)]);

        $this->event = new PreContentViewEvent($contentView);

        $this->locationService = $this->createMock(LocationService::class);
        $this->userService = $this->createMock(UserService::class);
        $this->repository = $this->createMock(Repository::class);

        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->configResolver
            ->method('getParameter')
            ->with('admin_ui_forms.content_edit.fieldtypes')
            ->willReturn(
                [
                    'ibexa_taxonomy_entry_assignment' => [
                        'meta' => true,
                    ],
                ]
            );

        $this->groupedContentFormFieldsProvider = $this->createMock(GroupedContentFormFieldsProviderInterface::class);

        $this->viewParametersListener = new SetViewParametersListener(
            $this->locationService,
            $this->userService,
            $this->repository,
            $this->configResolver,
            $this->groupedContentFormFieldsProvider
        );
    }

    public function testSetViewTemplateParameters(): void
    {
        $locationA = new Core\Location(['id' => self::EXAMPLE_LOCATION_A_ID]);
        $locationB = new Core\Location(['id' => self::EXAMPLE_LOCATION_B_ID]);
        $locations = [$locationA, $locationB];

        $contentInfo = $this->generateContentInfo();

        $versionInfo = $this->generateVersionInfo($contentInfo);
        $content = $this->generateContent($versionInfo);
        $location = $this->generateLocation();

        $contentView = new ContentEditView();
        $contentView->setParameters([
            'content' => $content,
            'location' => $location,
        ]);

        $this->locationService
            ->method('loadParentLocationsForDraftContent')
            ->with($versionInfo)
            ->willReturn($locations);

        $this->repository
            ->method('sudo')
            ->willReturn([$locationA]);

        $this->viewParametersListener->setContentEditViewTemplateParameters(new PreContentViewEvent($contentView));

        $this->assertSame($locations, $contentView->getParameter('parent_locations'));
    }

    /**
     * @param int|null $parentLocationId
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location
     */
    private function generateLocation(int $parentLocationId = null): API\Location
    {
        return new Core\Location(['id' => 3, 'parentLocationId' => $parentLocationId]);
    }

    public function testSetViewTemplateParametersWithMainLocationId(): void
    {
        $mainLocationId = 123;
        $parentLocationId = 456;
        $published = true;

        $parentLocation = new Core\Location(['id' => $parentLocationId]);
        $parentLocations = [$parentLocation];
        $contentInfo = $this->generateContentInfo($mainLocationId, $published);
        $versionInfo = $this->generateVersionInfo($contentInfo);
        $content = $this->generateContent($versionInfo);
        $location = $this->generateLocation($parentLocationId);

        $contentView = new ContentEditView();
        $contentView->setParameters([
            'content' => $content,
            'location' => $location,
            'parent_locations' => [],
        ]);

        $this->locationService
            ->method('loadParentLocationsForDraftContent')
            ->with($versionInfo)
            ->willReturn($parentLocations);
        $this->locationService
            ->method('loadLocation')
            ->with($parentLocationId)
            ->willReturn(reset($parentLocations));
        $this->repository
            ->method('sudo')
            ->willReturn($parentLocation);

        $this->viewParametersListener->setContentEditViewTemplateParameters(new PreContentViewEvent($contentView));

        $this->assertSame([], $contentView->getParameter('parent_locations'));
        $this->assertSame(reset($parentLocations), $contentView->getParameter('parent_location'));
    }

    public function testSetViewTemplateParametersWithoutContentEditViewInstance(): void
    {
        $contentView = $this->createMock(View::class);

        $this->locationService->expects(self::never())
            ->method('loadParentLocationsForDraftContent');

        $this->assertNull(
            $this->viewParametersListener->setContentEditViewTemplateParameters(
                new PreContentViewEvent($contentView)
            )
        );
    }

    public function testSetUserUpdateViewTemplateParametersWithoutUserUpdateViewInstance(): void
    {
        $view = $this->createMock(View::class);

        $this->locationService->expects(self::never())
            ->method('loadParentLocationsForDraftContent');

        $this->assertNull(
            $this->viewParametersListener->setUserUpdateViewTemplateParameters(
                new PreContentViewEvent($view)
            )
        );
    }

    public function testSetUserUpdateViewTemplateParameters(): void
    {
        $ownerId = 42;

        $user = $this->generateUser($ownerId);

        $userUpdateView = new UserUpdateView();
        $userUpdateView->setParameters([
            'user' => $user,
        ]);

        $this->userService
            ->method('loadUser')
            ->with($ownerId)
            ->willReturn($user);

        $this->viewParametersListener->setUserUpdateViewTemplateParameters(new PreContentViewEvent($userUpdateView));

        $this->assertSame($user, $userUpdateView->getParameter('creator'));
    }

    public function testSetGroupedFieldsParameter(): void
    {
        $fields = [
            'name' => 'ezstring',
            'short_name' => 'ezstring',
            'description' => 'ezrichtext',
            'tags' => 'ibexa_taxonomy_entry_assignment',
        ];

        $fieldsDataChildren = [];
        foreach ($fields as $identifier => $type) {
            $fieldsDataChildren[$identifier] = $this->createFieldMock($identifier, $type);
        }

        $fieldsDataForm = $this->createMock(FormInterface::class);
        $fieldsDataForm
            ->method('all')
            ->willReturn($fieldsDataChildren);

        $form = $this->createMock(FormInterface::class);
        $form
            ->method('get')
            ->with('fieldsData')
            ->willReturn($fieldsDataForm);

        $contentEditView = new ContentEditView();
        $contentEditView->setForm($form);

        $groupedFields = [
            'Content' => ['name', 'short_name', 'description'],
        ];

        $this->groupedContentFormFieldsProvider
            ->method('getGroupedFields')
            ->with($fieldsDataChildren)
            ->willReturn($groupedFields);

        $this->viewParametersListener->setGroupedFieldsParameter(new PreContentViewEvent($contentEditView));

        $this->assertSame($groupedFields, $contentEditView->getParameter('grouped_fields'));
    }

    public function testSetIgnoredContentFieldsParameter(): void
    {
        $fields = [
            'name' => 'ezstring',
            'short_name' => 'ezstring',
            'description' => 'ezrichtext',
            'tags' => 'ibexa_taxonomy_entry_assignment',
        ];

        $fieldsDataChildren = [];
        foreach ($fields as $identifier => $type) {
            $fieldsDataChildren[$identifier] = $this->createFieldMock($identifier, $type);
        }

        $fieldsDataForm = $this->createMock(FormInterface::class);
        $fieldsDataForm
            ->method('all')
            ->willReturn($fieldsDataChildren);

        $form = $this->createMock(FormInterface::class);
        $form
            ->method('get')
            ->with('fieldsData')
            ->willReturn($fieldsDataForm);

        $contentEditView = new ContentEditView();
        $contentEditView->setForm($form);

        $ignoredContentFields = ['tags'];

        $this->viewParametersListener->setGroupedFieldsParameter(new PreContentViewEvent($contentEditView));

        $this->assertSame($ignoredContentFields, $contentEditView->getParameter('ignored_content_fields'));
    }

    public function testSubscribedEvents(): void
    {
        $this->locationService
            ->expects(self::never())
            ->method('loadParentLocationsForDraftContent');

        $expectedSubscribedEvents = [
            MVCEvents::PRE_CONTENT_VIEW => [
                ['setContentEditViewTemplateParameters', 10],
                ['setUserUpdateViewTemplateParameters', 5],
                ['setContentTranslateViewTemplateParameters', 10],
                ['setContentCreateViewTemplateParameters', 10],
                ['setGroupedFieldsParameter', 20],
            ],
        ];

        $actualSubscribedEvents = $this->viewParametersListener::getSubscribedEvents();

        $this->assertCount(count($actualSubscribedEvents), $expectedSubscribedEvents);
        foreach ($expectedSubscribedEvents as $key => $value) {
            $this->assertArrayHasKey($key, $actualSubscribedEvents);
            $this->assertSame($value, $actualSubscribedEvents[$key]);
        }
    }

    /**
     * @param int $mainLocationId
     * @param bool $published
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo
     */
    private function generateContentInfo(int $mainLocationId = null, bool $published = false): API\ContentInfo
    {
        return new API\ContentInfo([
            'mainLocationId' => $mainLocationId,
            'ownerId' => self::EXAMPLE_OWNER_ID,
            'published' => $published,
        ]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo
     */
    private function generateVersionInfo(API\ContentInfo $contentInfo): API\VersionInfo
    {
        return new Core\VersionInfo(['contentInfo' => $contentInfo]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Content
     */
    private function generateContent(API\VersionInfo $versionInfo): API\Content
    {
        return new Core\Content(['versionInfo' => $versionInfo]);
    }

    /**
     * @param int $ownerId
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\User
     */
    private function generateUser(int $ownerId): APIUser
    {
        $contentInfo = new API\ContentInfo(['ownerId' => $ownerId]);

        $versionInfo = new Core\VersionInfo(['contentInfo' => $contentInfo]);

        $content = $this->generateContent($versionInfo);

        return new CoreUser(['content' => $content]);
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Field|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createFieldMock(string $identifier, string $type): MockObject
    {
        $data = new FieldData([
            'field' => new Field([
                'fieldDefIdentifier' => $identifier,
                'fieldTypeIdentifier' => $type,
            ]),
            'fieldDefinition' => new FieldDefinition([
                'fieldTypeIdentifier' => $type,
            ]),
        ]);

        $field = $this->createMock(FormInterface::class);
        $field
            ->method('getData')
            ->willReturn($data);

        return $field;
    }
}

class_alias(SetViewParametersListenerTest::class, 'EzSystems\EzPlatformAdminUi\Tests\EventListener\SetViewParametersListenerTest');
