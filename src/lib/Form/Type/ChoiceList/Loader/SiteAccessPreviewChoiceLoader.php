<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ChoiceList\Loader;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SiteAccessPreviewChoiceLoader extends BaseChoiceLoader
{
    public function __construct(
        private readonly SiteAccessChoiceLoader $siteAccessChoiceLoader,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly int $contentId,
        private readonly string $languageCode,
        private readonly int $versionNo
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getChoiceList(): array
    {
        $baseChoiceList = $this->siteAccessChoiceLoader->getChoiceList();

        return array_map(fn (string $siteAccessKey): string => $this->urlGenerator->generate(
            'ibexa.version.preview',
            [
                'contentId' => $this->contentId,
                'versionNo' => $this->versionNo,
                'language' => $this->languageCode,
                'siteAccessName' => $siteAccessKey,
            ]
        ), $baseChoiceList);
    }
}
