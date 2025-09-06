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

final class CustomUrlsDataset
{
    /** @var \Ibexa\AdminUi\UI\Value\Content\UrlAlias[] */
    private ?array $data = null;

    public function __construct(
        private readonly URLAliasService $urlAliasService,
        private readonly ValueFactory $valueFactory,
        private readonly LoggerInterface $logger
    ) {
    }

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
                    $location->getId()
                ),
                ['exception' => $e]
            );
            $customUrlAliases = [];
        }

        $this->data = array_map(
            function (URLAlias $urlAlias) {
                return $this->valueFactory->createUrlAlias($urlAlias);
            },
            iterator_to_array($customUrlAliases)
        );

        return $this;
    }

    /**
     * @return \Ibexa\AdminUi\UI\Value\Content\UrlAlias[]
     */
    public function getCustomUrlAliases(): array
    {
        return $this->data ?? [];
    }
}
