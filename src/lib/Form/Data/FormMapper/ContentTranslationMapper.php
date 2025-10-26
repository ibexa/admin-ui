<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\FormMapper;

use Ibexa\AdminUi\Form\Data\ContentTranslationData;
use Ibexa\Contracts\AdminUi\Form\Data\FormMapper\FormDataMapperInterface;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoSuchOptionException;
use Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTranslationMapper implements FormDataMapperInterface
{
    /**
     * Maps a ValueObject from Ibexa content repository to a data usable as underlying form data (e.g. create/update
     * struct).
     *
     * @param Content|ValueObject $content
     * @param array $params
     *
     * @return ContentTranslationData
     *
     * @throws UndefinedOptionsException
     * @throws OptionDefinitionException
     * @throws NoSuchOptionException
     * @throws MissingOptionsException
     * @throws InvalidOptionsException
     * @throws AccessException
     */
    public function mapToFormData(
        ValueObject $content,
        array $params = []
    ) {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $params = $optionsResolver->resolve($params);

        /** @var Language $language */
        $language = $params['language'];

        /** @var Language|null $baseLanguage */
        $baseLanguage = $params['baseLanguage'];
        $baseLanguageCode = $baseLanguage ? $baseLanguage->languageCode : null;

        /** @var ContentType $contentType */
        $contentType = $params['contentType'];

        $data = new ContentTranslationData(['content' => $content, 'contentType' => $contentType]);
        $data->initialLanguageCode = $language->languageCode;

        foreach ($content->getFieldsByLanguage() as $field) {
            $fieldDef = $contentType->getFieldDefinition($field->fieldDefIdentifier);
            $fieldValue = null !== $baseLanguageCode
                ? $content->getFieldValue($fieldDef->identifier, $baseLanguageCode)
                : $fieldDef->defaultValue;
            $data->addFieldData(new FieldData([
                'fieldDefinition' => $fieldDef,
                'field' => $field,
                'value' => $fieldDef->isTranslatable
                    ? $fieldValue
                    : $field->value,
            ]));
        }

        return $data;
    }

    /**
     * @param OptionsResolver $optionsResolver
     *
     * @throws UndefinedOptionsException
     * @throws AccessException
     */
    private function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver
            ->setRequired([
                'language',
                'contentType',
            ])
            ->setDefined(['baseLanguage'])
            ->setAllowedTypes('contentType', ContentType::class)
            ->setAllowedTypes('baseLanguage', ['null', Language::class])
            ->setAllowedTypes('language', Language::class);
    }
}

class_alias(ContentTranslationMapper::class, 'EzSystems\EzPlatformAdminUi\Form\Data\FormMapper\ContentTranslationMapper');
