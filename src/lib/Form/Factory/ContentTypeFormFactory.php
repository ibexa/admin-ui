<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Factory;

use Ibexa\AdminUi\Form\Data\ContentType\ContentTypeCopyData;
use Ibexa\AdminUi\Form\Data\ContentType\ContentTypeEditData;
use Ibexa\AdminUi\Form\Data\ContentType\Translation\TranslationAddData;
use Ibexa\AdminUi\Form\Data\ContentType\Translation\TranslationRemoveData;
use Ibexa\AdminUi\Form\Type\ContentType\ContentTypeCopyType;
use Ibexa\AdminUi\Form\Type\ContentType\ContentTypeEditType;
use Ibexa\AdminUi\Form\Type\ContentType\Translation\TranslationAddType;
use Ibexa\AdminUi\Form\Type\ContentType\Translation\TranslationRemoveType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Util\StringUtil;

final readonly class ContentTypeFormFactory
{
    public function __construct(
        private FormFactoryInterface $formFactory
    ) {
    }

    public function addContentTypeTranslation(
        ?TranslationAddData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: 'add-translation';

        return $this->formFactory->createNamed($name, TranslationAddType::class, $data);
    }

    public function removeContentTypeTranslation(
        ?TranslationRemoveData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: 'delete-translations';

        return $this->formFactory->createNamed($name, TranslationRemoveType::class, $data);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function contentTypeEdit(
        ?ContentTypeEditData $data = null,
        ?string $name = null,
        array $options = []
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentTypeEditType::class);

        return $this->formFactory->createNamed($name, ContentTypeEditType::class, $data, $options);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function contentTypeCopy(
        ContentTypeCopyData $data,
        ?string $name = null,
        array $options = []
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentTypeCopyType::class);

        return $this->formFactory->createNamed($name, ContentTypeCopyType::class, $data, $options);
    }
}
