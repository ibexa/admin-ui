<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\REST;

use Ibexa\Contracts\Test\Rest\Input\PayloadLoader;
use Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition;

/**
 * Coverage for /content-type/load-field-definitions-from-expression REST endpoint.
 */
final class PostLoadFieldDefinitionsFromExpressionTest extends BaseAdminUiRestWebTestCase
{
    private const INPUT_MEDIA_TYPE = 'FieldDefinitionExpression';

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException
     */
    protected function setUp(): void
    {
        parent::setUp();

        // to create a new user before logging-in via REST
        $this->getIbexaTestCore()->setAdministratorUser();

        $this->loginAsUser(
            $this->createUserWithPolicies(
                'editor',
                [
                    'user/login' => [],
                    'content/read' => [],
                    'content/versionread' => [],
                ]
            )
        );
    }

    protected static function getEndpointsToTest(): iterable
    {
        $payloadLoader = new PayloadLoader(dirname(__DIR__) . '/Resources/REST/InputPayloads');

        foreach (self::REQUIRED_FORMATS as $format) {
            yield new EndpointRequestDefinition(
                'POST',
                '/api/ibexa/v2/content-type/load-field-definitions-from-expression',
                'FieldDefinitionInfoList',
                "application/vnd.ibexa.api.FieldDefinitionInfoList+$format",
                ['HTTP_X-SiteAccess' => 'admin'],
                $payloadLoader->loadPayload(self::INPUT_MEDIA_TYPE, 'json'),
                null,
                'FieldDefinitionInfoList'
            );

            yield new EndpointRequestDefinition(
                'POST',
                '/api/ibexa/v2/content-type/load-field-definitions-from-expression',
                'FieldDefinitionInfoList',
                "application/vnd.ibexa.api.FieldDefinitionInfoList+$format",
                ['HTTP_X-SiteAccess' => 'admin'],
                $payloadLoader->loadPayload(
                    self::INPUT_MEDIA_TYPE,
                    'json',
                    'FieldDefinitionExpressionWithConfiguration',
                ),
                null,
                'FieldDefinitionInfoListWithConfiguration'
            );
        }
    }
}
