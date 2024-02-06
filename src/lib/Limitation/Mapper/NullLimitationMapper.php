<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationFormMapperInterface;
use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\FormInterface;

class NullLimitationMapper implements LimitationFormMapperInterface, LimitationValueMapperInterface, TranslationContainerInterface
{
    /**
     * @var string
     */
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function mapLimitationForm(FormInterface $form, Limitation $data)
    {
    }

    public function getFormTemplate()
    {
        return $this->template;
    }

    public function filterLimitationValues(Limitation $limitation)
    {
    }

    public function mapLimitationValue(Limitation $limitation)
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

class_alias(NullLimitationMapper::class, 'EzSystems\EzPlatformAdminUi\Limitation\Mapper\NullLimitationMapper');
