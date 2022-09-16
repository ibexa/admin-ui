<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Config\AdminUiForms;

use Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\AdminUiForms;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

/**
 * @internal
 */
final class ContentTypeFieldTypesResolver implements ContentTypeFieldTypesResolverInterface
{
    private ConfigResolverInterface $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @return array<string, array{
     *     'meta'?: bool
     * }>
     */
    public function getFieldTypes(): array
    {
        if (!$this->configResolver->hasParameter(AdminUiForms::CONTENT_TYPE_FIELD_TYPES_PARAM)) {
            return [];
        }

        return $this->configResolver->getParameter(AdminUiForms::CONTENT_TYPE_FIELD_TYPES_PARAM);
    }

    /**
     * @return array<string>
     */
    public function getMetaFieldTypeIdentifiers(): array
    {
        $fieldTypeConfig = $this->getFieldTypes();

        return array_keys(
            array_filter(
                $fieldTypeConfig,
                static fn (array $config): bool => true === $config['meta']
            )
        );
    }
}
