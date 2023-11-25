<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ContentType;

use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\ContentTypeChoiceLoader;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type allowing to select ContentType.
 */
class ContentTypeChoiceType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    protected $contentTypeService;

    /** @var \Ibexa\AdminUi\Form\Type\ChoiceList\Loader\ContentTypeChoiceLoader */
    private $contentTypeChoiceLoader;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Ibexa\AdminUi\Form\Type\ChoiceList\Loader\ContentTypeChoiceLoader $contentTypeChoiceLoader
     */
    public function __construct(
        ContentTypeService $contentTypeService,
        ContentTypeChoiceLoader $contentTypeChoiceLoader
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->contentTypeChoiceLoader = $contentTypeChoiceLoader;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choice_loader' => $this->contentTypeChoiceLoader,
                'choice_label' => 'name',
                'choice_value' => 'identifier',
            ]);
    }
}

class_alias(ContentTypeChoiceType::class, 'EzSystems\EzPlatformAdminUi\Form\Type\ContentType\ContentTypeChoiceType');
