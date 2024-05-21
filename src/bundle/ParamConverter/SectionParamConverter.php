<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ParamConverter;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SectionParamConverter implements ParamConverterInterface
{
    public const PARAMETER_SECTION_ID = 'sectionId';

    /**
     * @var \Ibexa\Contracts\Core\Repository\SectionService
     */
    private $sectionService;

    /**
     * SectionParamConverter constructor.
     *
     * @param \Ibexa\Contracts\Core\Repository\SectionService $sectionService
     */
    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if (!$request->get(self::PARAMETER_SECTION_ID)) {
            return false;
        }

        $id = (int)$request->get(self::PARAMETER_SECTION_ID);

        try {
            $section = $this->sectionService->loadSection($id);
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException("Section $id not found.");
        }

        $request->attributes->set($configuration->getName(), $section);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return Section::class === $configuration->getClass();
    }
}
