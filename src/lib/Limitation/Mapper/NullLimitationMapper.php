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
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\FormInterface;

final readonly class NullLimitationMapper implements LimitationFormMapperInterface, LimitationValueMapperInterface, TranslationContainerInterface
{
    public function __construct(private string $template)
    {
    }

    public function mapLimitationForm(FormInterface $form, Limitation $data): void
    {
    }

    public function getFormTemplate(): string
    {
        return $this->template;
    }

    public function filterLimitationValues(Limitation $limitation): void
    {
    }

    /**
     * @return mixed[]
     */
    public function mapLimitationValue(Limitation $limitation): array
    {
        return $limitation->limitationValues;
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(
                LimitationIdentifierToLabelConverter::convert('status'),
                'ibexa_content_forms_policies'
            )->setDesc('Status'),
        ];
    }
}
