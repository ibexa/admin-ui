<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\REST;

use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\User\UserCreateStruct;
use Ibexa\Contracts\Test\Rest\WebTestCase;
use LogicException;

final class GetUsersWithPermissionInfoTest extends WebTestCase
{
    private const ENDPOINT_URL = 'permission/users-with-permission-info/%s/%s?%s';
    private const HEADERS = [
        'HTTP_ACCEPT' => 'application/json',
        'X-Siteaccess' => 'admin',
    ];
    private const EDITOR_USER_GROUP_REMOTE_ID = '3c160cca19fb135f83bd02d911f04db2';
    private const USER_REGISTRATION_REMOTE_ID = '5f7f0bdb3381d6a461d8c29ff53d908f';
    private const MEDIA_CONTENT_ITEM_ID = 41;
    private const MEDIA_LOCATION_ID = 43;
    private const MODULE_CONTENT = 'content';
    private const FUNCTION_EDIT = 'edit';
    private const FUNCTION_READ = 'read';

    private UserService $userService;

    private RoleService $roleService;

    protected function setUp(): void
    {
        parent::setUp();

        $ibexaTestCore = $this->getIbexaTestCore();
        $this->userService = $ibexaTestCore->getUserService();
        $this->roleService = $ibexaTestCore->getRoleService();

        $this->createUsers();
    }

    /**
     * @dataProvider provideDataForTestGetUsersWithPermissionsEndpoint
     *
     * @param array<string, string> $queryParameters
     */
    public function testGetUsersWithPermissionsEndpoint(
        int $contentId,
        string $module,
        string $function,
        array $queryParameters,
        string $expectedResponse
    ): void {
        $uri = $this->getUri($module, $function, $queryParameters);
        $this->client->request('GET', $uri, [], [], self::HEADERS);

        $response = $this->client->getResponse();

        self::assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        if (false === $content) {
            throw new LogicException('Missing response content');
        }

        $fixedResponse = $this->doReplaceResponse($content);

        self::assertSame($expectedResponse, $fixedResponse);
    }

    /**
     * @return iterable<array{
     *     int,
     *     string,
     *     string,
     *     array<string, mixed>,
     *     string,
     * }>
     */
    public function provideDataForTestGetUsersWithPermissionsEndpoint(): iterable
    {
        yield 'Check content-read for content item 41' => [
            self::MEDIA_CONTENT_ITEM_ID,
            self::MODULE_CONTENT,
            self::FUNCTION_READ,
            ['contentId' => 41],
            '{"access":[{"id":"__FIXED_ID__","name":"Administrator User","email":"admin@link.invalid"},{"id":"__FIXED_ID__","name":"John Doe","email":"john@link.invalid"},{"id":"__FIXED_ID__","name":"Josh Bar","email":"joshua@link.invalid"}],"no_access":[{"id":"__FIXED_ID__","name":"Anonymous User","email":"anonymous@link.invalid"},{"id":"__FIXED_ID__","name":"Guest Guest","email":"guest@link.invalid"}]}',
        ];

        yield 'Check content-read for content item 41 and location 51' => [
            self::MEDIA_CONTENT_ITEM_ID,
            self::MODULE_CONTENT,
            self::FUNCTION_READ,
            [
                'contentId' => 41,
                'locationId' => 51,
            ],
            '{"access":[{"id":"__FIXED_ID__","name":"Administrator User","email":"admin@link.invalid"},{"id":"__FIXED_ID__","name":"John Doe","email":"john@link.invalid"},{"id":"__FIXED_ID__","name":"Josh Bar","email":"joshua@link.invalid"}],"no_access":[{"id":"__FIXED_ID__","name":"Anonymous User","email":"anonymous@link.invalid"},{"id":"__FIXED_ID__","name":"Guest Guest","email":"guest@link.invalid"}]}',
        ];

        yield 'Check content-read for phrase=undef*' => [
            self::MEDIA_CONTENT_ITEM_ID,
            self::MODULE_CONTENT,
            self::FUNCTION_READ,
            [
                'contentId' => 41,
                'phrase' => 'undef*',
            ],
            '{"access":[],"no_access":[]}',
        ];

        yield 'Check content-edit for content item 2 and phrase=jo' => [
            self::MEDIA_CONTENT_ITEM_ID,
            self::MODULE_CONTENT,
            self::FUNCTION_EDIT,
            [
                'contentId' => 41,
                'phrase' => 'jo*',
            ],
            '{"access":[{"id":"__FIXED_ID__","name":"John Doe","email":"john@link.invalid"},{"id":"__FIXED_ID__","name":"Josh Bar","email":"joshua@link.invalid"}],"no_access":[]}',
        ];

        yield 'Check content-edit for content item 41 and phrase=bar*' => [
            self::MEDIA_CONTENT_ITEM_ID,
            self::MODULE_CONTENT,
            self::FUNCTION_EDIT,
            [
                'contentId' => 41,
                'phrase' => 'bar*',
            ],
            '{"access":[{"id":"__FIXED_ID__","name":"Josh Bar","email":"joshua@link.invalid"}],"no_access":[]}',
        ];

        yield 'Check content-edit for content item 41 and location 43 and phrase=joshua@link.invalid' => [
            self::MEDIA_CONTENT_ITEM_ID,
            self::MODULE_CONTENT,
            self::FUNCTION_EDIT,
            [
                'phrase' => 'joshua@link.invalid',
                'contentId' => 41,
                'locationId' => self::MEDIA_LOCATION_ID,
            ],
            '{"access":[{"id":"__FIXED_ID__","name":"Josh Bar","email":"joshua@link.invalid"}],"no_access":[]}',
        ];
    }

    private function createUsers(): void
    {
        $this->getIbexaTestCore()->setAdministratorUser();

        $role = $this->roleService->loadRoleByIdentifier('Editor');
        $editorGroup = $this->userService->loadUserGroupByRemoteId(self::EDITOR_USER_GROUP_REMOTE_ID);

        $user1CreateStruct = $this->createUserCreateStruct('john', 'John', 'Doe', 'john@link.invalid');
        $user1 = $this->userService->createUser($user1CreateStruct, [$editorGroup]);
        $this->roleService->assignRoleToUser($role, $user1);

        $user2CreateStruct = $this->createUserCreateStruct('josh', 'Josh', 'Bar', 'joshua@link.invalid');
        $user2 = $this->userService->createUser($user2CreateStruct, [$editorGroup]);
        $this->roleService->assignRoleToUser($role, $user2);

        // Guest user should not be visible on the list
        $guestCreateStruct = $this->createUserCreateStruct('guest', 'Guest', 'Guest', 'guest@link.invalid');
        $groupGuest = $this->userService->loadUserGroupByRemoteId(self::USER_REGISTRATION_REMOTE_ID);
        $guest = $this->userService->createUser($guestCreateStruct, [$groupGuest]);

        $roleMember = $this->roleService->loadRoleByIdentifier('Member');
        $this->roleService->assignRoleToUser($roleMember, $guest);
    }

    private function createUserCreateStruct(
        string $login,
        string $firstName,
        string $lastName,
        string $email
    ): UserCreateStruct {
        $userCreateStruct = $this->userService->newUserCreateStruct(
            $login,
            $email,
            $login,
            'eng-GB'
        );

        $userCreateStruct->setField('first_name', $firstName);
        $userCreateStruct->setField('last_name', $lastName);

        return $userCreateStruct;
    }

    /**
     * @param array<string, string> $queryParameters
     */
    private static function getUri(
        string $module,
        string $function,
        array $queryParameters = []
    ): string {
        return sprintf(
            self::ENDPOINT_URL,
            $module,
            $function,
            http_build_query($queryParameters),
        );
    }

    private function doReplaceResponse(string $jsonResponse): string
    {
        $fixedResponse = preg_replace('~"id":\d+~', '"id":"__FIXED_ID__"', $jsonResponse);
        if (null === $fixedResponse) {
            throw new LogicException('Failed to replace JSON response.');
        }

        return $fixedResponse;
    }
}
