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

/**
 * {@inheritdoc}
 */
class ContentTranslateView extends BaseView implements ContentTypeValueView
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content */
    private Content $content;

    /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType */
    private ContentType $contentType;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location|null */
    private ?Location $location = null;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language */
    private Language $language;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language|null */
    private $baseLanguage;

    /** @var \Symfony\Component\Form\FormInterface */
    private FormInterface $form;

    /** @var \Symfony\Component\Form\FormView */
    private FormView $formView;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     */
    public function setContent(Content $content): void
    {
        $this->content = $content;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Content
     */
    public function getContent(): Content
    {
        return $this->content;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     */
    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location|null
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language
     */
    public function getLanguage(): Language
    {
        return $this->language;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language $language
     */
    public function setLanguage(Language $language): void
    {
        $this->language = $language;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language|null
     */
    public function getBaseLanguage(): ?Language
    {
        return $this->baseLanguage;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language|null $baseLanguage
     */
    public function setBaseLanguage($baseLanguage): void
    {
        $this->baseLanguage = $baseLanguage;
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     */
    public function setForm(FormInterface $form): void
    {
        $this->form = $form;
    }

    /**
     * @return \Symfony\Component\Form\FormView
     */
    public function getFormView(): FormView
    {
        return $this->formView;
    }

    /**
     * @param \Symfony\Component\Form\FormView $formView
     */
    public function setFormView(FormView $formView): void
    {
        $this->formView = $formView;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType
     */
    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     */
    public function setContentType(ContentType $contentType): void
    {
        $this->contentType = $contentType;
    }
}
