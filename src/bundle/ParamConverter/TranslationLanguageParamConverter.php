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

class TranslationLanguageParamConverter implements ParamConverterInterface
{
    public const PARAMETER_LANGUAGE_CODE_TO = 'toLanguageCode';
    public const PARAMETER_LANGUAGE_CODE_FROM = 'fromLanguageCode';

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
        if ($request->get(self::PARAMETER_LANGUAGE_CODE_TO) && 'language' === $configuration->getName()) {
            $languageCode = $request->get(self::PARAMETER_LANGUAGE_CODE_TO);
        } elseif ($request->get(self::PARAMETER_LANGUAGE_CODE_FROM) && 'baseLanguage' === $configuration->getName()) {
            $languageCode = $request->get(self::PARAMETER_LANGUAGE_CODE_FROM);
        } else {
            return false;
        }

        $request->attributes->set($configuration->getName(), $this->getLanguage($languageCode));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return Language::class === $configuration->getClass();
    }

    /**
     * @param string $languageCode
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language
     */
    private function getLanguage(string $languageCode): Language
    {
        try {
            $language = $this->languageService->loadLanguage($languageCode);
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException("Language $languageCode not found.");
        }

        return $language;
    }
}

class_alias(TranslationLanguageParamConverter::class, 'EzSystems\EzPlatformAdminUiBundle\ParamConverter\TranslationLanguageParamConverter');
