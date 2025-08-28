<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;
use Symfony\Component\Validator\Constraints as Assert;

class ContentTranslationData extends ContentUpdateStruct implements NewnessCheckable
{
    /** @var \Ibexa\Contracts\ContentForms\Data\Content\FieldData[] */
    #[Assert\Valid]
    protected array $fieldsData = [];

    protected Content $content;

    protected ContentType $contentType;

    public function addFieldData(FieldData $fieldData): void
    {
        $this->fieldsData[$fieldData->getFieldDefinition()->getIdentifier()] = $fieldData;
    }

    public function isNew(): bool
    {
        return false;
    }
}
