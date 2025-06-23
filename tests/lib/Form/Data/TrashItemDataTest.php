<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\Data;

use Ibexa\AdminUi\Form\Data\TrashItemData;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\Content\TrashItem;
use PHPUnit\Framework\TestCase;

final class TrashItemDataTest extends TestCase
{
    public function testIsParentInTrashReturnsFalseWhenNoAncestors(): void
    {
        $trashItem = new TrashItem(['path' => ['1', '2', '3'], 'id' => 3]);
        $data = new TrashItemData($trashItem, null, []);

        self::assertFalse($data->isParentInTrash());
    }

    public function testIsParentInTrashReturnsFalseWhenPathsMatch(): void
    {
        $trashItem = new TrashItem(['path' => ['1', '2', '3'], 'id' => 3]);

        $ancestor = new Location(['path' => ['1', '2']]);
        $data = new TrashItemData($trashItem, null, [$ancestor]);

        self::assertFalse($data->isParentInTrash());
    }

    public function testIsParentInTrashReturnsTrueWhenPathsDoNotMatch(): void
    {
        $trashItem = new TrashItem(['path' => ['1', '2', '3'], 'id' => 3]);
        $ancestor = new Location(['path' => ['1']]);

        $data = new TrashItemData($trashItem, null, [$ancestor]);

        self::assertTrue($data->isParentInTrash());
    }

    public function testIsParentInTrashWithMultipleAncestors(): void
    {
        $trashItem = new TrashItem(['path' => ['1', '2', '3', '4'], 'id' => 4]);

        $ancestor1 = new Location(['path' => ['1']]);
        $ancestor2 = new Location(['path' => ['1', '2', '3']]);

        $data = new TrashItemData($trashItem, null, [$ancestor1, $ancestor2]);

        self::assertFalse($data->isParentInTrash());
    }
}
