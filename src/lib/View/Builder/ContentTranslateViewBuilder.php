<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\View\Builder;

use Ibexa\AdminUi\View\ContentTranslateSuccessView;
use Ibexa\AdminUi\View\ContentTranslateView;
use Ibexa\ContentForms\Form\ActionDispatcher\ActionDispatcherInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\InvalidArgumentType;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\Core\MVC\Symfony\View\Builder\ViewBuilder;
use Ibexa\Core\MVC\Symfony\View\Configurator;
use Ibexa\Core\MVC\Symfony\View\ParametersInjector;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * Builds ContentEditView objects.
 *
 * @internal
 */
class ContentTranslateViewBuilder implements ViewBuilder
{
    /** @var Repository */
    private $repository;

    /** @var Configurator */
    private $viewConfigurator;

    /** @var ParametersInjector */
    private $viewParametersInjector;

    /** @var ActionDispatcherInterface */
    private $contentActionDispatcher;

    /** @var UserLanguagePreferenceProviderInterface */
    private $languagePreferenceProvider;

    public function __construct(
        Repository $repository,
        Configurator $viewConfigurator,
        ParametersInjector $viewParametersInjector,
        ActionDispatcherInterface $contentActionDispatcher,
        UserLanguagePreferenceProviderInterface $languagePreferenceProvider
    ) {
        $this->repository = $repository;
        $this->viewConfigurator = $viewConfigurator;
        $this->viewParametersInjector = $viewParametersInjector;
        $this->contentActionDispatcher = $contentActionDispatcher;
        $this->languagePreferenceProvider = $languagePreferenceProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function matches($argument)
    {
        return 'Ibexa\Bundle\AdminUi\Controller\ContentEditController::translateAction' === $argument;
    }

    /**
     * @param array $parameters
     *
     * @return ContentTranslateSuccessView|ContentTranslateView
     *
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws InvalidArgumentType
     * @throws InvalidOptionsException
     * @throws BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws UnauthorizedException
     */
    public function buildView(array $parameters)
    {
        $view = new ContentTranslateView();

        $fromLanguage = $this->resolveFromLanguage($parameters);
        $toLanguage = $this->resolveToLanguage($parameters);
        $location = $this->resolveLocation($parameters, $fromLanguage);
        $content = $this->resolveContent($parameters, $location, $fromLanguage);
        $contentInfo = $content->contentInfo;
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $content->getContentType()->id,
            $this->languagePreferenceProvider->getPreferredLanguages()
        );
        /** @var FormInterface $form */
        $form = $parameters['form'];

        if (null === $location && $contentInfo->isPublished()) {
            // assume main location if no location was provided
            $location = $this->loadLocation((int) $contentInfo->mainLocationId);
        }

        if ($form->isSubmitted() && $form->isValid() && null !== $form->getClickedButton()) {
            $this->contentActionDispatcher->dispatchFormAction(
                $form,
                $form->getData(),
                $form->getClickedButton()->getName(),
                ['referrerLocation' => $location]
            );

            if ($response = $this->contentActionDispatcher->getResponse()) {
                return new ContentTranslateSuccessView($response);
            }
        }

        $formView = $form->createView();

        $view->setContent($content);
        $view->setContentType($contentType);
        $view->setLanguage($toLanguage);
        $view->setBaseLanguage($fromLanguage);
        $view->setLocation($location);
        $view->setForm($parameters['form']);
        $view->setFormView($formView);

        $this->viewParametersInjector->injectViewParameters($view, $parameters);
        $this->viewConfigurator->configure($view);

        return $view;
    }

    /**
     * Loads Content with id $contentId.
     *
     * @param array $languages
     *
     * @return Content
     *
     * @throws UnauthorizedException
     * @throws NotFoundException
     */
    private function loadContent(
        int $contentId,
        array $languages = [],
        ?int $versionNo = null
    ): Content {
        return $this->repository->getContentService()->loadContent($contentId, $languages, $versionNo);
    }

    /**
     * Loads a visible Location.
     *
     * @param array|null $languages
     *
     * @return Location
     *
     * @throws UnauthorizedException
     * @throws NotFoundException
     */
    private function loadLocation(
        int $locationId,
        ?array $languages = null
    ): Location {
        return $this->repository->getLocationService()->loadLocation($locationId, $languages);
    }

    /**
     * Loads Language with code $languageCode.
     *
     * @throws NotFoundException
     */
    private function loadLanguage(string $languageCode): Language
    {
        return $this->repository->getContentLanguageService()->loadLanguage($languageCode);
    }

    /**
     * @param array $parameters
     *
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    private function resolveFromLanguage(array $parameters): ?Language
    {
        if (isset($parameters['fromLanguage'])) {
            return $parameters['fromLanguage'];
        }

        if (isset($parameters['fromLanguageCode'])) {
            return $this->loadLanguage($parameters['fromLanguageCode']);
        }

        return null;
    }

    /**
     * @param array $parameters
     *
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    private function resolveToLanguage(array $parameters): Language
    {
        if (isset($parameters['toLanguage'])) {
            return $parameters['toLanguage'];
        }

        if (isset($parameters['toLanguageCode'])) {
            return $this->loadLanguage($parameters['toLanguageCode']);
        }

        throw new InvalidArgumentException(
            'Language',
            'No language information provided. Check the \'toLanguage\' and \'toLanguageCode\' parameters'
        );
    }

    /**
     * @param array $parameters
     *
     * @throws InvalidArgumentException
     * @throws UnauthorizedException
     * @throws NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function resolveContent(
        array $parameters,
        ?Location $location,
        ?Language $language
    ): Content {
        if (isset($parameters['content'])) {
            return $parameters['content'];
        } elseif (null !== $location) {
            return $location->getContent();
        }

        if (!isset($parameters['contentId'])) {
            throw new InvalidArgumentException(
                'Content',
                'No content could be loaded from the parameters'
            );
        }

        return $this->loadContent(
            (int) $parameters['contentId'],
            null !== $language ? [$language->languageCode] : []
        );
    }

    /**
     * @param array $parameters
     *
     * @throws UnauthorizedException
     * @throws NotFoundException
     */
    private function resolveLocation(
        array $parameters,
        ?Language $language
    ): ?Location {
        if (isset($parameters['location'])) {
            return $parameters['location'];
        }

        if (isset($parameters['locationId'])) {
            return $this->loadLocation(
                (int) $parameters['locationId'],
                null !== $language ? [$language->languageCode] : null
            );
        }

        return null;
    }
}

class_alias(ContentTranslateViewBuilder::class, 'EzSystems\EzPlatformAdminUi\View\Builder\ContentTranslateViewBuilder');
