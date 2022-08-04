<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Provider;

use Ibexa\Contracts\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProviderInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

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

        $groupedFields = $this->innerGroupedContentFormFieldsProvider->getGroupedFields($fieldsDataForm);
        foreach ($groupedFields as $group => $fields) {
            $groupedFields[$group] = array_filter(
                $fields,
                static function (string $fieldIdentifier) use ($fieldsDataForm, $identifiers): bool {
                    $fieldData = $fieldsDataForm[$fieldIdentifier]->getNormData();
                    $fieldTypeIdentifier = $fieldData->fieldDefinition->fieldTypeIdentifier;

                    return !in_array($fieldTypeIdentifier, $identifiers, true);
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
}
