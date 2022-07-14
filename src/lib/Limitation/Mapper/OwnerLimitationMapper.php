<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Symfony\Contracts\Translation\TranslatorInterface;

class OwnerLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface
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
        // 2: "Session" is not supported yet, see OwnerLimitationType
        return [
            1 => $this->translator->trans(/** @Desc("Self") */
                'policy.limitation.owner.self',
                [],
                'ezplatform_content_forms_role'
            ),
        ];
    }

    public function mapLimitationValue(Limitation $limitation)
    {
        return [
            $this->translator->trans(/** @Desc("Self") */
                'policy.limitation.owner.self',
                [],
                'ezplatform_content_forms_role'
            ),
        ];
    }
}

class_alias(OwnerLimitationMapper::class, 'EzSystems\EzPlatformAdminUi\Limitation\Mapper\OwnerLimitationMapper');
