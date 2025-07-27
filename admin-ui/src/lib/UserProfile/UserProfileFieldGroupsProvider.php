<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UserProfile;

use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use JMS\TranslationBundle\Annotation\Ignore;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserProfileFieldGroupsProvider implements FieldsGroupsList, TranslationContainerInterface
{
    private const FIELD_GROUPS = ['about', 'contact'];

    private FieldsGroupsList $innerService;

    private TranslatorInterface $translator;

    public function __construct(
        FieldsGroupsList $innerService,
        TranslatorInterface $translator
    ) {
        $this->innerService = $innerService;
        $this->translator = $translator;
    }

    /**
     * @return array<string, string>
     */
    public function getGroups(): array
    {
        $groups = $this->innerService->getGroups();
        foreach (self::FIELD_GROUPS as $group) {
            $groups[$group] = $this->translator->trans(/** @Ignore */ $group, [], 'ibexa_fields_groups');
        }

        return $groups;
    }

    public function getDefaultGroup(): string
    {
        return $this->innerService->getDefaultGroup();
    }

    public function getFieldGroup(FieldDefinition $fieldDefinition): string
    {
        return $this->innerService->getFieldGroup($fieldDefinition);
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create('about', 'ibexa_fields_groups')->setDesc('About'),
            Message::create('contact', 'ibexa_fields_groups')->setDesc('Contact'),
        ];
    }
}
