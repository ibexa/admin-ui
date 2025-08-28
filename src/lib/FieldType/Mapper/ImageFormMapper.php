<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\FieldType\Mapper;

use Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\ContentForms\ConfigResolver\MaxUploadSize;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

final readonly class ImageFormMapper implements FieldDefinitionFormMapperInterface
{
    /**
     * @param string[] $allowedMimeTypes
     */
    public function __construct(
        private array $allowedMimeTypes,
        private MaxUploadSize $maxUploadSize,
        private MimeTypesInterface $mimeTypes
    ) {
    }

    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data): void
    {
        $isTranslation = $data->contentTypeData->languageCode !== $data->contentTypeData->mainLanguageCode;

        if (!empty($this->allowedMimeTypes)) {
            $fieldDefinitionForm
                ->add('mimeTypes', ChoiceType::class, [
                    'required' => false,
                    'multiple' => true,
                    'choices' => $this->getMimeTypesChoiceList(),
                    'property_path' => 'fieldSettings[mimeTypes]',
                    'label' => /** @Desc("Image types") */ 'field_definition.ibexa_image.image_types',
                ]);
        }

        $fieldDefinitionForm
            ->add('maxSize', NumberType::class, [
                'required' => false,
                'property_path' => 'validatorConfiguration[FileSizeValidator][maxFileSize]',
                'label' => /** @Desc("Maximum file size (MB)") */ 'field_definition.ibexa_image.max_file_size',
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => $this->maxUploadSize->get(MaxUploadSize::MEGABYTES),
                    ]),
                ],
                'attr' => [
                    'min' => 0,
                    'max' => $this->maxUploadSize->get(MaxUploadSize::MEGABYTES),
                ],
                'disabled' => $isTranslation,
                'scale' => 1,
            ])
            ->add('isAlternativeTextRequired', CheckboxType::class, [
                'required' => false,
                'property_path' => 'validatorConfiguration[AlternativeTextValidator][required]',
                'label' => /** @Desc("Alternative text is required") */ 'field_definition.ibexa_image.is_alternative_text_required',
                'disabled' => $isTranslation,
            ]);
    }

    /**
     * Fake method to set the translation domain for the extractor.
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'ibexa_content_type',
            ]);
    }

    /**
     * @return array<string>
     */
    private function getMimeTypesChoiceList(): array
    {
        $mimeTypeChoiceList = [];
        foreach ($this->allowedMimeTypes as $mimeType) {
            $extensions = implode(
                ', ',
                array_map(
                    static function (string $extension): string {
                        return '*.' . $extension;
                    },
                    $this->mimeTypes->getExtensions($mimeType)
                )
            );
            $label = explode('image/', $mimeType);
            $mimeTypeChoiceList[$label[1] . " ($extensions)"] = $mimeType;
        }

        return $mimeTypeChoiceList;
    }
}
