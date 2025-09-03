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
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\Core\MVC\Symfony\View\Builder\ViewBuilder;
use Ibexa\Core\MVC\Symfony\View\Configurator;
use Ibexa\Core\MVC\Symfony\View\ParametersInjector;

/**
 * Builds ContentEditView objects.
 *
 * @internal
 */
readonly class ContentTranslateViewBuilder implements ViewBuilder
{
    public function __construct(
        private Repository $repository,
        private Configurator $viewConfigurator,
        private ParametersInjector $viewParametersInjector,
        private ActionDispatcherInterface $contentActionDispatcher,
        private UserLanguagePreferenceProviderInterface $languagePreferenceProvider
    ) {
    }

    public function matches($argument): bool
    {
        return 'Ibexa\Bundle\AdminUi\Controller\ContentEditController::translateAction' === $argument;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentType
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function buildView(array $parameters): ContentTranslateSuccessView|ContentTranslateView
    {
        $view = new ContentTranslateView();

        $fromLanguage = $this->resolveFromLanguage($parameters);
        $toLanguage = $this->resolveToLanguage($parameters);
        $location = $this->resolveLocation($parameters, $fromLanguage);
        $content = $this->resolveContent($parameters, $location, $fromLanguage);
        $contentInfo = $content->getContentInfo();
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $content->getContentType()->id,
            $this->languagePreferenceProvider->getPreferredLanguages()
        );

        /** @var \Symfony\Component\Form\FormInterface<mixed> $form */
        $form = $parameters['form'];

        if (null === $location && $contentInfo->isPublished()) {
            // assume main location if no location was provided
            $location = $this->loadLocation((int) $contentInfo->getMainLocationId());
        }

        $clickedButton = $form->getClickedButton();
        if ($form->isSubmitted() && $form->isValid() && null !== $clickedButton) {
            $this->contentActionDispatcher->dispatchFormAction(
                $form,
                $form->getData(),
                $clickedButton->getName(),
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
     * @param array<int, string> $languages
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function loadContent(int $contentId, array $languages = [], ?int $versionNo = null): Content
    {
        return $this->repository->getContentService()->loadContent($contentId, $languages, $versionNo);
    }

    /**
     * @param array<int, string>|null $languages
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function loadLocation(int $locationId, ?array $languages = null): Location
    {
        return $this->repository->getLocationService()->loadLocation($locationId, $languages);
    }

    /**
     * Loads Language with code $languageCode.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function loadLanguage(string $languageCode): Language
    {
        return $this->repository->getContentLanguageService()->loadLanguage($languageCode);
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
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
     * @param array<string, mixed> $parameters
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
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
     * @param array<string, mixed> $parameters
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function resolveContent(array $parameters, ?Location $location, ?Language $language): Content
    {
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
            null !== $language ? [$language->getLanguageCode()] : []
        );
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function resolveLocation(array $parameters, ?Language $language): ?Location
    {
        if (isset($parameters['location'])) {
            return $parameters['location'];
        }

        if (isset($parameters['locationId'])) {
            return $this->loadLocation(
                (int) $parameters['locationId'],
                null !== $language ? [$language->getLanguageCode()] : null
            );
        }

        return null;
    }
}
