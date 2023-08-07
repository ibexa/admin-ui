<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\Repository\SectionService;

/**
 * Provides information about sections.
 */
class Sections implements ProviderInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\SectionService */
    private $sectionService;

    public function __construct(
        SectionService $sectionService
    ) {
        $this->sectionService = $sectionService;
    }

    public function getConfig(): array
    {
        $sections = $this->sectionService->loadSections();
        $config = [];

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Section $section */
        foreach ($sections as $section) {
            $config[$section->identifier] = $section->name;
        }

        return $config;
    }
}

class_alias(Sections::class, 'EzSystems\EzPlatformAdminUi\UI\Config\Provider\Sections');
