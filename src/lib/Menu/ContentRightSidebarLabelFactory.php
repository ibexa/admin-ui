<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu;

use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUserGroup;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsDashboardContainer;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

final class ContentRightSidebarLabelFactory implements ContentRightSidebarLabelFactoryInterface, TranslationContainerInterface
{
    public const CREATE = 'sidebar_right.create';
    public const CREATE_CONTENT = 'sidebar_right.create_content';
    public const CREATE_USER = 'sidebar_right.create_user';

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * Returns label based on content type.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     *
     * @return string
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function createLabel(ContentType $contentType): string
    {
        switch (true) {
            case $this->isUserGroup($contentType):
                return self::CREATE_USER;
            case $this->isDashboard($contentType):
                return self::CREATE;
            default:
                return self::CREATE_CONTENT;
        }
    }

    private function isUserGroup(ContentType $contentType): bool
    {
        return (new ContentTypeIsUserGroup($this->configResolver->getParameter('user_group_content_type_identifier')))->isSatisfiedBy($contentType);
    }

    private function isDashboard(ContentType $contentType): bool
    {
        return (new ContentTypeIsDashboardContainer($this->configResolver->getParameter('dashboard.container_content_type_identifier')))->isSatisfiedBy($contentType);
    }

    /**
     * @return \JMS\TranslationBundle\Model\Message[]
     */
    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::CREATE_CONTENT, 'ibexa_menu'))->setDesc('Create content'),
            (new Message(self::CREATE_USER, 'ibexa_menu'))->setDesc('Create user'),
            (new Message(self::CREATE, 'ibexa_menu'))->setDesc('Create'),
        ];
    }
}

class_alias(ContentRightSidebarLabelFactory::class, 'EzSystems\EzPlatformAdminUi\Menu\ContentRightSidebarLabelFactory');
