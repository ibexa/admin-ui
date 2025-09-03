<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Content;

use Ibexa\AdminUi\Form\DataTransformer\ContentTransformer;
use Ibexa\Contracts\Core\Repository\ContentService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends \Symfony\Component\Form\AbstractType<mixed>
 */
class ContentType extends AbstractType
{
    public function __construct(protected readonly ContentService $contentService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(new ContentTransformer($this->contentService));
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
