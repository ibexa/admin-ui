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
    protected ContentTypeService $contentTypeService;

    private ContentTypeChoiceLoader $contentTypeChoiceLoader;

    public function __construct(
        ContentTypeService $contentTypeService,
        ContentTypeChoiceLoader $contentTypeChoiceLoader
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->contentTypeChoiceLoader = $contentTypeChoiceLoader;
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choice_loader' => $this->contentTypeChoiceLoader,
                'choice_label' => 'name',
                'choice_value' => 'identifier',
            ]);
    }
}
