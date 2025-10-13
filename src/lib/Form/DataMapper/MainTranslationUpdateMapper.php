<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Content\Translation\MainTranslationUpdateData;
use Ibexa\Contracts\AdminUi\Form\DataMapper\DataMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentMetadataUpdateStruct;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

final readonly class MainTranslationUpdateMapper implements DataMapperInterface
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function map(ValueObject|ContentMetadataUpdateStruct $value): MainTranslationUpdateData
    {
        if (!$value instanceof ContentMetadataUpdateStruct) {
            throw new InvalidArgumentException(
                'value',
                sprintf('must be an instance of %s', ContentMetadataUpdateStruct::class)
            );
        }

        $data = new MainTranslationUpdateData();
        $data->setLanguageCode($value->mainLanguageCode);

        return $data;
    }

    /**
     * @param \Ibexa\AdminUi\Form\Data\Content\Translation\MainTranslationUpdateData $data
     */
    public function reverseMap(mixed $data): ContentMetadataUpdateStruct
    {
        return new ContentMetadataUpdateStruct([
            'mainLanguageCode' => $data->languageCode,
            'name' => $data->content?->getName($data->languageCode),
        ]);
    }
}
