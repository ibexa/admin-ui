<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Language;

use Ibexa\AdminUi\Form\DataTransformer\LanguageTransformer;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class LanguageType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    protected $languageService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LanguageService $languageService
     */
    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new LanguageTransformer($this->languageService));
    }

    public function getParent(): ?string
    {
        return HiddenType::class;
    }
}
