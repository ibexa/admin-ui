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

/**
 * @phpstan-import-type TPermissionRestrictions from \Ibexa\AdminUi\REST\Value\ContentTree\NodeExtendedInfo
 */
final class NodeExtendedInfoVisitor extends ValueObjectVisitor
{
    public const MAIN_ELEMENT = 'ContentTreeNodeExtendedInfo';

    /**
     * @param \Ibexa\AdminUi\REST\Value\ContentTree\NodeExtendedInfo $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement(self::MAIN_ELEMENT);
        $visitor->setHeader('Content-Type', $generator->getMediaType(self::MAIN_ELEMENT));
        $visitor->setStatus(Response::HTTP_OK);

        $this->buildPermissionNode($data->getPermissionRestrictions(), $generator);
        $this->buildPreviewableTranslationsNode($data->getPreviewableTranslations(), $generator);
        $this->buildTranslationsNode($data->getTranslations(), $generator);

        $generator->endObjectElement(self::MAIN_ELEMENT);
    }

    /**
     * @param string[] $previewableTranslations
     */
    protected function buildPreviewableTranslationsNode(
        array $previewableTranslations,
        Generator $generator
    ): void {
        $generator->startHashElement('previewableTranslations');
        $generator->startList('values');
        foreach ($previewableTranslations as $value) {
            $generator->valueElement('value', $value);
        }
        $generator->endList('values');
        $generator->endHashElement('previewableTranslations');
    }

    /**
     * @param array<int, string> $translations
     */
    protected function buildTranslationsNode(
        array $translations,
        Generator $generator
    ): void {
        $generator->startHashElement('translations');
        $generator->startList('values');
        foreach ($translations as $value) {
            $generator->valueElement('value', $value);
        }
        $generator->endList('values');
        $generator->endHashElement('translations');
    }

    /**
     * @phpstan-param TPermissionRestrictions $permissionRestrictions
     */
    protected function buildPermissionNode(
        ?array $permissionRestrictions,
        Generator $generator
    ): void {
        if (null === $permissionRestrictions) {
            return;
        }

        $generator->startList('permissions');

        foreach ($permissionRestrictions as $function => $restrictions) {
            $generator->startHashElement('function');
            $generator->attribute('name', $function);
            foreach ($restrictions as $restrictionKey => $restrictionValue) {
                if (is_array($restrictionValue)) {
                    $generator->startHashElement($restrictionKey . 'List');
                    $generator->startList($restrictionKey);
                    foreach ($restrictionValue as $value) {
                        $generator->valueElement('value', $value);
                    }
                    $generator->endList($restrictionKey);
                    $generator->endHashElement($restrictionKey . 'List');
                } elseif (is_bool($restrictionValue)) {
                    $generator->valueElement($restrictionKey, $generator->serializeBool($restrictionValue));
                }
            }
            $generator->endHashElement('function');
        }

        $generator->endList('permissions');
    }
}
