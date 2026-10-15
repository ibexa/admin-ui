<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\UniversalDiscovery;

use Ibexa\Contracts\AdminUi\UniversalDiscovery\Provider;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DatePublished;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SubtreeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\Test\IbexaKernelTestCase;
use Ibexa\Rest\Server\Values\RestLocation;

/**
 * @covers \Ibexa\AdminUi\UniversalDiscovery\UniversalDiscoveryProvider
 */
final class UniversalDiscoveryProviderTest extends IbexaKernelTestCase
{
    private const HOME_LOCATION_ID = 2;

    private Provider $provider;

    private Location $departments;

    private Location $blog;

    protected function setUp(): void
    {
        self::bootKernel();
        self::setAdministratorUser();

        $this->provider = self::getServiceByClassName(Provider::class);

        $this->departments = $this->createFolder('Departments', self::HOME_LOCATION_ID);
        $this->blog = $this->createFolder('Blog', $this->departments->id);
        $this->createFolder('Other department', $this->departments->id);
    }

    public function testColumnsKeepAncestorsTheUserCannotRead(): void
    {
        self::getPermissionResolver()->setCurrentUserReference($this->createBlogReader());

        $columns = $this->provider->getColumns($this->blog->id, 10, new DatePublished());

        self::assertNull($columns[self::HOME_LOCATION_ID]['location']);
        self::assertNull($columns[$this->departments->id]['location']);
        self::assertSame(
            [$this->blog->id],
            self::getSubitemLocationIds($columns[$this->departments->id]['subitems']['locations'])
        );
    }

    public function testColumnsContainReadableAncestors(): void
    {
        $columns = $this->provider->getColumns($this->blog->id, 10, new DatePublished());

        self::assertInstanceOf(Location::class, $columns[self::HOME_LOCATION_ID]['location']);
        self::assertInstanceOf(Location::class, $columns[$this->departments->id]['location']);
        self::assertSame($this->departments->id, $columns[$this->departments->id]['location']->id);
    }

    private function createFolder(
        string $name,
        int $parentLocationId
    ): Location {
        $contentService = self::getContentService();
        $locationService = self::getLocationService();

        $contentCreateStruct = $contentService->newContentCreateStruct(
            self::getContentTypeService()->loadContentTypeByIdentifier('folder'),
            'eng-GB'
        );
        $contentCreateStruct->setField('name', $name);

        $content = $contentService->publishVersion(
            $contentService->createContent(
                $contentCreateStruct,
                [$locationService->newLocationCreateStruct($parentLocationId)]
            )->getVersionInfo()
        );

        $location = $content->getVersionInfo()->getContentInfo()->getMainLocation();
        self::assertNotNull($location);

        return $location;
    }

    private function createBlogReader(): User
    {
        $userService = self::getUserService();
        $roleService = self::getRoleService();

        $userCreateStruct = $userService->newUserCreateStruct('blog_reader', 'blog_reader@test.local', 'blog_reader', 'eng-GB');
        $userCreateStruct->setField('first_name', 'Blog');
        $userCreateStruct->setField('last_name', 'Reader');
        $user = $userService->createUser($userCreateStruct, [$userService->loadUserGroup(4)]);

        $roleCreateStruct = $roleService->newRoleCreateStruct('blog_reader');
        foreach (['read', 'versionread'] as $function) {
            $policyCreateStruct = $roleService->newPolicyCreateStruct('content', $function);
            $policyCreateStruct->addLimitation(
                new SubtreeLimitation(['limitationValues' => [$this->blog->pathString]])
            );
            $roleCreateStruct->addPolicy($policyCreateStruct);
        }
        $roleDraft = $roleService->createRole($roleCreateStruct);
        $roleService->publishRoleDraft($roleDraft);
        $roleService->assignRoleToUser($roleService->loadRole($roleDraft->id), $user);

        return $user;
    }

    /**
     * The selected location is moved on top of its parent column as a plain Location.
     *
     * @param array<RestLocation|Location> $locations
     *
     * @return array<int>
     */
    private static function getSubitemLocationIds(array $locations): array
    {
        return array_map(
            static fn (object $location): int => $location instanceof RestLocation
                ? $location->location->id
                : $location->id,
            $locations
        );
    }
}
