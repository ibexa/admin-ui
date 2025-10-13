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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Range;

final readonly class BinaryFileFormMapper implements FieldDefinitionFormMapperInterface
{
    public function __construct(private MaxUploadSize $maxUploadSize)
    {
    }

    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data): void
    {
        $isTranslation = $data->contentTypeData->languageCode !== $data->contentTypeData->mainLanguageCode;
        $fieldDefinitionForm
            ->add('maxSize', IntegerType::class, [
                'required' => false,
                'property_path' => 'validatorConfiguration[FileSizeValidator][maxFileSize]',
                'label' => /** @Desc("Maximum file size (MB)") */ 'field_definition.ibexa_binaryfile.max_file_size',
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
