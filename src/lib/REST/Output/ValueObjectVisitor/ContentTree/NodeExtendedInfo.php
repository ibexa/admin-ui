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
final class NodeExtendedInfo extends ValueObjectVisitor
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\ContentTree\NodeExtendedInfo $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement('ContentTreeNodeExtendedInfo');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentTreeNodeExtendedInfo'));
        $visitor->setStatus(Response::HTTP_OK);

        $this->buildPermissionNode($data->getPermissionRestrictions(), $generator);
        $this->buildPreviewableTranslationsNode($data->getPreviewableTranslations(), $generator);

        $generator->endObjectElement('ContentTreeNodeExtendedInfo');
    }

    /**
     * @param array<string>|null $previewableTranslations
     */
    protected function buildPreviewableTranslationsNode(
        ?array $previewableTranslations,
        Generator $generator
    ): void {
        if ($previewableTranslations === null) {
            return;
        }

        $generator->valueElement('previewableTranslations', implode(',', $previewableTranslations));
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

        $generator->startHashElement('permissions');

        foreach ($permissionRestrictions as $function => $restrictions) {
            $generator->startHashElement($function);
            foreach ($restrictions as $restrictionKey => $restrictionValue) {
                if (is_array($restrictionValue)) {
                    $generator->startList($restrictionKey);
                    foreach ($restrictionValue as $value) {
                        $generator->valueElement($restrictionKey, $value);
                    }
                    $generator->endList($restrictionKey);
                } else {
                    $generator->valueElement($restrictionKey, $restrictionValue);
                }
            }
            $generator->endHashElement($function);
        }

        $generator->endHashElement('permissions');
    }
}
