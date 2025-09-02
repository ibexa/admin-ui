<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Extension\Content;

use Ibexa\ContentForms\Form\Type\Content\ContentEditType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

class ContentEditTypeExtension extends AbstractTypeExtension
{
    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('preview', SubmitType::class, [
            'label' => /** @Desc("Preview") */ 'preview',
            'attr' => [
                'hidden' => true,
                'formnovalidate' => 'formnovalidate',
            ],
            'translation_domain' => 'ibexa_content_preview',
        ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, static function (PostSubmitEvent $event): void {
            $form = $event->getForm();

            if ($form->get('preview')->isClicked()) {
                $event->stopPropagation();
            }
        }, 900);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ContentEditType::class];
    }
}
