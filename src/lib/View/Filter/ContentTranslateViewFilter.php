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
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
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
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoSuchOptionException;
use Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * Handles content translation form.
 */
class ContentTranslateViewFilter implements EventSubscriberInterface
{
    /** @var ContentService */
    private $contentService;

    /** @var LanguageService */
    private $languageService;

    /** @var ContentTypeService */
    private $contentTypeService;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var UserLanguagePreferenceProviderInterface */
    private $languagePreferenceProvider;

    /**
     * @param ContentService $contentService
     * @param LanguageService $languageService
     * @param ContentTypeService $contentTypeService
     * @param FormFactoryInterface $formFactory
     * @param UserLanguagePreferenceProviderInterface $languagePreferenceProvider
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

    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_BUILDER_PARAMETERS => 'handleContentTranslateForm'];
    }

    /**
     * @param FilterViewBuilderParametersEvent $event
     *
     * @throws UndefinedOptionsException
     * @throws OptionDefinitionException
     * @throws NoSuchOptionException
     * @throws MissingOptionsException
     * @throws AccessException
     * @throws InvalidOptionsException
     * @throws UnauthorizedException
     * @throws NotFoundException
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
     * @param Content $content
     * @param Language $toLanguage
     * @param Language|null $fromLanguage
     * @param ContentType $contentType
     *
     * @return ContentTranslationData
     *
     * @throws UndefinedOptionsException
     * @throws OptionDefinitionException
     * @throws NoSuchOptionException
     * @throws MissingOptionsException
     * @throws InvalidOptionsException
     * @throws AccessException
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
     * @param ContentTranslationData $contentUpdate
     * @param Language $toLanguage
     * @param Content $content
     *
     * @return FormInterface
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

class_alias(ContentTranslateViewFilter::class, 'EzSystems\EzPlatformAdminUi\View\Filter\ContentTranslateViewFilter');
