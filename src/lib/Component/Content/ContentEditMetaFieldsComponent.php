<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component\Content;

use Ibexa\Contracts\AdminUi\Component\Renderable;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
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
        $metaFieldDefinitionCollection = $this->getMetaFieldDefinitionCollection($contentType);

        if (empty($metaFieldTypeIdentifiers) && $metaFieldDefinitionCollection->isEmpty()) {
            return self::NO_CONTENT;
        }

        $metaFields = $this->mapMetaFieldDefinitionCollectionToIdentifiers($metaFieldDefinitionCollection);

        foreach ($metaFieldTypeIdentifiers as $identifier) {
            $fields = $contentType->getFieldDefinitionsOfType($identifier);
            $metaFields = array_merge($metaFields, array_column($fields->toArray(), 'identifier'));
        }

        $parameters['meta_fields'] = array_unique($metaFields);

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

        return array_keys(
            array_filter(
                $fieldTypeConfig,
                static fn (array $config): bool => true === $config['meta']
            )
        );
    }

    private function getMetaFieldDefinitionCollection(ContentType $contentType): FieldDefinitionCollection
    {
        $metaFieldGroups = $this->configResolver->getParameter(
            'admin_ui_forms.content_edit.meta_field_groups_list'
        );

        return $contentType->fieldDefinitions->filter(
            static fn (FieldDefinition $field): bool => in_array($field->fieldGroup, $metaFieldGroups, true),
        );
    }

    /**
     * @return array<string>
     */
    private function mapMetaFieldDefinitionCollectionToIdentifiers(
        FieldDefinitionCollection $metaFieldDefinitionCollection
    ): array {
        return array_column($metaFieldDefinitionCollection->toArray(), 'identifier');
    }
}
