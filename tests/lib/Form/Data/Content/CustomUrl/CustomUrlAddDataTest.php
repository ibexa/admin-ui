<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\Data\Content\CustomUrl;

use Ibexa\AdminUi\Form\Data\Content\CustomUrl\CustomUrlAddData;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;

class CustomUrlAddDataTest extends TestCase
{
    public function testConstruct(): void
    {
        $location = new Location(['id' => 2]);
        $language = new Language(['languageCode' => 'eng-GB']);
        $path = '/test';
        $siteAccess = 'site3';

        $data = new CustomUrlAddData($location, $path, $language, false, true, $siteAccess);

        self::assertSame($location, $data->getLocation());
        self::assertSame($language, $data->getLanguage());
        self::assertSame($path, $data->getPath());
        self::assertSame($siteAccess, $data->getSiteAccess());
    }
}

class_alias(CustomUrlAddDataTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Form\Data\Content\CustomUrl\CustomUrlAddDataTest');
