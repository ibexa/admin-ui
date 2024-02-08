<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

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

class ImageFormMapper implements FieldDefinitionFormMapperInterface
{
    /** @var array<string> */
    private array $allowedMimeTypes;

    /** @var \Ibexa\ContentForms\ConfigResolver\MaxUploadSize */
    private $maxUploadSize;

    private MimeTypesInterface $mimeTypes;

    /**
     * @param string[] $allowedMimeTypes
     */
    public function __construct(
        array $allowedMimeTypes,
        MaxUploadSize $maxUploadSize,
        MimeTypesInterface $mimeTypes
    ) {
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->maxUploadSize = $maxUploadSize;
        $this->mimeTypes = $mimeTypes;
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
                    'label' => /** @Desc("Image types") */ 'field_definition.ezimage.image_types',
                ]);
        }

        $fieldDefinitionForm
            ->add('maxSize', NumberType::class, [
                'required' => false,
                'property_path' => 'validatorConfiguration[FileSizeValidator][maxFileSize]',
                'label' => /** @Desc("Maximum file size (MB)") */ 'field_definition.ezimage.max_file_size',
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
                'label' => /** @Desc("Alternative text is required") */ 'field_definition.ezimage.is_alternative_text_required',
                'disabled' => $isTranslation,
            ]);
    }

    /**
     * Fake method to set the translation domain for the extractor.
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
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

class_alias(ImageFormMapper::class, 'EzSystems\EzPlatformAdminUi\FieldType\Mapper\ImageFormMapper');
