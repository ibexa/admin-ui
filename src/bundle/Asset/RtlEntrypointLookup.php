<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Asset;

use Ibexa\Contracts\AdminUi\Rtl\RtlModeResolverInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\Asset\IntegrityDataProviderInterface;

final readonly class RtlEntrypointLookup implements EntrypointLookupInterface, IntegrityDataProviderInterface
{
    public function __construct(
        private EntrypointLookupInterface $inner,
        private RtlModeResolverInterface $rtlModeResolver,
    ) {
    }

    /**
     * @return array<string>
     */
    public function getJavaScriptFiles(string $entryName): array
    {
        return $this->inner->getJavaScriptFiles($entryName);
    }

    /**
     * @return array<string>
     */
    public function getCssFiles(string $entryName): array
    {
        if (!$this->rtlModeResolver->isRtl()) {
            return $this->inner->getCssFiles($entryName);
        }

        if ($this->entryExists($entryName . '-rtl')) {
            $entryName .= '-rtl';
        }

        $files = $this->inner->getCssFiles($entryName);

        $overrideEntryName = $entryName . '-override';
        if ($this->entryExists($overrideEntryName)) {
            $files = array_merge($files, $this->inner->getCssFiles($overrideEntryName));
        }

        return $files;
    }

    private function entryExists(string $entryName): bool
    {
        if ($this->inner instanceof EntrypointLookup) {
            return $this->inner->entryExists($entryName);
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    public function getIntegrityData(): array
    {
        if ($this->inner instanceof IntegrityDataProviderInterface) {
            return $this->inner->getIntegrityData();
        }

        return [];
    }

    public function reset(): void
    {
        $this->inner->reset();
    }
}
