<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ParamConverter;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LanguageParamConverter implements ParamConverterInterface
{
    public const PARAMETER_LANGUAGE_ID = 'languageId';
    public const PARAMETER_LANGUAGE_CODE = 'languageCode';

    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    private $languageService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LanguageService $languageService
     */
    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if (!$request->get(self::PARAMETER_LANGUAGE_ID) && !$request->get(self::PARAMETER_LANGUAGE_CODE)) {
            return false;
        }

        if ($request->get(self::PARAMETER_LANGUAGE_ID)) {
            $id = (int)$request->get(self::PARAMETER_LANGUAGE_ID);

            try {
                $language = $this->languageService->loadLanguageById($id);
            } catch (NotFoundException $e) {
                throw new NotFoundHttpException("Language $id not found.");
            }
        } elseif ($request->get(self::PARAMETER_LANGUAGE_CODE)) {
            $languageCode = $request->get(self::PARAMETER_LANGUAGE_CODE);

            try {
                $language = $this->languageService->loadLanguage($languageCode);
            } catch (NotFoundException $e) {
                throw new NotFoundHttpException("Language $languageCode not found.");
            }
        }

        $request->attributes->set($configuration->getName(), $language);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return Language::class === $configuration->getClass();
    }
}

class_alias(LanguageParamConverter::class, 'EzSystems\EzPlatformAdminUiBundle\ParamConverter\LanguageParamConverter');
