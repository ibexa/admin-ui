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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ContentTypeType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    protected $contentTypeService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ContentTypeTransformer($this->contentTypeService));
    }

    public function getParent()
    {
        return TextType::class;
    }
}
