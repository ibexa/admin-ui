<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\URLAlias;
use Psr\Log\LoggerInterface;

class CustomUrlsDataset
{
    private URLAliasService $urlAliasService;

    private ValueFactory $valueFactory;

    /** @var \Ibexa\AdminUi\UI\Value\Content\UrlAlias[] */
    private ?array $data = null;

    private LoggerInterface $logger;

    public function __construct(
        URLAliasService $urlAliasService,
        ValueFactory $valueFactory,
        LoggerInterface $logger
    ) {
        $this->urlAliasService = $urlAliasService;
        $this->valueFactory = $valueFactory;
        $this->logger = $logger;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     *
     * @return \Ibexa\AdminUi\UI\Dataset\CustomUrlsDataset
     */
    public function load(Location $location): self
    {
        try {
            $customUrlAliases = $this->urlAliasService->listLocationAliases(
                $location,
                true,
                null,
                true
            );
        } catch (BadStateException $e) {
            $this->logger->warning(
                sprintf(
                    'At least one custom alias belonging to location %d is broken. Fix it by using the ibexa:urls:regenerate-aliases command.',
                    $location->id
                ),
                ['exception' => $e]
            );
            $customUrlAliases = [];
        }

        $this->data = array_map(
            function (URLAlias $urlAlias) {
                return $this->valueFactory->createUrlAlias($urlAlias);
            },
            $customUrlAliases
        );

        return $this;
    }

    /**
     * @return \Ibexa\AdminUi\UI\Value\Content\UrlAlias[]
     */
    public function getCustomUrlAliases(): array
    {
        return $this->data;
    }
}
