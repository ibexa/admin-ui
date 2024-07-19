<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use DateTimeImmutable;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Core\FieldType\Image\Value;
use Ibexa\Rest\Server\Controller;
use Ibexa\User\UserSetting\DateTimeFormat\FormatterInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

final class DownloadImageController extends Controller
{
    private const EXTENSION_ZIP = '.zip';

    private const ARCHIVE_NAME_PATTERN = 'images_%s' . self::EXTENSION_ZIP;

    private int $downloadLimit;

    private FormatterInterface $formatter;

    /** @var array<string, mixed> */
    private array $imageMappings;

    private SearchService $searchService;

    /**
     * @param array<string, mixed> $imageMappings
     */
    public function __construct(
        int $downloadLimit,
        FormatterInterface $formatter,
        array $imageMappings,
        SearchService $searchService
    ) {
        $this->downloadLimit = $downloadLimit;
        $this->formatter = $formatter;
        $this->imageMappings = $imageMappings;
        $this->searchService = $searchService;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidCriterionArgumentException
     * @throws \Exception
     */
    public function downloadAction(string $contentIdList): Response
    {
        $splitContentIdList = array_map(
            static fn (string $value): int => (int)$value,
            explode(',', $contentIdList)
        );

        $this->assertDownloadLimitNotExceeded($splitContentIdList);

        $images = $this->loadImages($splitContentIdList);
        if (0 === $images->totalCount) {
            return new Response(
                'No results found.',
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->processDownloading($images);
    }

    /**
     * @param array<int> $contentIdList
     */
    private function assertDownloadLimitNotExceeded(array $contentIdList): void
    {
        if (count($contentIdList) > $this->downloadLimit) {
            throw new RuntimeException(
                sprintf(
                    'Total download limit in one request is %d.',
                    $this->downloadLimit
                )
            );
        }
    }

    /**
     * @throws \Random\RandomException
     * @throws \Exception
     */
    private function processDownloading(SearchResult $result): Response
    {
        if (1 === $result->totalCount) {
            return $this->downloadSingleImage(
                $result->getIterator()->current()->valueObject
            );
        }

        if (!extension_loaded('zip')) {
            throw new RuntimeException(
                'ZIP extension is not loaded. Enable the extension or use single download instead.'
            );
        }

        $contentList = [];

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit $image */
        foreach ($result as $image) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
            $content = $image->valueObject;
            $contentList[] = $content;
        }

        return $this->downloadArchiveWithImages($contentList);
    }

    /**
     * @throws \Random\RandomException
     */
    private function downloadSingleImage(Content $content): Response
    {
        $value = $this->getImageValue($content);
        $uri = $this->getImageUri($value);

        $content = file_get_contents($uri);
        if (false === $content) {
            throw new RuntimeException(
                sprintf(
                    'Failed to read data from "%s"',
                    $uri
                )
            );
        }

        $response = $this->createResponse(
            $content,
            $this->getImageFileName($value)
        );

        $response->headers->set('Content-Type', $value->mime);

        return $response;
    }

    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\Content\Content> $contentList
     *
     * @throws \Random\RandomException
     */
    private function downloadArchiveWithImages(array $contentList): Response
    {
        $archiveName = sprintf(
            self::ARCHIVE_NAME_PATTERN,
            $this->generateRandomFileName()
        );

        $this->createArchive($archiveName, $contentList);

        $content = file_get_contents($archiveName);
        if (false === $content) {
            throw new RuntimeException('Failed to read archive with images.');
        }

        $fileName = $this->formatter->format(new DateTimeImmutable()) . self::EXTENSION_ZIP;
        $response = $this->createResponse($content, $fileName);
        $response->headers->set('Content-Type', 'application/zip');

        unlink($archiveName);

        return $response;
    }

    private function getImageValue(Content $content): Value
    {
        $imageFieldIdentifier = $this->getImageFieldIdentifier($content->getContentType()->identifier);
        $value = $content->getFieldValue($imageFieldIdentifier);

        if (null === $value) {
            throw new RuntimeException(
                sprintf(
                    'Missing field with identifier: "%s"',
                    $imageFieldIdentifier
                )
            );
        }

        if (!$value instanceof Value) {
            throw new RuntimeException(
                sprintf(
                    'Field value should be of type %s. "%s" given.',
                    Value::class,
                    get_debug_type($value)
                )
            );
        }

        return $value;
    }

    private function getImageFieldIdentifier(string $contentTypeIdentifier): string
    {
        $imageFieldIdentifier = $this->imageMappings[$contentTypeIdentifier]['imageFieldIdentifier'];
        if (null === $imageFieldIdentifier) {
            throw new RuntimeException(
                sprintf(
                    'Missing key imageFieldIdentifier for content type mapping "%s".',
                    $contentTypeIdentifier
                )
            );
        }

        return $imageFieldIdentifier;
    }

    private function getImageUri(Value $value): string
    {
        $uri = $value->uri;
        if (null === $uri) {
            throw new RuntimeException('Missing image uri');
        }

        return ltrim($uri, '/');
    }

    /**
     * @throws \Random\RandomException
     */
    private function getImageFileName(Value $value): string
    {
        return $value->fileName ?? 'image_' . $this->generateRandomFileName();
    }

    /**
     * @param array<int> $contentIdList
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidCriterionArgumentException;
     */
    private function loadImages(array $contentIdList): SearchResult
    {
        $query = new Query();
        $query->filter = new Query\Criterion\LogicalAnd(
            [
                new Query\Criterion\ContentId($contentIdList),
                new Query\Criterion\ContentTypeIdentifier($this->getContentTypeIdentifiers()),
            ]
        );
        $query->limit = $this->downloadLimit;

        return $this->searchService->findContent($query, [], false);
    }

    /**
     * @return array<string>
     */
    private function getContentTypeIdentifiers(): array
    {
        $contentTypeIdentifiers = [];
        foreach ($this->imageMappings as $contentTypeIdentifier => $mapping) {
            $contentTypeIdentifiers[] = $contentTypeIdentifier;
        }

        return $contentTypeIdentifiers;
    }

    private function createResponse(
        string $content,
        string $fileName
    ): Response {
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName
        );

        return new Response(
            $content,
            Response::HTTP_OK,
            [
                'Content-Disposition' => $disposition,
                'Content-Length' => strlen($content),
            ]
        );
    }

    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\Content\Content> $contentList
     *
     * @throws \Random\RandomException
     */
    private function createArchive(string $name, array $contentList): void
    {
        $zipArchive = new ZipArchive();
        $zipArchive->open($name, ZipArchive::CREATE);

        foreach ($contentList as $content) {
            $value = $this->getImageValue($content);
            $zipArchive->addFile(
                $this->getImageUri($value),
                $this->getImageFileName($value)
            );
        }

        $zipArchive->close();
    }

    /**
     * @throws \Random\RandomException
     */
    private function generateRandomFileName(): string
    {
        return bin2hex(random_bytes(12));
    }
}
