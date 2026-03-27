<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\REST;

use Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition;

/**
 * Coverage for /content/objects/translated-names-list REST endpoint.
 */
final class GetTranslatedNamesTest extends BaseAdminUiRestWebTestCase
{
    private const ENDPOINT_URL = 'api/ibexa/v2/content/objects/translated-names-list';
    private const RESOURCE_TYPE = 'ContentTreeTranslatedNamesList';

    protected static function getEndpointsToTest(): iterable
    {
        foreach (self::REQUIRED_FORMATS as $format) {
            yield new EndpointRequestDefinition(
                'GET',
                self::ENDPOINT_URL . '?content_ids[]=57&content_ids[]=41',
                self::RESOURCE_TYPE,
                self::generateMediaTypeString(self::RESOURCE_TYPE, $format),
                [],
                null,
                null,
                self::RESOURCE_TYPE,
            );
        }
    }
}
