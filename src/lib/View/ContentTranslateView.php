<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\View;

use Ibexa\ContentForms\Content\View\ContentTypeValueView;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\MVC\Symfony\View\BaseView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ContentTranslateView extends BaseView implements ContentTypeValueView
{
    private Content $content;

    private ContentType $contentType;

    private ?Location $location = null;

    private Language $language;

    private ?Language $baseLanguage;

    /** @var \Symfony\Component\Form\FormInterface<mixed> */
    private FormInterface $form;

    private FormView $formView;

    public function setContent(Content $content): void
    {
        $this->content = $content;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): void
    {
        $this->language = $language;
    }

    public function getBaseLanguage(): ?Language
    {
        return $this->baseLanguage;
    }

    public function setBaseLanguage(?Language $baseLanguage): void
    {
        $this->baseLanguage = $baseLanguage;
    }

    /**
     * @return \Symfony\Component\Form\FormInterface<mixed>
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $form
     */
    public function setForm(FormInterface $form): void
    {
        $this->form = $form;
    }

    public function getFormView(): FormView
    {
        return $this->formView;
    }

    public function setFormView(FormView $formView): void
    {
        $this->formView = $formView;
    }

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    public function setContentType(ContentType $contentType): void
    {
        $this->contentType = $contentType;
    }
}
