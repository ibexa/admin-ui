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

/**
 * @extends \Symfony\Component\Form\AbstractType<mixed>
 */
class LanguageType extends AbstractType
{
    public function __construct(protected readonly LanguageService $languageService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new LanguageTransformer($this->languageService));
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
