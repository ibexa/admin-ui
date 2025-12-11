<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\ContentName;
use Ibexa\Contracts\Core\Repository\Values\Filter\Filter;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use Ibexa\Core\Limitation\MemberOfLimitationType;
use JMS\TranslationBundle\Annotation\Desc;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MemberOfLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface, TranslationContainerInterface
{
    public function __construct(
        private readonly UserService $userService,
        private readonly Repository $repository,
        private readonly ContentService $contentService,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * @return array<int, string|null>
     */
    protected function getSelectionChoices(): array
    {
        $userGroups = $this->loadUserGroups();
        $choices = [];
        $choices[MemberOfLimitationType::SELF_USER_GROUP] = $this->getSelfUserGroupLabel();

        foreach ($userGroups as $userGroup) {
            $choices[$userGroup->getId()] = $userGroup->getName();
        }

        return $choices;
    }

    public function mapLimitationValue(Limitation $limitation): array
    {
        $values = [];
        foreach ($limitation->limitationValues as $groupId) {
            if ((int)$groupId === MemberOfLimitationType::SELF_USER_GROUP) {
                $values[] = $this->getSelfUserGroupLabel();
                continue;
            }
            $values[] = $this->userService->loadUserGroup((int)$groupId)->getName();
        }

        return $values;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[]
     */
    private function loadUserGroups(): array
    {
        return $this->repository->sudo(function (): array {
            $filter = new Filter();
            $filter->withCriterion(new ContentTypeIdentifier('user_group'));
            $filter->withSortClause(new ContentName());
            $results = $this->contentService->find($filter);

            $groups = [];
            foreach ($results as $result) {
                $groups[] = $this->userService->loadUserGroup($result->getId());
            }

            return $groups;
        });
    }

    private function getSelfUserGroupLabel(): string
    {
        return $this->translator->trans(
            /** @Desc("Self") */
            'policy.limitation.member_of.self_user_group',
            [],
            'ibexa_content_forms_role'
        );
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(
                LimitationIdentifierToLabelConverter::convert('memberof'),
                'ibexa_content_forms_policies'
            )->setDesc('MemberOf'),
        ];
    }
}
