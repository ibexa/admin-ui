<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Type\ContentType;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ContentTypeCreateType extends AbstractType
{
    private ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_content_forms_contenttype_create';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'ibexa_content_type',
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contentTypeGroupId', HiddenType::class, [
                'constraints' => new Callback(
                    function ($contentTypeGroupId, ExecutionContextInterface $context): void {
                        try {
                            $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId);
                        } catch (NotFoundException $e) {
                            $context
                                ->buildViolation('content_type.error.content_type_group.not_found')
                                ->setParameter('%id%', $contentTypeGroupId)
                                ->addViolation();
                        }
                    }
                ),
            ])
            ->add('create', SubmitType::class, ['label' => /** @Desc("Create a content type") */ 'content_type.create']);
    }
}
