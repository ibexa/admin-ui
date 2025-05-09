<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Language\LanguageCreateData;
use Ibexa\AdminUi\Form\Data\Language\LanguageDeleteData;
use Ibexa\AdminUi\Form\Data\Language\LanguagesDeleteData;
use Ibexa\AdminUi\Form\Data\Language\LanguageUpdateData;
use Ibexa\AdminUi\Form\DataMapper\LanguageCreateMapper;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Form\Type\Language\LanguageCreateType;
use Ibexa\AdminUi\Form\Type\Language\LanguageUpdateType;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Button;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LanguageController extends Controller
{
    private TranslatableNotificationHandlerInterface $notificationHandler;

    private LanguageService $languageService;

    private LanguageCreateMapper $languageCreateMapper;

    private SubmitHandler $submitHandler;

    private FormFactory $formFactory;

    private ConfigResolverInterface $configResolver;

    public function __construct(
        TranslatableNotificationHandlerInterface $notificationHandler,
        LanguageService $languageService,
        LanguageCreateMapper $languageCreateMapper,
        SubmitHandler $submitHandler,
        FormFactory $formFactory,
        ConfigResolverInterface $configResolver
    ) {
        $this->notificationHandler = $notificationHandler;
        $this->languageService = $languageService;
        $this->languageCreateMapper = $languageCreateMapper;
        $this->submitHandler = $submitHandler;
        $this->formFactory = $formFactory;
        $this->configResolver = $configResolver;
    }

    /**
     * Renders the language list.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $pagerfanta = new Pagerfanta(
            new ArrayAdapter(iterator_to_array($this->languageService->loadLanguages()))
        );

        $pagerfanta->setMaxPerPage($this->configResolver->getParameter('pagination.language_limit'));
        $pagerfanta->setCurrentPage(min($page, $pagerfanta->getNbPages()));

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language[] $languageList */
        $languageList = $pagerfanta->getCurrentPageResults();

        $deleteLanguagesForm = $this->formFactory->deleteLanguages(
            new LanguagesDeleteData($this->getLanguagesNumbers($languageList))
        );

        return $this->render('@ibexadesign/language/list.html.twig', [
            'pager' => $pagerfanta,
            'form_languages_delete' => $deleteLanguagesForm,
            'can_administrate' => $this->isGranted(new Attribute('content', 'translations')),
        ]);
    }

    /**
     * Renders the view of a language.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language $language
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Language $language): Response
    {
        $deleteForm = $this->formFactory->deleteLanguage(
            $language,
            new LanguageDeleteData($language)
        );

        return $this->render('@ibexadesign/language/index.html.twig', [
            'language' => $language,
            'deleteForm' => $deleteForm,
            'can_administrate' => $this->isGranted(new Attribute('content', 'translations')),
        ]);
    }

    /**
     * Deletes a language.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language $language
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, Language $language): Response
    {
        $form = $this->formFactory->deleteLanguage(
            $language,
            new LanguageDeleteData($language)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LanguageDeleteData $data): void {
                $language = $data->getLanguage();
                $this->languageService->deleteLanguage($language);

                $this->notificationHandler->success(
                    /** @Desc("Language '%name%' removed.") */
                    'language.delete.success',
                    ['%name%' => $language->name],
                    'ibexa_language'
                );
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.language.list');
    }

    /**
     * Handles removing languages based on submitted form.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \InvalidArgumentException
     */
    public function bulkDeleteAction(Request $request): Response
    {
        $form = $this->formFactory->deleteLanguages(
            new LanguagesDeleteData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LanguagesDeleteData $data): void {
                foreach ($data->getLanguages() as $languageId => $selected) {
                    $language = $this->languageService->loadLanguageById($languageId);
                    $this->languageService->deleteLanguage($language);

                    $this->notificationHandler->success(
                        /** @Desc("Language '%name%' removed.") */
                        'language.delete.success',
                        ['%name%' => $language->name],
                        'ibexa_language'
                    );
                }
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.language.list');
    }

    public function createAction(Request $request): Response
    {
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formFactory->createLanguage();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LanguageCreateData $data) use ($form): Response {
                $languageCreateStruct = $this->languageCreateMapper->reverseMap($data);
                $language = $this->languageService->createLanguage($languageCreateStruct);

                $this->notificationHandler->success(
                    /** @Desc("Language '%name%' created.") */
                    'language.create.success',
                    ['%name%' => $language->name],
                    'ibexa_language'
                );

                if ($form->getClickedButton() instanceof Button
                    && $form->getClickedButton()->getName() === LanguageCreateType::BTN_SAVE
                ) {
                    return $this->redirectToRoute('ibexa.language.edit', [
                        'languageId' => $language->id,
                    ]);
                }

                return new RedirectResponse($this->generateUrl('ibexa.language.view', [
                    'languageId' => $language->id,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/language/create.html.twig', [
            'form' => $form,
            'actionUrl' => $this->generateUrl('ibexa.language.create'),
        ]);
    }

    public function editAction(Request $request, Language $language): Response
    {
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formFactory->updateLanguage(
            new LanguageUpdateData($language)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LanguageUpdateData $data) use ($language, $form): Response {
                $this->languageService->updateLanguageName($language, $data->getName());

                $data->isEnabled()
                    ? $this->languageService->enableLanguage($language)
                    : $this->languageService->disableLanguage($language);

                $this->notificationHandler->success(
                    /** @Desc("Language '%name%' updated.") */
                    'language.update.success',
                    ['%name%' => $language->name],
                    'ibexa_language'
                );

                if ($form->getClickedButton() instanceof Button
                    && $form->getClickedButton()->getName() === LanguageUpdateType::BTN_SAVE
                ) {
                    return $this->redirectToRoute('ibexa.language.edit', [
                        'languageId' => $language->id,
                    ]);
                }

                return new RedirectResponse($this->generateUrl('ibexa.language.view', [
                    'languageId' => $language->id,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/language/edit.html.twig', [
            'form' => $form,
            'actionUrl' => $this->generateUrl('ibexa.language.edit', ['languageId' => $language->id]),
            'language' => $language,
        ]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language[] $languages
     *
     * @return array
     */
    private function getLanguagesNumbers(array $languages): array
    {
        $languagesNumbers = array_column($languages, 'id');

        return array_combine($languagesNumbers, array_fill_keys($languagesNumbers, false));
    }
}
