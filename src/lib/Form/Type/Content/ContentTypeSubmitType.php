<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Content;

use Ibexa\AdminUi\Form\DataTransformer\ContentTypeTransformer;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends \Symfony\Component\Form\AbstractType<mixed>
 */
class ContentTypeSubmitType extends AbstractType
{
    public function __construct(protected readonly ContentTypeService $contentTypeService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(
            new ContentTypeTransformer($this->contentTypeService)
        );
    }

    public function getParent(): string
    {
        return SubmitType::class;
    }
}
