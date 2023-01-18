<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Provider;

use Ibexa\Contracts\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProviderInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\Form\FormInterface;

final class GroupedNonMetaFormFieldsProvider implements GroupedContentFormFieldsProviderInterface
{
    private GroupedContentFormFieldsProviderInterface $innerGroupedContentFormFieldsProvider;

    private ConfigResolverInterface $configResolver;

    public function __construct(
        GroupedContentFormFieldsProviderInterface $innerGroupedContentFormFieldsProvider,
        ConfigResolverInterface $configResolver
    ) {
        $this->innerGroupedContentFormFieldsProvider = $innerGroupedContentFormFieldsProvider;
        $this->configResolver = $configResolver;
    }

    public function getGroupedFields(array $fieldsDataForm): array
    {
        $identifiers = $this->getMetaFields();
        $metaFieldGroups = $this->getMetaFieldGroups();

        $metaFieldIdentifiers = array_keys(
            array_filter(
                $fieldsDataForm,
                static fn (FormInterface $field): bool => true
                    === in_array($field->getData()->fieldDefinition->fieldGroup, $metaFieldGroups)
            )
        );

        $groupedFields = $this->innerGroupedContentFormFieldsProvider->getGroupedFields($fieldsDataForm);
        foreach ($groupedFields as $group => $fields) {
            $groupedFields[$group] = array_filter(
                $fields,
                static function (string $fieldIdentifier) use (
                    $fieldsDataForm,
                    $identifiers,
                    $metaFieldIdentifiers
                ): bool {
                    $fieldData = $fieldsDataForm[$fieldIdentifier]->getNormData();
                    $fieldTypeIdentifier = $fieldData->fieldDefinition->fieldTypeIdentifier;
                    $fieldIdentifier = $fieldData->fieldDefinition->identifier;

                    return !in_array($fieldTypeIdentifier, $identifiers, true)
                        && !in_array($fieldIdentifier, $metaFieldIdentifiers);
                }
            );
        }

        return array_filter($groupedFields);
    }

    /**
     * @return array<string>
     */
    private function getMetaFields(): array
    {
        $fieldTypesConfig = $this->configResolver->getParameter('admin_ui_forms.content_edit.fieldtypes');

        return array_keys(
            array_filter(
                $fieldTypesConfig,
                static fn (array $config): bool => true === $config['meta']
            )
        );
    }

    /**
     * @return array<string>
     */
    private function getMetaFieldGroups(): array
    {
        return $this->configResolver->getParameter('admin_ui_forms.content_edit.meta_field_groups_list');
    }
}
