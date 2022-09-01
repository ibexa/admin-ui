<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component\ContentType;

use Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolverInterface;
use Ibexa\Contracts\AdminUi\Component\Renderable;
use Twig\Environment;

final class ContentTypeEditMetaFieldsComponent implements Renderable
{
    private const NO_CONTENT = '';

    private ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver;

    private Environment $twig;

    public function __construct(
        ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver,
        Environment $twig
    ) {
        $this->contentTypeFieldTypesResolver = $contentTypeFieldTypesResolver;
        $this->twig = $twig;
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

        $metaFieldTypeIdentifiers = $this->contentTypeFieldTypesResolver->getMetaFieldTypeIdentifiers();

        if (empty($metaFieldTypeIdentifiers)) {
            return self::NO_CONTENT;
        }

        $parameters['meta_fields'] = [];
        foreach ($metaFieldTypeIdentifiers as $identifier) {
            $fields = $contentType->getFieldDefinitionsOfType($identifier);
            $parameters['meta_fields'] = array_merge(
                $parameters['meta_fields'],
                array_column($fields->toArray(), 'identifier')
            );
        }

        return $this->twig->render(
            '@ibexadesign/content_type/components/meta_fields.html.twig',
            $parameters
        );
    }
}
