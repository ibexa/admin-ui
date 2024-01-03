<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ParamConverter;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentTypeParamConverter implements ParamConverterInterface
{
    public const PARAMETER_CONTENT_TYPE_ID = 'contentTypeId';
    public const PARAMETER_CONTENT_TYPE_IDENTIFIER = 'contentTypeIdentifier';

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $languagePreferenceProvider;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeGroupService
     * @param \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface $languagePreferenceProvider
     */
    public function __construct(
        ContentTypeService $contentTypeGroupService,
        UserLanguagePreferenceProviderInterface $languagePreferenceProvider
    ) {
        $this->contentTypeService = $contentTypeGroupService;
        $this->languagePreferenceProvider = $languagePreferenceProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if (!$request->get(self::PARAMETER_CONTENT_TYPE_ID) && !$request->get(self::PARAMETER_CONTENT_TYPE_IDENTIFIER)) {
            return false;
        }

        $prioritizedLanguages = $this->languagePreferenceProvider->getPreferredLanguages();

        try {
            if ($request->get(self::PARAMETER_CONTENT_TYPE_ID)) {
                $id = (int)$request->get(self::PARAMETER_CONTENT_TYPE_ID);
                $contentType = $this->contentTypeService->loadContentType($id, $prioritizedLanguages);
            } elseif ($request->get(self::PARAMETER_CONTENT_TYPE_IDENTIFIER)) {
                $identifier = $request->get(self::PARAMETER_CONTENT_TYPE_IDENTIFIER);
                $contentType = $this->contentTypeService->loadContentTypeByIdentifier($identifier, $prioritizedLanguages);
            }
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException('Content type ' . ($id ?? $identifier) . ' not found.');
        }

        $request->attributes->set($configuration->getName(), $contentType);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return ContentType::class === $configuration->getClass();
    }
}

class_alias(ContentTypeParamConverter::class, 'EzSystems\EzPlatformAdminUiBundle\ParamConverter\ContentTypeParamConverter');
