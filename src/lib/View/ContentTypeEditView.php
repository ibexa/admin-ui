<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\View;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\Core\MVC\Symfony\View\BaseView;
use Symfony\Component\Form\FormInterface;

final class ContentTypeEditView extends BaseView
{
    private ContentTypeGroup $contentTypeGroup;

    private ContentTypeDraft $contentTypeDraft;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language */
    private ?Language $language = null;

    private FormInterface $form;

    /**
     * @param string|\Closure $templateIdentifier Valid path to the template. Can also be a closure.
     */
    public function __construct(
        $template,
        ContentTypeGroup $contentTypeGroup,
        ContentTypeDraft $contentTypeDraft,
        Language $language,
        FormInterface $form
    ) {
        parent::__construct($template);

        $this->contentTypeGroup = $contentTypeGroup;
        $this->contentTypeDraft = $contentTypeDraft;
        $this->language = $language;
        $this->form = $form;
    }

    public function getContentTypeGroup(): ContentTypeGroup
    {
        return $this->contentTypeGroup;
    }

    public function setContentTypeGroup(ContentTypeGroup $contentTypeGroup): void
    {
        $this->contentTypeGroup = $contentTypeGroup;
    }

    public function getContentTypeDraft(): ContentTypeDraft
    {
        return $this->contentTypeDraft;
    }

    public function setContentTypeDraft(ContentTypeDraft $contentTypeDraft): void
    {
        $this->contentTypeDraft = $contentTypeDraft;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): void
    {
        $this->language = $language;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function setForm(FormInterface $form): void
    {
        $this->form = $form;
    }

    /**
     * @return array<string,mixed>
     */
    protected function getInternalParameters(): array
    {
        return [
            'content_type_group' => $this->contentTypeGroup,
            'content_type' => $this->contentTypeDraft,
            'form' => $this->form ? $this->form->createView() : null,
            'language_code' => $this->language !== null ? $this->language->languageCode : null,
        ];
    }
}
