<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\FieldType\Mapper;

use Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\ContentForms\ConfigResolver\MaxUploadSize;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Range;

class BinaryFileFormMapper implements FieldDefinitionFormMapperInterface
{
    /** @var \Ibexa\ContentForms\ConfigResolver\MaxUploadSize */
    private $maxUploadSize;

    public function __construct(FieldTypeService $fieldTypeService, MaxUploadSize $maxUploadSize)
    {
        $this->maxUploadSize = $maxUploadSize;
    }

    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data): void
    {
        $isTranslation = $data->contentTypeData->languageCode !== $data->contentTypeData->mainLanguageCode;
        $fieldDefinitionForm
            ->add('maxSize', IntegerType::class, [
                'required' => false,
                'property_path' => 'validatorConfiguration[FileSizeValidator][maxFileSize]',
                'label' => /** @Desc("Maximum file size (MB)") */ 'field_definition.ezbinaryfile.max_file_size',
                'translation_domain' => 'ibexa_content_type',
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
            ]);
    }
}

class_alias(BinaryFileFormMapper::class, 'EzSystems\EzPlatformAdminUi\FieldType\Mapper\BinaryFileFormMapper');
