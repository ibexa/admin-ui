<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ParamConverter;

use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ObjectStateGroupParamConverter implements ParamConverterInterface
{
    public const PARAMETER_OBJECT_STATE_GROUP_ID = 'objectStateGroupId';

    /** @var \Ibexa\Contracts\Core\Repository\ObjectStateService */
    private $objectStateService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ObjectStateService $objectStateService
     */
    public function __construct(ObjectStateService $objectStateService)
    {
        $this->objectStateService = $objectStateService;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter $configuration
     *
     * @return bool
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $id = (int)$request->get(self::PARAMETER_OBJECT_STATE_GROUP_ID);
        $objectStateGroup = $this->objectStateService->loadObjectStateGroup($id);

        if (!$objectStateGroup) {
            throw new NotFoundHttpException("Object state group $id not found.");
        }

        $request->attributes->set($configuration->getName(), $objectStateGroup);

        return true;
    }

    /**
     * @param \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter $configuration
     *
     * @return bool
     */
    public function supports(ParamConverter $configuration)
    {
        return ObjectStateGroup::class === $configuration->getClass();
    }
}
