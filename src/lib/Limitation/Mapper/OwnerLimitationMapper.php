<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use JMS\TranslationBundle\Annotation\Desc;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OwnerLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface, TranslationContainerInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @return array<int, string>
     */
    protected function getSelectionChoices(): array
    {
        // 2: "Session" is not supported yet, see OwnerLimitationType
        return [
            1 => $this->translator->trans(/** @Desc("Self") */
                'policy.limitation.owner.self',
                [],
                'ibexa_content_forms_role'
            ),
        ];
    }

    /**
     * @return string[]
     */
    public function mapLimitationValue(Limitation $limitation): array
    {
        return [
            $this->translator->trans(/** @Desc("Self") */
                'policy.limitation.owner.self',
                [],
                'ibexa_content_forms_role'
            ),
        ];
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(
                LimitationIdentifierToLabelConverter::convert('owner'),
                'ibexa_content_forms_policies'
            )->setDesc('Owner'),
            Message::create(
                LimitationIdentifierToLabelConverter::convert('parentowner'),
                'ibexa_content_forms_policies'
            )->setDesc('Owner of Parent'),
        ];
    }
}
