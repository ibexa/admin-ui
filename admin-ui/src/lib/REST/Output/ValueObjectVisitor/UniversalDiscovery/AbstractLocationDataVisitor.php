<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Output\ValueObjectVisitor\UniversalDiscovery;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Rest\Server\Values\Version;

/**
 * @phpstan-import-type PermissionRestrictions from \Ibexa\AdminUi\REST\Value\UniversalDiscovery\LocationData
 */
abstract class AbstractLocationDataVisitor extends ValueObjectVisitor
{
    protected function buildLocationNode(
        ?Location $location,
        Generator $generator,
        Visitor $visitor
    ): void {
        if (null === $location) {
            return;
        }

        $generator->startHashElement('location');
        $visitor->visitValueObject($location);
        $generator->endHashElement('location');
    }

    /**
     * @phpstan-param PermissionRestrictions $permissionRestrictions
     */
    protected function buildPermissionNode(
        ?array $permissionRestrictions,
        Generator $generator
    ): void {
        if (null === $permissionRestrictions) {
            return;
        }

        $generator->startHashElement('permissions');

        foreach ($permissionRestrictions as $function => $restrictions) {
            $generator->startHashElement($function);
            foreach ($restrictions as $restrictionKey => $restrictionValue) {
                if (is_array($restrictionValue)) {
                    $generator->startList($restrictionKey);
                    foreach ($restrictionValue as $value) {
                        $generator->valueElement($restrictionKey, $value);
                    }
                    $generator->endList($restrictionKey);
                } else {
                    $generator->valueElement($restrictionKey, $restrictionValue);
                }
            }
            $generator->endHashElement($function);
        }

        $generator->endHashElement('permissions');
    }

    protected function buildTotalCountNode(
        int $totalCount,
        Generator $generator
    ): void {
        $generator->valueElement('totalCount', $totalCount);
    }

    protected function buildBookmarkedNode(
        ?bool $isBookmarked,
        Generator $generator
    ): void {
        if (null === $isBookmarked) {
            return;
        }
        $generator->generateFieldTypeHash('bookmarked', $isBookmarked);
    }

    protected function buildVersionNode(
        ?Version $version,
        Generator $generator,
        Visitor $visitor
    ): void {
        if (null === $version) {
            return;
        }

        $generator->startObjectElement('version');
        $visitor->visitValueObject($version);
        $generator->endObjectElement('version');
    }
}
