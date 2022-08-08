<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\AbstractParser;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Configuration parser for Admin UI forms settings.
 *
 * Example configuration:
 * ```yaml
 * ezpublish:
 *   system:
 *      admin_group: # configuration per SiteAccess or SiteAccess group
 *          admin_ui_forms:
 *              content_edit_form_templates:
 *                  - { template: 'template.html.twig', priority: 0 }
 * ```
 */
class AdminUiForms extends AbstractParser
{
    public const FORM_TEMPLATES_PARAM = 'admin_ui_forms.content_edit_form_templates';
    public const FIELD_TYPES_PARAM = 'admin_ui_forms.content_edit.fieldtypes';

    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     */
    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('admin_ui_forms')
                ->info('Admin UI forms configuration settings')
                ->beforeNormalization()
                    ->always(static function (array $array): array {
                        // handle deprecated config
                        if (isset($array['content_edit_form_templates'])) {
                            $array['content_edit']['form_templates'] = $array['content_edit_form_templates'];
                            unset($array['content_edit_form_templates']);
                        }

                        return $array;
                    })
                ->end()
                ->children()
                    ->arrayNode('content_edit')
                        ->info('Content Edit form configuration')
                        ->children()
                            ->arrayNode('form_templates')
                                ->info('A list of Content Edit (and create) default Twig form templates')
                                ->setDeprecated(
                                    'ibexa/admin-ui',
                                    '4.2.0',
                                    'Setting "admin_ui.content_edit_form_templates" is deprecated. Use "admin_ui.content_edit.form_templates" instead.'
                                )
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('template')->end()
                                        ->integerNode('priority')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('fieldtypes')
                                ->info('Configuration for specific FieldTypes')
                                ->useAttributeAsKey('identifier')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('identifier')->end()
                                        ->booleanNode('meta')
                                            ->info('Make this fieldtype a part of Meta group')
                                            ->defaultFalse()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function mapConfig(
        array &$scopeSettings,
        $currentScope,
        ContextualizerInterface $contextualizer
    ): void {
        if (!empty($scopeSettings['admin_ui_forms']['content_edit']['form_templates'])) {
            $scopeSettings['admin_ui_forms.content_edit_form_templates'] = $this->processContentEditFormTemplates(
                $scopeSettings['admin_ui_forms']['content_edit']['form_templates']
            );
            unset($scopeSettings['admin_ui_forms']['content_edit']['form_templates']);
        }

        if (!empty($scopeSettings['admin_ui_forms']['content_edit']['fieldtypes'])) {
            $scopeSettings['admin_ui_forms.content_edit.fieldtypes'] = $scopeSettings['admin_ui_forms']['content_edit']['fieldtypes'];
            unset($scopeSettings['admin_ui_forms']['content_edit']['fieldtypes']);
        }

        $contextualizer->setContextualParameter(
            static::FORM_TEMPLATES_PARAM,
            $currentScope,
            $scopeSettings['admin_ui_forms.content_edit_form_templates'] ?? []
        );
        $contextualizer->setContextualParameter(
            static::FIELD_TYPES_PARAM,
            $currentScope,
            $scopeSettings['admin_ui_forms.content_edit.fieldtypes'] ?? []
        );
    }

    /**
     * {@inheritdoc}
     */
    public function postMap(array $config, ContextualizerInterface $contextualizer)
    {
        $contextualizer->mapConfigArray('admin_ui_forms.content_edit_form_templates', $config);
        $contextualizer->mapConfigArray('admin_ui_forms.content_edit.fieldtypes', $config);
    }

    /**
     * Processes given prioritized list of templates, sorts them according to their priorities and
     * returns as a simple list of templates.
     *
     * The input list of the templates needs to be in the form of:
     * <code>
     *  [
     *      [ 'template' => '<file_path>', 'priority' => <int> ],
     *  ],
     * </code>
     *
     * @param array $formTemplates
     *
     * @return array ordered list of templates
     */
    private function processContentEditFormTemplates(array $formTemplates)
    {
        $priorities = array_column($formTemplates, 'priority');
        array_multisort($priorities, SORT_DESC, $formTemplates);

        // return as a simple list of templates.
        return array_column($formTemplates, 'template');
    }
}

class_alias(AdminUiForms::class, 'EzSystems\EzPlatformAdminUiBundle\DependencyInjection\Configuration\Parser\AdminUiForms');
