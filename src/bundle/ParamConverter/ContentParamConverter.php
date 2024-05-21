<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ParamConverter;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Loads Content object using ids from request parameters.
 */
class ContentParamConverter implements ParamConverterInterface
{
    public const PARAMETER_CONTENT_ID = 'contentId';
    public const PARAMETER_VERSION_NO = 'versionNo';
    public const PARAMETER_LANGUAGE_CODE = 'languageCode';

    /**
     * @var \Ibexa\Contracts\Core\Repository\ContentService
     */
    private $contentService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     */
    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $contentId = $request->get(self::PARAMETER_CONTENT_ID);
        $versionNo = $request->get(self::PARAMETER_VERSION_NO);
        $languageCode = $request->get(self::PARAMETER_LANGUAGE_CODE);

        if (null === $contentId || !\is_array($languageCode)) {
            return false;
        }

        $content = $this->contentService->loadContent($contentId, $languageCode, $versionNo);

        $request->attributes->set($configuration->getName(), $content);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration): bool
    {
        return Content::class === $configuration->getClass();
    }
}
