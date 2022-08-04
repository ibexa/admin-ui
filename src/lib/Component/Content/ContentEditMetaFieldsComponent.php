<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component\Content;

use Ibexa\Contracts\AdminUi\Component\Renderable;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Twig\Environment;

class ContentEditMetaFieldsComponent implements Renderable
{
    private const NO_CONTENT = '';

    private Environment $twig;

    private ConfigResolverInterface $configResolver;

    public function __construct(
        Environment $twig,
        ConfigResolverInterface $configResolver
    ) {
        $this->twig = $twig;
        $this->configResolver = $configResolver;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return string
     */
    public function render(array $parameters = []): string
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType */
        $contentType = $parameters['content_type'];

        $metaFieldTypeIdentifiers = $this->getMetaFieldTypeIdentifiers();

        if (empty($metaFieldTypeIdentifiers)) {
            return self::NO_CONTENT;
        }

        $parameters['meta_fields'] = [];
        foreach ($metaFieldTypeIdentifiers as $identifier) {
            $fields = $contentType->getFieldDefinitionsOfType($identifier);
            $parameters['meta_fields'] += array_column($fields->toArray(), 'identifier');
        }

        return $this->twig->render(
            '@ibexadesign/content/components/meta_fields.html.twig',
            $parameters
        );
    }

    /**
     * @return array<string>
     */
    private function getMetaFieldTypeIdentifiers(): array
    {
        $fieldTypeConfig = $this->configResolver->getParameter('admin_ui_forms.content_edit.fieldtypes');

        return array_keys(array_filter($fieldTypeConfig, static fn (array $config) => true === $config['meta']));
    }
}
