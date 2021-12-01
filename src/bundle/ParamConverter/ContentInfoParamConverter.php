<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ParamConverter;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentInfoParamConverter implements ParamConverterInterface
{
    public const PARAMETER_CONTENT_INFO_ID = 'contentInfoId';

    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentTypeService
     */
    public function __construct(ContentService $contentTypeService)
    {
        $this->contentService = $contentTypeService;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $id = (int)$request->get(self::PARAMETER_CONTENT_INFO_ID);
        $contentInfo = $this->contentService->loadContentInfo($id);

        if (!$contentInfo) {
            throw new NotFoundHttpException("Content Info $id not found.");
        }

        $request->attributes->set($configuration->getName(), $contentInfo);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return ContentInfo::class === $configuration->getClass();
    }
}

class_alias(ContentInfoParamConverter::class, 'EzSystems\EzPlatformAdminUiBundle\ParamConverter\ContentInfoParamConverter');
