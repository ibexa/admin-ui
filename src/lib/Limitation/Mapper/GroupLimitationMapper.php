<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GroupLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface, TranslationContainerInterface
{
    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    protected function getSelectionChoices()
    {
        return [
            1 => $this->translator->trans(/** @Desc("Self") */
                'policy.limitation.group.self',
                [],
                'ezplatform_content_forms_role'
            ),
        ];
    }

    public function mapLimitationValue(Limitation $limitation)
    {
        return [
            $this->translator->trans(/** @Desc("Self") */
                'policy.limitation.group.self',
                [],
                'ezplatform_content_forms_role'
            ),
        ];
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(new Message(
                LimitationIdentifierToLabelConverter::convert('group'),
                'ezplatform_content_forms_policies'
            ))->setDesc('Content Type Group'),
        ];
    }
}

class_alias(GroupLimitationMapper::class, 'EzSystems\EzPlatformAdminUi\Limitation\Mapper\GroupLimitationMapper');
