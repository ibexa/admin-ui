<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Config\AdminUiForms;

/**
 * @internal
 */
interface ContentTypeFieldTypesResolverInterface
{
    /**
     * @return array<string, array{
     *     'position': int,
     *     'meta'?: bool,
     * }>
     */
    public function getFieldTypes(): array;

    /**
     * @return array<string>
     */
    public function getMetaFieldTypeIdentifiers(): array;
}
