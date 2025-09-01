<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation\Templating;

use Ibexa\AdminUi\Exception\MissingLimitationBlockException;
use Ibexa\AdminUi\Exception\ValueMapperNotFoundException;
use Ibexa\AdminUi\Limitation\LimitationValueMapperRegistryInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Twig\Environment;
use Twig\Template;
use Twig\TemplateWrapper;

final readonly class LimitationBlockRenderer implements LimitationBlockRendererInterface
{
    public const string LIMITATION_VALUE_BLOCK_NAME = 'ibexa_limitation_%s_value';
    public const string LIMITATION_VALUE_BLOCK_NAME_FALLBACK = 'ibexa_limitation_value_fallback';

    public function __construct(
        private LimitationValueMapperRegistryInterface $valueMapperRegistry,
        private Environment $twig,
        private ConfigResolverInterface $configResolver
    ) {
    }

    public function renderLimitationValue(Limitation $limitation, array $parameters = []): string
    {
        try {
            $blockName = $this->getValueBlockName($limitation);
            $parameters = $this->getValueBlockParameters($limitation, $parameters);
        } catch (ValueMapperNotFoundException | NotFoundException $exception) {
            $blockName = self::LIMITATION_VALUE_BLOCK_NAME_FALLBACK;
            $parameters = $this->getValueFallbackBlockParameters($limitation, $parameters);
        }

        $localTemplate = null;
        if (isset($parameters['template'])) {
            $localTemplate = $parameters['template'];
            unset($parameters['template']);
        }

        $template = $this->findTemplateWithBlock($blockName, $localTemplate);
        if ($template === null) {
            throw new MissingLimitationBlockException("Could not find a block for {$limitation->getIdentifier()}: $blockName.");
        }

        return $template->renderBlock($blockName, $parameters);
    }

    private function getValueBlockName(Limitation $limitation): string
    {
        return sprintf(self::LIMITATION_VALUE_BLOCK_NAME, strtolower($limitation->getIdentifier()));
    }

    /**
     * Finds the first template containing block definition $blockName.
     */
    private function findTemplateWithBlock(
        string $blockName,
        string|Template|null $localTemplate = null
    ): TemplateWrapper|Template|null {
        if ($localTemplate !== null) {
            if (is_string($localTemplate)) {
                $localTemplate = $this->twig->load($localTemplate);
            }

            if ($localTemplate->hasBlock($blockName)) {
                return $localTemplate;
            }
        }

        foreach ($this->getLimitationValueResources() as &$template) {
            $template = $this->twig->load($template);
            if ($template->hasBlock($blockName)) {
                return $template;
            }
        }

        return null;
    }

    /**
     * Get parameters passed as context of value block render.
     *
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    private function getValueBlockParameters(Limitation $limitation, array $parameters): array
    {
        $values = $this->valueMapperRegistry
            ->getMapper($limitation->getIdentifier())
            ->mapLimitationValue($limitation);

        $parameters += [
            'limitation' => $limitation,
            'values' => $values,
        ];

        return $parameters;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    private function getValueFallbackBlockParameters(Limitation $limitation, array $parameters): array
    {
        $parameters += [
            'limitation' => $limitation,
            'values' => $limitation->limitationValues,
        ];

        return $parameters;
    }

    /**
     * @return string[]
     */
    private function getLimitationValueResources(): array
    {
        $resources = $this->configResolver->getParameter('limitation_value_templates');

        usort($resources, static function (array $a, array $b): int {
            return $b['priority'] <=> $a['priority'];
        });

        return array_column($resources, 'template');
    }
}
