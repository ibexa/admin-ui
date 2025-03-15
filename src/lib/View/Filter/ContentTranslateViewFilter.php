<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\View\Filter;

use Ibexa\AdminUi\Form\Data\ContentTranslationData;
use Ibexa\AdminUi\Form\Data\FormMapper\ContentTranslationMapper;
use Ibexa\ContentForms\Form\Type\Content\ContentEditType;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent;
use Ibexa\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Handles content translation form.
 */
class ContentTranslateViewFilter implements EventSubscriberInterface
{
    private ContentService $contentService;

    private LanguageService $languageService;

    private ContentTypeService $contentTypeService;

    private FormFactoryInterface $formFactory;

    private UserLanguagePreferenceProviderInterface $languagePreferenceProvider;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\LanguageService $languageService
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface $languagePreferenceProvider
     */
    public function __construct(
        ContentService $contentService,
        LanguageService $languageService,
        ContentTypeService $contentTypeService,
        FormFactoryInterface $formFactory,
        UserLanguagePreferenceProviderInterface $languagePreferenceProvider
    ) {
        $this->contentService = $contentService;
        $this->languageService = $languageService;
        $this->contentTypeService = $contentTypeService;
        $this->formFactory = $formFactory;
        $this->languagePreferenceProvider = $languagePreferenceProvider;
    }

    public static function getSubscribedEvents(): array
    {
        return [ViewEvents::FILTER_BUILDER_PARAMETERS => 'handleContentTranslateForm'];
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent $event
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\NoSuchOptionException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function handleContentTranslateForm(FilterViewBuilderParametersEvent $event): void
    {
        $controllerAction = $event->getParameters()->get('_controller');

        if (
            'Ibexa\Bundle\AdminUi\Controller\ContentEditController::translateAction' !== $controllerAction
        ) {
            return;
        }

        $request = $event->getRequest();
        $languageCode = $request->attributes->get('toLanguageCode');
        $baseLanguageCode = $request->attributes->get('fromLanguageCode');
        $content = $this->contentService->loadContent(
            (int)$request->attributes->get('contentId'),
            null !== $baseLanguageCode ? [$baseLanguageCode] : null
        );
        $contentType = $this->contentTypeService->loadContentType(
            $content->getContentType()->id,
            $this->languagePreferenceProvider->getPreferredLanguages()
        );
        $toLanguage = $this->languageService->loadLanguage($languageCode);
        $fromLanguage = $baseLanguageCode ? $this->languageService->loadLanguage($baseLanguageCode) : null;

        $contentTranslateData = $this->resolveContentTranslationData(
            $content,
            $toLanguage,
            $fromLanguage,
            $contentType
        );
        $form = $this->resolveContentTranslateForm(
            $contentTranslateData,
            $toLanguage,
            $content
        );

        $event->getParameters()->add(['form' => $form->handleRequest($request)]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language $toLanguage
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language|null $fromLanguage
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \Ibexa\AdminUi\Form\Data\ContentTranslationData
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\NoSuchOptionException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    private function resolveContentTranslationData(
        Content $content,
        Language $toLanguage,
        ?Language $fromLanguage,
        ContentType $contentType
    ): ContentTranslationData {
        $contentTranslationMapper = new ContentTranslationMapper();

        return $contentTranslationMapper->mapToFormData(
            $content,
            [
                'language' => $toLanguage,
                'baseLanguage' => $fromLanguage,
                'contentType' => $contentType,
            ]
        );
    }

    /**
     * @param \Ibexa\AdminUi\Form\Data\ContentTranslationData $contentUpdate
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language $toLanguage
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function resolveContentTranslateForm(
        ContentTranslationData $contentUpdate,
        Language $toLanguage,
        Content $content
    ): FormInterface {
        return $this->formFactory->create(
            ContentEditType::class,
            $contentUpdate,
            [
                'languageCode' => $toLanguage->languageCode,
                'mainLanguageCode' => $content->contentInfo->mainLanguageCode,
                'content' => $content,
                'contentUpdateStruct' => $contentUpdate,
                'drafts_enabled' => true,
            ]
        );
    }
}
