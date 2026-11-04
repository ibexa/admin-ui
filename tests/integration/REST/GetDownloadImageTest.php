<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\REST;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ContentTypeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\RoleLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SectionLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SubtreeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Test\Rest\WebTestCase;
use Ibexa\Core\FieldType\Image\Value as ImageValue;
use Ibexa\Core\MVC\Symfony\Security\UserWrapped;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use ZipArchive;

/**
 * Coverage for the image/download/{contentIdList} REST endpoint: only the images visible to the requesting user are returned.
 */
final class GetDownloadImageTest extends WebTestCase
{
    private const ENDPOINT_URL = '/api/ibexa/v2/image/download/%s';
    private const CONTENT_URL = '/api/ibexa/v2/content/objects/%d';

    private const ROOT_LOCATION_ID = 2;
    private const STANDARD_SECTION_ID = 1;
    private const USERS_GROUP_ID = 4;

    /** Value of the ibexa.dam_widget.image.download_limit parameter */
    private const DOWNLOAD_LIMIT = 25;
    private const UNKNOWN_CONTENT_ID = 999999;

    private const SITEACCESS_ADMIN = 'admin';
    // Anonymous role has user/login only for the siteaccesses defined in the test data
    private const SITEACCESS_ANONYMOUS = 'eng';

    private const ANONYMOUS = null;
    private const USER_WITHOUT_READ = 'download_no_role';
    private const USER_READER = 'download_reader';
    private const USER_SECTION_READER = 'download_section_reader';
    private const USER_SUBTREE_READER = 'download_subtree_reader';
    // Same shape as the DAM "Image Picker Reader" role: the subtree comes from the role assignment
    private const USER_CONTENT_TYPE_AND_SUBTREE_READER = 'download_content_type_and_subtree_reader';
    private const USER_FOLDER_ONLY_READER = 'download_folder_only_reader';

    private const PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    /** @var array<string, Content> */
    private array $images = [];

    /** @var array<string, string> image key => binary content of the stored file */
    private array $files = [];

    /** @var array<string, string> image key => file name stored in the image field */
    private array $fileNames = [];

    /** @var array<string> */
    private array $createdPaths = [];

    private int $allowedSectionId;

    private string $allowedFolderPath;

    private int $allowedFolderContentId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getIbexaTestCore()->setAdministratorUser();

        $this->createImages();
        $this->createUsers();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdPaths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $storageRoot = getcwd() . '/var/';
        foreach ($this->createdPaths as $path) {
            $directory = dirname($path);
            while (str_starts_with($directory, $storageRoot) && is_dir($directory) && 2 === count(scandir($directory))) {
                rmdir($directory);
                $directory = dirname($directory);
            }
        }

        parent::tearDown();
    }

    public function testAnonymousUserDoesNotReceiveImageOutsideOwnScope(): void
    {
        $this->assertContentNotReadable('other_section_image', self::ANONYMOUS);

        $response = $this->requestDownload(['other_section_image'], self::ANONYMOUS);

        $this->assertNothingDownloaded($response, ['other_section_image']);
    }

    public function testUserWithoutReadRoleDoesNotReceiveImage(): void
    {
        $this->assertContentNotReadable('section_allowed_1', self::USER_WITHOUT_READ);

        $response = $this->requestDownload(['section_allowed_1'], self::USER_WITHOUT_READ);

        $this->assertNothingDownloaded($response, ['section_allowed_1']);
    }

    public function testUserWithReadWithoutEditReceivesSingleImage(): void
    {
        $this->assertContentReadable('outside_subtree_image', self::USER_READER);

        $response = $this->requestDownload(['outside_subtree_image'], self::USER_READER);

        $this->assertSingleImageResponse($response, 'outside_subtree_image');
    }

    public function testAnonymousUserReceivesPublicImage(): void
    {
        $this->assertContentReadable('public_1', self::ANONYMOUS);

        $response = $this->requestDownload(['public_1'], self::ANONYMOUS);

        $this->assertSingleImageResponse($response, 'public_1');
    }

    public function testUserWithReadReceivesArchiveOfVisibleImages(): void
    {
        $response = $this->requestDownload(['public_1', 'section_allowed_1', 'subtree_allowed_1'], self::USER_READER);

        $this->assertArchiveResponse($response, ['public_1', 'section_allowed_1', 'subtree_allowed_1']);
    }

    /**
     * @dataProvider provideScopedUsers
     *
     * @param array<string> $visibleImageKeys
     */
    public function testMixedListWithOneVisibleImageReturnsSingleImage(
        ?string $login,
        string $hiddenImageKey,
        array $visibleImageKeys
    ): void {
        $visibleImageKey = $visibleImageKeys[0];
        $this->assertContentReadable($visibleImageKey, $login);
        $this->assertContentNotReadable($hiddenImageKey, $login);

        $response = $this->requestDownload([$hiddenImageKey, $visibleImageKey], $login);

        $this->assertSingleImageResponse($response, $visibleImageKey);
    }

    /**
     * @dataProvider provideScopedUsers
     *
     * @param array<string> $visibleImageKeys
     */
    public function testMixedListReturnsArchiveContainingOnlyVisibleImages(
        ?string $login,
        string $hiddenImageKey,
        array $visibleImageKeys
    ): void {
        $this->assertContentNotReadable($hiddenImageKey, $login);

        $response = $this->requestDownload(array_merge([$hiddenImageKey], $visibleImageKeys), $login);

        $this->assertArchiveResponse($response, $visibleImageKeys);
    }

    /**
     * @dataProvider provideScopedUsers
     */
    public function testListWithoutVisibleImagesReturnsNotFound(
        ?string $login,
        string $hiddenImageKey
    ): void {
        $this->assertContentNotReadable($hiddenImageKey, $login);

        $response = $this->requestDownload([$hiddenImageKey], $login);

        $this->assertNothingDownloaded($response, [$hiddenImageKey]);
    }

    public function testUserWithReadLimitedToOtherContentTypeDoesNotReceiveImage(): void
    {
        $this->assertContentNotReadable('public_1', self::USER_FOLDER_ONLY_READER);

        $response = $this->requestDownload(['public_1'], self::USER_FOLDER_ONLY_READER);

        $this->assertNothingDownloaded($response, ['public_1']);
    }

    public function testUnknownContentIdReturnsNotFound(): void
    {
        $response = $this->requestDownloadByContentIds([self::UNKNOWN_CONTENT_ID], self::USER_READER);

        $this->assertNothingDownloaded($response, []);
    }

    public function testUnknownContentIdIsIgnoredInMixedList(): void
    {
        $response = $this->requestDownloadByContentIds(
            [self::UNKNOWN_CONTENT_ID, $this->images['public_1']->id],
            self::USER_READER
        );

        $this->assertSingleImageResponse($response, 'public_1');
    }

    public function testContentOtherThanImageIsNeverDownloaded(): void
    {
        $this->assertContentIdReadable($this->allowedFolderContentId, self::USER_READER);

        $response = $this->requestDownloadByContentIds([$this->allowedFolderContentId], self::USER_READER);

        $this->assertNothingDownloaded($response, []);
    }

    public function testContentOtherThanImageIsIgnoredInMixedList(): void
    {
        $this->assertContentIdReadable($this->allowedFolderContentId, self::USER_READER);

        $response = $this->requestDownloadByContentIds(
            [$this->allowedFolderContentId, $this->images['public_1']->id],
            self::USER_READER
        );

        $this->assertSingleImageResponse($response, 'public_1');
    }

    public function testRequestWithIdsAtDownloadLimitIsServed(): void
    {
        $contentIds = array_merge(
            [$this->images['public_1']->id],
            $this->getUnknownContentIds(self::DOWNLOAD_LIMIT - 1)
        );

        $response = $this->requestDownloadByContentIds($contentIds, self::USER_READER);

        $this->assertSingleImageResponse($response, 'public_1');
    }

    public function testRequestWithIdsOverDownloadLimitIsRejected(): void
    {
        $contentIds = array_merge(
            [$this->images['public_1']->id],
            $this->getUnknownContentIds(self::DOWNLOAD_LIMIT)
        );

        $response = $this->requestDownloadByContentIds($contentIds, self::USER_READER);

        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $error = json_decode((string)$response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(
            sprintf('Total download limit in one request is %d.', self::DOWNLOAD_LIMIT),
            $error['ErrorMessage']['errorDescription']
        );
        self::assertFalse($response->headers->has('Content-Disposition'));
    }

    /**
     * @return iterable<string, array{?string, string, array<string>}>
     */
    public static function provideScopedUsers(): iterable
    {
        yield 'anonymous user' => [
            self::ANONYMOUS,
            'other_section_image',
            ['public_1', 'public_2'],
        ];

        yield 'section limitation' => [
            self::USER_SECTION_READER,
            'other_section_image',
            ['section_allowed_1', 'section_allowed_2'],
        ];

        yield 'subtree limitation' => [
            self::USER_SUBTREE_READER,
            'outside_subtree_image',
            ['subtree_allowed_1', 'subtree_allowed_2'],
        ];

        yield 'content type limitation with subtree role assignment' => [
            self::USER_CONTENT_TYPE_AND_SUBTREE_READER,
            'outside_subtree_image',
            ['subtree_allowed_1', 'subtree_allowed_2'],
        ];
    }

    private function createImages(): void
    {
        $this->allowedSectionId = $this->createSection('download_allowed')->id;
        $otherSectionId = $this->createSection('download_other')->id;

        // Public images: readable by anonymous through the Standard section
        $this->createImage('public_1', self::STANDARD_SECTION_ID, self::ROOT_LOCATION_ID);
        $this->createImage('public_2', self::STANDARD_SECTION_ID, self::ROOT_LOCATION_ID);

        // Section limitation scenario
        $this->createImage('section_allowed_1', $this->allowedSectionId, self::ROOT_LOCATION_ID);
        $this->createImage('section_allowed_2', $this->allowedSectionId, self::ROOT_LOCATION_ID);
        $this->createImage('other_section_image', $otherSectionId, self::ROOT_LOCATION_ID);

        // Subtree limitation scenario, all of it in the second section
        $allowedFolder = $this->createFolder('download_folder_allowed', $otherSectionId);
        $outsideSubtreeFolder = $this->createFolder('download_folder_outside_subtree', $otherSectionId);
        $this->allowedFolderPath = $allowedFolder->pathString;
        $this->allowedFolderContentId = $allowedFolder->contentId;
        $this->createImage('subtree_allowed_1', $otherSectionId, $allowedFolder->id);
        $this->createImage('subtree_allowed_2', $otherSectionId, $allowedFolder->id);
        $this->createImage('outside_subtree_image', $otherSectionId, $outsideSubtreeFolder->id);
    }

    private function createUsers(): void
    {
        $contentTypeService = $this->getIbexaTestCore()->getContentTypeService();
        $imageTypeId = $contentTypeService->loadContentTypeByIdentifier('image')->id;
        $folderTypeId = $contentTypeService->loadContentTypeByIdentifier('folder')->id;

        $this->createUser(self::USER_WITHOUT_READ, [
            'user/login' => [],
        ]);
        $this->createUser(self::USER_READER, [
            'user/login' => [],
            'content/read' => [],
        ]);
        $this->createUser(self::USER_SECTION_READER, [
            'user/login' => [],
            'content/read' => [new SectionLimitation(['limitationValues' => [$this->allowedSectionId]])],
        ]);
        $this->createUser(self::USER_SUBTREE_READER, [
            'user/login' => [],
            'content/read' => [new SubtreeLimitation(['limitationValues' => [$this->allowedFolderPath]])],
        ]);
        $this->createUser(
            self::USER_CONTENT_TYPE_AND_SUBTREE_READER,
            [
                'user/login' => [],
                'content/read' => [new ContentTypeLimitation(['limitationValues' => [$folderTypeId, $imageTypeId]])],
            ],
            new SubtreeLimitation(['limitationValues' => [$this->allowedFolderPath]])
        );
        $this->createUser(self::USER_FOLDER_ONLY_READER, [
            'user/login' => [],
            'content/read' => [new ContentTypeLimitation(['limitationValues' => [$folderTypeId]])],
        ]);
    }

    /**
     * @param array<string> $imageKeys
     */
    private function requestDownload(
        array $imageKeys,
        ?string $login
    ): Response {
        return $this->requestDownloadByContentIds(
            array_map(fn (string $key): int => $this->images[$key]->id, $imageKeys),
            $login
        );
    }

    /**
     * @param array<int> $contentIds
     */
    private function requestDownloadByContentIds(
        array $contentIds,
        ?string $login
    ): Response {
        return $this->requestAs(
            $login,
            sprintf(self::ENDPOINT_URL, implode(',', $contentIds)),
            'application/json'
        );
    }

    /**
     * @return array<int> ids that do not belong to any content item
     */
    private function getUnknownContentIds(int $count): array
    {
        return array_map(
            static fn (int $offset): int => self::UNKNOWN_CONTENT_ID + $offset,
            range(1, $count)
        );
    }

    /**
     * Performs a GET request as the given user, or as anonymous when $login is null.
     */
    private function requestAs(
        ?string $login,
        string $uri,
        string $accept
    ): Response {
        if (self::ANONYMOUS === $login) {
            // Fixtures are persisted, so a fresh kernel without a logged in user acts as anonymous
            self::ensureKernelShutdown();
            $this->client = self::createClient();
        } else {
            $this->loginAs($this->loadUser($login));
        }

        $this->client->request('GET', $uri, [], [], [
            'HTTP_ACCEPT' => $accept,
            'HTTP_X-SiteAccess' => self::ANONYMOUS === $login ? self::SITEACCESS_ANONYMOUS : self::SITEACCESS_ADMIN,
        ]);

        return $this->client->getResponse();
    }

    /**
     * Confirms through the standard content endpoint that the image is not visible to the user,
     * so a passing download test cannot be caused by leftover roles.
     */
    private function assertContentNotReadable(
        string $imageKey,
        ?string $login
    ): void {
        $this->assertContentIdNotReadable($this->images[$imageKey]->id, $login);
    }

    private function assertContentReadable(
        string $imageKey,
        ?string $login
    ): void {
        $this->assertContentIdReadable($this->images[$imageKey]->id, $login);
    }

    private function assertContentIdNotReadable(
        int $contentId,
        ?string $login
    ): void {
        $response = $this->requestContent($contentId, $login);

        self::assertContains(
            $response->getStatusCode(),
            [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN],
            sprintf('Content %d should not be readable for %s', $contentId, $login ?? 'anonymous')
        );
    }

    private function assertContentIdReadable(
        int $contentId,
        ?string $login
    ): void {
        $response = $this->requestContent($contentId, $login);

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
            sprintf('Content %d should be readable for %s', $contentId, $login ?? 'anonymous')
        );
    }

    private function requestContent(
        int $contentId,
        ?string $login
    ): Response {
        return $this->requestAs(
            $login,
            sprintf(self::CONTENT_URL, $contentId),
            'application/vnd.ibexa.api.Content+json'
        );
    }

    /**
     * @param array<string> $imageKeys
     */
    private function assertNothingDownloaded(
        Response $response,
        array $imageKeys
    ): void {
        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        foreach ($imageKeys as $imageKey) {
            self::assertStringNotContainsString($this->files[$imageKey], (string)$response->getContent());
        }
        self::assertFalse($response->headers->has('Content-Disposition'));
    }

    private function assertSingleImageResponse(
        Response $response,
        string $imageKey
    ): void {
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('image/png', $response->headers->get('Content-Type'));
        self::assertStringContainsString(
            $this->fileNames[$imageKey],
            (string)$response->headers->get('Content-Disposition')
        );
        self::assertSame($this->files[$imageKey], $response->getContent());
    }

    /**
     * @param array<string> $imageKeys
     */
    private function assertArchiveResponse(
        Response $response,
        array $imageKeys
    ): void {
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('application/zip', $response->headers->get('Content-Type'));

        $expected = [];
        foreach ($imageKeys as $imageKey) {
            $expected[$this->fileNames[$imageKey]] = $this->files[$imageKey];
        }
        $actual = $this->extractArchive((string)$response->getContent());
        ksort($expected);
        ksort($actual);

        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, string> file name => file content
     */
    private function extractArchive(string $archiveContent): array
    {
        $archivePath = tempnam(sys_get_temp_dir(), 'ibexa-download-test');
        file_put_contents($archivePath, $archiveContent);

        try {
            $archive = new ZipArchive();
            self::assertTrue($archive->open($archivePath));

            $files = [];
            for ($index = 0; $index < $archive->numFiles; ++$index) {
                $name = $archive->getNameIndex($index);
                $content = $archive->getFromIndex($index);
                self::assertIsString($name);
                self::assertIsString($content);
                $files[$name] = $content;
            }
            $archive->close();
        } finally {
            unlink($archivePath);
        }

        return $files;
    }

    private function createSection(string $identifier): Section
    {
        $sectionService = $this->getIbexaTestCore()->getSectionService();
        $struct = $sectionService->newSectionCreateStruct();
        $struct->identifier = $identifier;
        $struct->name = $identifier;

        return $sectionService->createSection($struct);
    }

    private function createFolder(
        string $name,
        int $sectionId
    ): Location {
        $core = $this->getIbexaTestCore();
        $contentService = $core->getContentService();

        $struct = $contentService->newContentCreateStruct(
            $core->getContentTypeService()->loadContentTypeByIdentifier('folder'),
            'eng-GB'
        );
        $struct->sectionId = $sectionId;
        $struct->setField('name', $name);

        $content = $contentService->publishVersion(
            $contentService->createContent(
                $struct,
                [$core->getLocationService()->newLocationCreateStruct(self::ROOT_LOCATION_ID)]
            )->versionInfo
        );

        return $core->getLocationService()->loadLocation((int)$content->contentInfo->mainLocationId);
    }

    /**
     * Creates a published image with a real file in the location the controller reads from (relative to cwd).
     */
    private function createImage(
        string $key,
        int $sectionId,
        int $parentLocationId
    ): void {
        $core = $this->getIbexaTestCore();
        $contentService = $core->getContentService();

        // A valid PNG followed by a unique marker, so the stored bytes are distinguishable per image
        $binary = base64_decode(self::PNG_1X1, true) . 'MARKER-' . $key . '-' . bin2hex(random_bytes(8));
        $sourcePath = tempnam(sys_get_temp_dir(), 'ibexa-download-source');
        file_put_contents($sourcePath, $binary);

        $struct = $contentService->newContentCreateStruct(
            $core->getContentTypeService()->loadContentTypeByIdentifier('image'),
            'eng-GB'
        );
        $struct->sectionId = $sectionId;
        $struct->setField('name', $key);
        $struct->setField('image', [
            'inputUri' => $sourcePath,
            'fileName' => $key . '.png',
            'fileSize' => strlen($binary),
            'alternativeText' => $key,
        ]);

        $content = $contentService->publishVersion(
            $contentService->createContent(
                $struct,
                [$core->getLocationService()->newLocationCreateStruct($parentLocationId)]
            )->versionInfo
        );
        unlink($sourcePath);

        $imageValue = $content->getFieldValue('image');
        self::assertInstanceOf(ImageValue::class, $imageValue);

        $targetPath = getcwd() . '/' . ltrim((string)$imageValue->uri, '/');
        if (!is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0777, true);
        }
        file_put_contents($targetPath, $binary);
        $this->createdPaths[] = $targetPath;

        $this->images[$key] = $content;
        $this->files[$key] = $binary;
        $this->fileNames[$key] = (string)$imageValue->fileName;
    }

    private function loadUser(string $login): User
    {
        $core = $this->getIbexaTestCore();
        $core->setAdministratorUser();

        return $core->getUserService()->loadUserByLogin($login);
    }

    /**
     * @param array<string, array<Limitation>> $policies
     */
    private function createUser(
        string $login,
        array $policies,
        ?RoleLimitation $assignmentLimitation = null
    ): void {
        $core = $this->getIbexaTestCore();
        $userService = $core->getUserService();
        $roleService = $core->getRoleService();

        $userCreateStruct = $userService->newUserCreateStruct($login, "$login@test.local", $login, 'eng-GB');
        $userCreateStruct->setField('first_name', $login);
        $userCreateStruct->setField('last_name', $login);
        $user = $userService->createUser($userCreateStruct, [$userService->loadUserGroup(self::USERS_GROUP_ID)]);

        $roleCreateStruct = $roleService->newRoleCreateStruct('role_for_' . $login);
        foreach ($policies as $moduleFunction => $limitations) {
            [$module, $function] = explode('/', $moduleFunction);
            $policyCreateStruct = $roleService->newPolicyCreateStruct($module, $function);
            foreach ($limitations as $limitation) {
                $policyCreateStruct->addLimitation($limitation);
            }
            $roleCreateStruct->addPolicy($policyCreateStruct);
        }

        $roleDraft = $roleService->createRole($roleCreateStruct);
        $roleService->publishRoleDraft($roleDraft);
        $roleService->assignRoleToUser($roleService->loadRole($roleDraft->id), $user, $assignmentLimitation);
    }

    private function loginAs(User $user): void
    {
        $symfonyUser = $this->createStub(UserInterface::class);
        $symfonyUser->method('getRoles')->willReturn(['ROLE_USER']);
        $symfonyUser->method('getUserIdentifier')->willReturn($user->login);

        $this->client->loginUser(new UserWrapped($symfonyUser, $user));
    }
}
