<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tests\Playwright;

use Ibexa\CohesivoPlaywright\Fixture\ContentFixtureFactory;
use Ibexa\CohesivoPlaywright\Fixture\IbexaPlaywrightTestCase;
use Ibexa\CohesivoPlaywright\Page\AdminUi\ContentManagementPage;
use Ibexa\CohesivoPlaywright\Page\AdminUi\TrashPage;
use PHPUnit\Framework\Attributes\Group;

/**
 * All 6 trash scenarios: empty, move to trash, restore, restore under new location, delete, search.
 * Runs on all 4 editions, mirroring Trash.spec.ts's own tag: ['@IbexaOSS', '@IbexaHeadless', '@IbexaExperience', '@IbexaCommerce'].
 */
#[Group('oss')]
#[Group('headless')]
#[Group('experience')]
#[Group('commerce')]
final class TrashTest extends IbexaPlaywrightTestCase
{
    private ContentFixtureFactory $fixtures;

    private int $trashTestLocationId;

    private int $trashTestContentId;

    private string $runId;

    /** Instance-context setUp(), not static: a static setUpBeforeClass() leaves the "kernel" service unset. */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = new ContentFixtureFactory($this->repository(), $this->persistenceCachePool());
        $this->runId = substr((string) (int) (microtime(true) * 1000), -6);

        [$this->trashTestContentId, $this->trashTestLocationId] = $this->fixtures->createFolder(
            'TrashTest' . $this->runId,
            2, // Ibexa DXP content node
        );
        $this->cleanup(fn () => $this->fixtures->deleteContent($this->trashTestContentId));
    }

    private function sendToTrash(
        int $contentId,
        int $locationId
    ): void {
        $page = $this->visit(ContentManagementPage::route($contentId, $locationId));
        // visit() itself already asserts via the real navigation response (PoC architecture spike).
        $contentPage = new ContentManagementPage($page);
        $contentPage->assertOpened();
        $contentPage->sendToTrash();
    }

    private function openTrash(): TrashPage
    {
        $page = $this->visit(TrashPage::ROUTE);
        // visit() itself already asserts via the real navigation response (PoC architecture spike).
        $trash = new TrashPage($page);
        $trash->waitUntilLoaded();

        return $trash;
    }

    public function testTrashCanBeEmptied(): void
    {
        $this->logIn();
        [$childId, $childLocId] = $this->fixtures->createFolder('FolderToTrash' . $this->runId, $this->trashTestLocationId);

        $this->sendToTrash($childId, $childLocId);

        $trash = $this->openTrash();
        $trash->assertNotEmpty();
        $trash->emptyTrash();
        $trash->assertEmpty();
    }

    public function testContentCanBeMovedToTrash(): void
    {
        $this->logIn();
        $name = 'FolderToTrashManually' . $this->runId;
        [$childId, $childLocId] = $this->fixtures->createFolder($name, $this->trashTestLocationId);

        $page = $this->visit(ContentManagementPage::route($childId, $childLocId));
        $contentPage = new ContentManagementPage($page);
        $contentPage->assertOpened();
        $contentPage->sendToTrash();
        $contentPage->notifications->assertSuccess("Location '{$name}' moved to Trash");

        $trash = $this->openTrash();
        $trash->assertItemInTrash($name);
    }

    public function testElementInTrashCanBeDeleted(): void
    {
        $this->logIn();
        $name = 'DeleteFromTrash' . $this->runId;
        [$childId, $childLocId] = $this->fixtures->createFolder($name, $this->trashTestLocationId);
        $this->sendToTrash($childId, $childLocId);

        $trash = $this->openTrash();
        $trash->assertItemInTrash($name);
        $trash->deleteFromTrash([$name]);
        $trash->notifications->assertSuccess('Deleted selected item(s) from Trash');
        $trash->assertItemNotInTrash($name);
    }

    public function testElementInTrashCanBeRestored(): void
    {
        $this->logIn();
        $name = 'RestoreFromTrash' . $this->runId;
        [$childId, $childLocId] = $this->fixtures->createFolder($name, $this->trashTestLocationId);
        $this->sendToTrash($childId, $childLocId);

        $trash = $this->openTrash();
        $trash->assertItemInTrash($name);
        $trash->restoreFromTrash([$name]);
        $trash->notifications->assertSuccess('Restored content to its original Location');
        $trash->assertItemNotInTrash($name);
    }

    public function testElementInTrashCanBeRestoredUnderNewLocation(): void
    {
        $this->logIn();
        $name = 'RestoreFromTrashNewLocation' . $this->runId;
        [$childId, $childLocId] = $this->fixtures->createFolder($name, $this->trashTestLocationId);
        // Registered now: once restored elsewhere, it's no longer under
        // $this->trashTestLocationId and needs its own cleanup.
        $this->cleanup(fn () => $this->fixtures->deleteContent($childId));
        $this->sendToTrash($childId, $childLocId);

        $trash = $this->openTrash();
        $trash->assertItemInTrash($name);
        $trash->restoreUnderNewLocation([$name], 'Media/Files');

        $trash->notifications->assertSuccess("Restored content under Location 'Files'");
        $trash->assertItemNotInTrash($name);

        // Verify the content actually landed under Media/Files (throws if absent).
        $restoredContentId = $this->fixtures->getContentIdByPath("Media/Files/{$name}");
        self::assertSame($childId, $restoredContentId);
    }

    public function testElementInTrashCanBeFoundBySearch(): void
    {
        $this->logIn();
        $name1 = 'TrashSearch1' . $this->runId;
        $name2 = 'TrashSearch2' . $this->runId;
        [$childId1, $childLocId1] = $this->fixtures->createFolder($name1, $this->trashTestLocationId);
        [$childId2, $childLocId2] = $this->fixtures->createFolder($name2, $this->trashTestLocationId);

        $this->sendToTrash($childId1, $childLocId1);
        $this->sendToTrash($childId2, $childLocId2);

        $trash = $this->openTrash();
        $trash->searchInTrash($name1);
        $trash->assertItemInTrash($name1);
        $trash->assertItemNotInTrash($name2);
    }
}
