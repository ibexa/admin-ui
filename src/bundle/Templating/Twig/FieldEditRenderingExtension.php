<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\Core\MVC\Symfony\Templating\Exception\MissingFieldBlockException;
use Ibexa\Core\MVC\Symfony\Templating\FieldBlockRendererInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FieldEditRenderingExtension extends AbstractExtension
{
    /** @var \Ibexa\Core\MVC\Symfony\Templating\FieldBlockRendererInterface|\Ibexa\Core\MVC\Symfony\Templating\Twig\FieldBlockRenderer */
    private $fieldBlockRenderer;

    public function __construct(FieldBlockRendererInterface $fieldBlockRenderer)
    {
        $this->fieldBlockRenderer = $fieldBlockRenderer;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        $fieldDefinitionEditCallable = function (Environment $twig, FieldDefinitionData $fieldDefinitionData, array $params = []) {
            $this->fieldBlockRenderer->setTwig($twig);

            return $this->renderFieldDefinitionEdit($fieldDefinitionData, $params);
        };

        return [
            new TwigFunction(
                'ez_render_field_definition_edit',
                $fieldDefinitionEditCallable,
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                    'deprecated' => '4.0',
                    'alternative' => 'ibexa_render_field_definition_edit',
                ]
            ),
            new TwigFunction(
                'ibexa_render_field_definition_edit',
                $fieldDefinitionEditCallable,
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
        ];
    }

    public function renderFieldDefinitionEdit(FieldDefinitionData $fieldDefinitionData, array $params = []): string
    {
        $params += ['data' => $fieldDefinitionData];
        try {
            return $this->fieldBlockRenderer->renderFieldDefinitionEdit($fieldDefinitionData->fieldDefinition, $params);
        } catch (MissingFieldBlockException $e) {
            // Silently fail on purpose.
            // If there is no template block for current field definition, there might not be anything specific to add.
            return '';
        }
    }
}
