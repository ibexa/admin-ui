<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Output\ValueObjectVisitor\ContentTree;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Symfony\Component\HttpFoundation\Response;

final class TranslatedNamesListVisitor extends ValueObjectVisitor
{
    private const MAIN_ELEMENT = 'ContentTreeTranslatedNamesList';

    /**
     * @param \Ibexa\AdminUi\REST\Value\ContentTree\TranslatedNamesList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement(self::MAIN_ELEMENT);
        $visitor->setHeader('Content-Type', $generator->getMediaType(self::MAIN_ELEMENT));
        $visitor->setStatus(Response::HTTP_OK);

        $generator->startList('entries');
        foreach ($data->getVersionInfoList() as $versionInfo) {
            $generator->startHashElement('entry');
            $generator->valueElement('contentId', $versionInfo->getContentInfo()->getId());

            $generator->startHashElement('languages');
            foreach ($versionInfo->getNames() as $languageCode => $name) {
                $generator->valueElement($languageCode, $name);
            }
            $generator->endHashElement('languages');

            $generator->endHashElement('entry');
        }
        $generator->endList('entries');

        $generator->endObjectElement(self::MAIN_ELEMENT);
    }
}
