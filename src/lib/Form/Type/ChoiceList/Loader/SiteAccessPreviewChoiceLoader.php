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
    private SiteAccessChoiceLoader $siteAccessChoiceLoader;

    private UrlGeneratorInterface $urlGenerator;

    private int $contentId;

    private string $languageCode;

    private int $versionNo;

    public function __construct(
        SiteAccessChoiceLoader $siteAccessChoiceLoader,
        UrlGeneratorInterface $urlGenerator,
        int $contentId,
        string $languageCode,
        int $versionNo
    ) {
        $this->siteAccessChoiceLoader = $siteAccessChoiceLoader;
        $this->urlGenerator = $urlGenerator;
        $this->contentId = $contentId;
        $this->languageCode = $languageCode;
        $this->versionNo = $versionNo;
    }

    /**
     * @return array<string, string>
     */
    public function getChoiceList(): array
    {
        $baseChoiceList = $this->siteAccessChoiceLoader->getChoiceList();

        $choiceList = [];
        foreach ($baseChoiceList as $siteAccessKey => $siteAccessName) {
            $choiceList[$siteAccessKey] = $this->urlGenerator->generate(
                'ibexa.version.preview',
                [
                    'contentId' => $this->contentId,
                    'versionNo' => $this->versionNo,
                    'language' => $this->languageCode,
                    'siteAccessName' => $siteAccessKey,
                ]
            );
        }

        return $choiceList;
    }
}
