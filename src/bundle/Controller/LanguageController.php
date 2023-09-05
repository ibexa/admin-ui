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
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LanguageController extends Controller
{
    /** @var \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface */
    private $notificationHandler;

    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    private $languageService;

    /** @var \Ibexa\AdminUi\Form\DataMapper\LanguageCreateMapper */
    private $languageCreateMapper;

    /** @var \Ibexa\AdminUi\Form\SubmitHandler */
    private $submitHandler;

    /** @var \Ibexa\AdminUi\Form\Factory\FormFactory */
    private $formFactory;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

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
        $page = $request->query->get('page') ?? 1;

        $pagerfanta = new Pagerfanta(
            new ArrayAdapter($this->languageService->loadLanguages())
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
            'form_languages_delete' => $deleteLanguagesForm->createView(),
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
            new LanguageDeleteData($language)
        );

        return $this->render('@ibexadesign/language/index.html.twig', [
            'language' => $language,
            'deleteForm' => $deleteForm->createView(),
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
            new LanguageDeleteData($language)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LanguageDeleteData $data) {
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

        return $this->redirect($this->generateUrl('ibexa.language.list'));
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
            $result = $this->submitHandler->handle($form, function (LanguagesDeleteData $data) {
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

        return $this->redirect($this->generateUrl('ibexa.language.list'));
    }

    public function createAction(Request $request): Response
    {
        $form = $this->formFactory->createLanguage();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LanguageCreateData $data) {
                $languageCreateStruct = $this->languageCreateMapper->reverseMap($data);
                $language = $this->languageService->createLanguage($languageCreateStruct);

                $this->notificationHandler->success(
                    /** @Desc("Language '%name%' created.") */
                    'language.create.success',
                    ['%name%' => $language->name],
                    'ibexa_language'
                );

                return new RedirectResponse($this->generateUrl('ibexa.language.view', [
                    'languageId' => $language->id,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/language/create.html.twig', [
            'form' => $form->createView(),
            'actionUrl' => $this->generateUrl('ibexa.language.create'),
        ]);
    }

    public function editAction(Request $request, Language $language): Response
    {
        $form = $this->formFactory->updateLanguage(
            new LanguageUpdateData($language)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LanguageUpdateData $data) use ($language) {
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

                return new RedirectResponse($this->generateUrl('ibexa.language.view', [
                    'languageId' => $language->id,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/language/edit.html.twig', [
            'form' => $form->createView(),
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

class_alias(LanguageController::class, 'EzSystems\EzPlatformAdminUiBundle\Controller\LanguageController');
