<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\FormMapper;

use Ibexa\ContentForms\Data\Content\ContentUpdateData;
use Ibexa\Contracts\AdminUi\Form\Data\FormMapper\FormDataMapperInterface;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Symfony\Component\OptionsResolver\OptionsResolver;

final readonly class ContentUpdateMapper implements FormDataMapperInterface
{
    /**
     * Maps a ValueObject from Ibexa content repository to a data usable as underlying form data (e.g. create/update struct).
     *
     * @param array<string, mixed> $params
     */
    public function mapToFormData(ValueObject|Content $repositoryValueObject, array $params = []): ContentUpdateData
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);

        $params = $optionsResolver->resolve($params);
        $languageCode = $params['languageCode'];

        $data = new ContentUpdateData(['contentDraft' => $repositoryValueObject]);
        $data->initialLanguageCode = $languageCode;

        $fields = $repositoryValueObject->getFieldsByLanguage($languageCode);
        foreach ($params['contentType']->getFieldDefinitions() as $fieldDef) {
            $field = $fields[$fieldDef->getIdentifier()];
            $data->addFieldData(new FieldData([
                'fieldDefinition' => $fieldDef,
                'field' => $field,
                'value' => $field->getValue(),
            ]));
        }

        return $data;
    }

    private function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver
            ->setRequired(['languageCode', 'contentType'])
            ->setAllowedTypes('contentType', ContentType::class);
    }
}
