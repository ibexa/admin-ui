<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\FormMapper;

use Ibexa\ContentForms\Data\Content\ContentCreateData;
use Ibexa\Contracts\AdminUi\Form\Data\FormMapper\FormDataMapperInterface;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form data mapper for content create without a draft.
 */
class ContentCreateMapper implements FormDataMapperInterface
{
    /**
     * Maps a ValueObject from Ibexa content repository to a data usable as underlying form data (e.g. create/update struct).
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType|\Ibexa\Contracts\Core\Repository\Values\ValueObject $contentType
     * @param array $params
     *
     * @return \Ibexa\ContentForms\Data\Content\ContentCreateData
     */
    public function mapToFormData(ValueObject $contentType, array $params = []): ContentCreateData
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $params = $resolver->resolve($params);

        $data = new ContentCreateData(['contentType' => $contentType, 'mainLanguageCode' => $params['mainLanguageCode']]);
        $data->addLocationStruct($params['parentLocation']);
        foreach ($contentType->fieldDefinitions as $fieldDef) {
            $data->addFieldData(new FieldData([
                'fieldDefinition' => $fieldDef,
                'field' => new Field([
                    'fieldDefIdentifier' => $fieldDef->identifier,
                    'languageCode' => $params['mainLanguageCode'],
                ]),
                'value' => $fieldDef->defaultValue,
            ]));
        }

        return $data;
    }

    private function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver
            ->setRequired(['mainLanguageCode', 'parentLocation'])
            ->setAllowedTypes('parentLocation', '\\Ibexa\\Contracts\\Core\\Repository\\Values\\Content\\LocationCreateStruct');
    }
}
