<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use JMS\TranslationBundle\Annotation\Desc;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GroupLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface, TranslationContainerInterface
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    protected function getSelectionChoices(): array
    {
        return [
            1 => $this->translator->trans(/** @Desc("Self") */
                'policy.limitation.group.self',
                [],
                'ibexa_content_forms_role'
            ),
        ];
    }

    public function mapLimitationValue(Limitation $limitation): array
    {
        return [
            $this->translator->trans(/** @Desc("Self") */
                'policy.limitation.group.self',
                [],
                'ibexa_content_forms_role'
            ),
        ];
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(
                LimitationIdentifierToLabelConverter::convert('group'),
                'ibexa_content_forms_policies'
            )->setDesc('Content type group'),
        ];
    }
}
