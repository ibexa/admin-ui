<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\REST;

use Ibexa\Contracts\Test\Rest\WebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

/**
 * Coverage for /bulk REST endpoint.
 */
final class PostBulkOperationTest extends WebTestCase
{
    #[DataProvider('provideRestUris')]
    public function testDispatchesRestOperation(string $uri): void
    {
        $this->performBulkRequest([
            'sections' => [
                'uri' => $uri,
                'method' => 'GET',
                'headers' => ['Accept' => 'application/vnd.ibexa.api.SectionList+json'],
            ],
        ]);

        self::assertResponseIsSuccessful();

        $response = json_decode(
            (string)$this->client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $operationResponse = $response['BulkOperationResponse']['operations']['sections'];

        self::assertSame(Response::HTTP_OK, $operationResponse['statusCode']);
        self::assertStringContainsString('SectionList', $operationResponse['content']);
    }

    #[DataProvider('provideNonRestUris')]
    public function testRejectsOperationWithNonRestUri(string $uri): void
    {
        $this->performBulkRequest([
            'operation' => [
                'uri' => $uri,
                'method' => 'GET',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertStringContainsString(
            'Bulk operation URI must point to a REST resource.',
            (string)$this->client->getResponse()->getContent()
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideRestUris(): iterable
    {
        yield 'relative URI' => ['/api/ibexa/v2/content/sections'];
        yield 'absolute URI' => ['http://localhost/api/ibexa/v2/content/sections'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNonRestUris(): iterable
    {
        yield 'fragment with controller call' => ['/_fragment?_path=_controller%3Dreadfile%26filename%3D%252Fo'];
        yield 'fragment behind REST prefix' => [
            '/api/ibexa/v2/_fragment?_path=_controller%3Dreadfile%26filename%3D%252Fo',
        ];
        yield 'unknown REST route' => ['/api/ibexa/v2/does-not-exist'];
        yield 'non-REST route' => ['/dashboard'];
    }

    /**
     * @param array<string, array{uri: string, method: string, headers?: array<string, string>}> $operations
     */
    private function performBulkRequest(array $operations): void
    {
        $this->client->request(
            'POST',
            '/api/ibexa/v2/bulk',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/vnd.ibexa.api.BulkOperation+json',
                'HTTP_ACCEPT' => 'application/vnd.ibexa.api.BulkOperationResponse+json',
                'HTTP_X-SiteAccess' => 'admin',
            ],
            json_encode(['bulkOperations' => ['operations' => $operations]], JSON_THROW_ON_ERROR)
        );
    }
}
