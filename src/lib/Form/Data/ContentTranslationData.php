<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;
use Symfony\Component\Validator\Constraints as Assert;

class ContentTranslationData extends ContentUpdateStruct implements NewnessCheckable
{
    /**
     * @var \Ibexa\Contracts\ContentForms\Data\Content\FieldData[]
     */
    #[Assert\Valid]
    protected $fieldsData;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content */
    protected $content;

    /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType */
    protected $contentType;

    public function addFieldData(FieldData $fieldData): void
    {
        $this->fieldsData[$fieldData->fieldDefinition->identifier] = $fieldData;
    }

    public function isNew(): bool
    {
        return false;
    }
}
