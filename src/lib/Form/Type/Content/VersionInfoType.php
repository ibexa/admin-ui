<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Content;

use Ibexa\AdminUi\Form\DataTransformer\VersionInfoTransformer;
use Ibexa\Contracts\Core\Repository\ContentService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class VersionInfoType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    protected $contentService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     */
    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'content_info',
                ContentInfoType::class,
                ['label' => false]
            )
            ->add(
                'version_no',
                HiddenType::class,
                ['label' => false]
            )
            ->addViewTransformer(new VersionInfoTransformer($this->contentService));
    }
}

class_alias(VersionInfoType::class, 'EzSystems\EzPlatformAdminUi\Form\Type\Content\VersionInfoType');
