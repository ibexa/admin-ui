<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationFormMapperInterface;
use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ChangeOwnerLimitation;
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use JMS\TranslationBundle\Annotation\Desc;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ChangeOwnerLimitationMapper implements LimitationValueMapperInterface, LimitationFormMapperInterface, TranslationContainerInterface
{
    private ?string $formTemplate = null;

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * @return int[]
     */
    public function mapLimitationValue(Limitation $limitation): array
    {
        return $limitation->limitationValues;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $form
     */
    public function mapLimitationForm(FormInterface $form, Limitation $data): void
    {
        $options = [
            'multiple' => true,
            'expanded' => false,
            'required' => false,
            'label' => LimitationIdentifierToLabelConverter::convert($data->getIdentifier()),
            'choices' => array_flip($this->getSelectionChoices()),
        ];

        $form->add('limitationValues', ChoiceType::class, $options);
    }

    public function getFormTemplate(): ?string
    {
        return $this->formTemplate;
    }

    public function filterLimitationValues(Limitation $limitation): void
    {
    }

    /**
     * @return array<int, string>
     */
    private function getSelectionChoices(): array
    {
        return [
            ChangeOwnerLimitation::LIMITATION_VALUE_SELF => $this->translator->trans(/** @Desc("Forbid") */
                'policy.limitation.change_owner.forbid',
                [],
                'ibexa_content_forms_role'
            ),
        ];
    }

    public function setFormTemplate(string $formTemplate): void
    {
        $this->formTemplate = $formTemplate;
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(
                LimitationIdentifierToLabelConverter::convert('changeowner'),
                'ibexa_content_forms_policies'
            )->setDesc('Change Owner'),
        ];
    }
}
