<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Exception;
use Ibexa\AdminUi\Form\Data\ContentType\ContentTypeCopyData;
use Ibexa\AdminUi\Form\Data\ContentType\ContentTypeEditData;
use Ibexa\AdminUi\Form\Data\ContentType\ContentTypesDeleteData;
use Ibexa\AdminUi\Form\Data\ContentType\Translation\TranslationAddData;
use Ibexa\AdminUi\Form\Data\ContentType\Translation\TranslationRemoveData;
use Ibexa\AdminUi\Form\Data\FormMapper\ContentTypeDraftMapper;
use Ibexa\AdminUi\Form\Factory\ContentTypeFormFactory;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Form\Type\ContentType\ContentTypeUpdateType;
use Ibexa\AdminUi\Service\MetaFieldType\MetaFieldDefinitionServiceInterface;
use Ibexa\AdminUi\Tab\ContentType\TranslationsTab;
use Ibexa\AdminUi\UI\Module\FieldTypeToolbar\FieldTypeToolbarFactory;
use Ibexa\AdminUi\View\ContentTypeCreateView;
use Ibexa\AdminUi\View\ContentTypeEditView;
use Ibexa\ContentForms\Form\ActionDispatcher\ActionDispatcherInterface;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContentTypeController extends Controller
{
    private const string PRIMARY_UPDATE_ACTION = 'publishContentType';

    public function __construct(
        private readonly TranslatableNotificationHandlerInterface $notificationHandler,
        private readonly TranslatorInterface $translator,
        private readonly ContentTypeService $contentTypeService,
        private readonly ActionDispatcherInterface $contentTypeActionDispatcher,
        private readonly FormFactory $formFactory,
        private readonly SubmitHandler $submitHandler,
        private readonly UserService $userService,
        private readonly LanguageService $languageService,
        private readonly ContentTypeFormFactory $contentTypeFormFactory,
        private readonly ContentTypeDraftMapper $contentTypeDraftMapper,
        private readonly ConfigResolverInterface $configResolver,
        private readonly FieldTypeToolbarFactory $fieldTypeToolbarFactory,
        private readonly MetaFieldDefinitionServiceInterface $metaFieldDefinitionService
    ) {
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Pagerfanta\Exception\OutOfRangeCurrentPageException
     * @throws \Pagerfanta\Exception\LessThan1CurrentPageException
     * @throws \Pagerfanta\Exception\LessThan1MaxPerPageException
     */
    public function listAction(ContentTypeGroup $group, string $routeName, int $page): Response
    {
        $deletableTypes = [];
        $contentTypes = iterator_to_array(
            $this->contentTypeService->loadContentTypes(
                $group,
                $this->configResolver->getParameter('languages')
            )
        );

        usort($contentTypes, static function (ContentType $contentType1, ContentType $contentType2): int {
            return strnatcasecmp($contentType1->getName() ?? '', $contentType2->getName() ?? '');
        });

        $pagerfanta = new Pagerfanta(
            new ArrayAdapter($contentTypes)
        );

        $pagerfanta->setMaxPerPage(
            $this->configResolver->getParameter('pagination.content_type_limit')
        );
        $pagerfanta->setCurrentPage(min($page, $pagerfanta->getNbPages()));

        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup[] $contentTypeGroupList */
        $types = iterator_to_array($pagerfanta->getCurrentPageResults());

        $deleteContentTypesForm = $this->formFactory->deleteContentTypes(
            new ContentTypesDeleteData($this->getContentTypesNumbers($types))
        );

        foreach ($types as $type) {
            $deletableTypes[$type->id] = !$this->contentTypeService->isContentTypeUsed($type);
        }

        $copyData = new ContentTypeCopyData(null, $group);
        $contentTypeCopyForm = $this->contentTypeFormFactory->contentTypeCopy($copyData)->createView();

        return $this->render('@ibexadesign/content_type/list.html.twig', [
            'content_type_group' => $group,
            'pager' => $pagerfanta,
            'deletable' => $deletableTypes,
            'form_content_types_delete' => $deleteContentTypesForm,
            'group' => $group,
            'route_name' => $routeName,
            'can_create' => $this->isGranted(new Attribute('class', 'create')),
            'can_update' => $this->isGranted(new Attribute('class', 'update')),
            'can_delete' => $this->isGranted(new Attribute('class', 'delete')),
            'content_type_copy_form' => $contentTypeCopyForm,
        ]);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function addAction(ContentTypeGroup $group): RedirectResponse|ContentTypeCreateView
    {
        $this->denyAccessUnlessGranted(new Attribute('class', 'create'));
        $mainLanguageCode = $this->languageService->getDefaultLanguageCode();

        $createStruct = $this->contentTypeService->newContentTypeCreateStruct(
            '__new__' . md5((string)microtime(true))
        );
        $createStruct->mainLanguageCode = $mainLanguageCode;
        $createStruct->names = [$mainLanguageCode => 'New content type'];

        $this->metaFieldDefinitionService->addMetaFieldDefinitions(
            $createStruct,
            $this->languageService->loadLanguage($mainLanguageCode)
        );

        try {
            $contentTypeDraft = $this->contentTypeService->createContentType($createStruct, [$group]);
        } catch (Exception) {
            $this->notificationHandler->error(
                /** @Desc("Cannot create content type. Could not find language with identifier '%languageCode%'") */
                'content_type.add.missing_language',
                ['%languageCode%' => $mainLanguageCode],
                'ibexa_content_type'
            );

            return $this->redirectToRoute('ibexa.content_type_group.view', [
                'contentTypeGroupId' => $group->id,
            ]);
        }
        $language = $this->languageService->loadLanguage($mainLanguageCode);
        $form = $this->createUpdateForm($group, $contentTypeDraft, $language);

        $view = new ContentTypeCreateView('@ibexadesign/content_type/create.html.twig', $group, $contentTypeDraft, $form);
        $view->setParameters([
            'field_type_toolbar' => $this->fieldTypeToolbarFactory->create(),
        ]);

        return $view;
    }

    public function addTranslationAction(Request $request): Response
    {
        $form = $this->contentTypeFormFactory->addContentTypeTranslation(
            new TranslationAddData()
        );
        $form->handleRequest($request);

        /** @var \Ibexa\AdminUi\Form\Data\ContentType\Translation\TranslationAddData $data */
        $data = $form->getData();
        $contentType = $data->getContentType();
        $contentTypeGroup = $data->getContentTypeGroup();

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (TranslationAddData $data): RedirectResponse {
                $contentType = $data->getContentType();
                $language = $data->getLanguage();
                $baseLanguage = $data->getBaseLanguage();
                $contentTypeGroup = $data->getContentTypeGroup();

                try {
                    $contentTypeDraft = $this->tryToCreateContentTypeDraft($contentType);
                } catch (BadStateException) {
                    $userId = $contentType->modifierId;
                    $this->notificationHandler->error(
                        /** @Desc("Draft of content type '%name%' already exists and is locked by '%userContentName%'") */
                        'content_type.edit.error.already_exists',
                        ['%name%' => $contentType->getName(), '%userContentName%' => $this->getUserNameById($userId)],
                        'ibexa_content_type'
                    );

                    return $this->redirectToRoute('ibexa.content_type.view', [
                        'contentTypeGroupId' => $contentTypeGroup->id,
                        'contentTypeId' => $contentType->id,
                    ]);
                }

                return new RedirectResponse($this->generateUrl('ibexa.content_type.update', [
                    'contentTypeId' => $contentTypeDraft->id,
                    'contentTypeGroupId' => $contentTypeGroup->id,
                    'fromLanguageCode' => $baseLanguage?->getLanguageCode(),
                    'toLanguageCode' => $language->getLanguageCode(),
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content_type.view', [
            'contentTypeGroupId' => $contentTypeGroup->id,
            'contentTypeId' => $contentType->id,
        ]);
    }

    public function removeTranslationAction(Request $request): Response
    {
        $form = $this->contentTypeFormFactory->removeContentTypeTranslation(
            new TranslationRemoveData()
        );
        $form->handleRequest($request);

        /** @var \Ibexa\AdminUi\Form\Data\ContentType\Translation\TranslationRemoveData $data */
        $data = $form->getData();
        $contentType = $data->getContentType();
        $contentTypeGroup = $data->getContentTypeGroup();

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (TranslationRemoveData $data): RedirectResponse {
                $contentType = $data->getContentType();
                $languageCodes = $data->getLanguageCodes();
                $contentTypeGroup = $data->getContentTypeGroup();
                try {
                    $contentTypeDraft = $this->tryToCreateContentTypeDraft($contentType);
                } catch (BadStateException) {
                    $userId = $contentType->modifierId;
                    $this->notificationHandler->error(
                        /** @Desc("Draft of content type '%name%' already exists and is locked by '%userContentName%'") */
                        'content_type.edit.error.already_exists',
                        ['%name%' => $contentType->getName(), '%userContentName%' => $this->getUserNameById($userId)],
                        'ibexa_content_type'
                    );

                    return $this->redirectToRoute('ibexa.content_type.view', [
                        'contentTypeGroupId' => $contentTypeGroup->id,
                        'contentTypeId' => $contentType->id,
                    ]);
                }
                foreach ($languageCodes as $languageCode => $isChecked) {
                    $newContentTypeDraft = $this->contentTypeService->removeContentTypeTranslation(
                        $contentTypeDraft,
                        $languageCode
                    );
                }
                $this->contentTypeService->publishContentTypeDraft($newContentTypeDraft);

                return new RedirectResponse($this->generateUrl('ibexa.content_type.view', [
                    'contentTypeId' => $contentType->id,
                    'contentTypeGroupId' => $contentTypeGroup->id,
                    '_fragment' => TranslationsTab::URI_FRAGMENT,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content_type.view', [
            'contentTypeGroupId' => $contentTypeGroup->id,
            'contentTypeId' => $contentType->id,
        ]);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     */
    public function editAction(Request $request, ContentTypeGroup $group, ContentType $contentType): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('class', 'update'));
        try {
            $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentType->id, true);
        } catch (NotFoundException) {
            $contentTypeDraft = $this->contentTypeService->createContentTypeDraft($contentType);
        }

        if ($contentTypeDraft->modifierId !== $this->getUser()->getAPIUser()->getUserId()) {
            $this->notificationHandler->error(
                $this->translator->trans(
                    /** @Desc("Draft of content type '%name%' already exists and is locked by '%userContentName%'") */
                    'content_type.edit.error.already_exists',
                    ['%name%' => $contentType->getName(), '%userContentName%' => $this->getUserNameById($contentTypeDraft->modifierId)],
                    'ibexa_content_type'
                )
            );

            return $this->redirectToRoute('ibexa.content_type.view', [
                'contentTypeGroupId' => $group->id,
                'contentTypeId' => $contentType->id,
            ]);
        }

        $form = $this->contentTypeFormFactory->contentTypeEdit(
            new ContentTypeEditData(),
            null,
            ['contentType' => $contentType]
        );

        $this->metaFieldDefinitionService->addMetaFieldDefinitions($contentTypeDraft);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentTypeEditData $data) use ($contentTypeDraft): RedirectResponse {
                $contentTypeGroup = $data->getContentTypeGroup();
                $language = $data->getLanguage();

                return $this->redirectToRoute('ibexa.content_type.update', [
                    'contentTypeId' => $contentTypeDraft->id,
                    'contentTypeGroupId' => $contentTypeGroup->id,
                    'toLanguageCode' => $language->languageCode,
                ]);
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content_type.update', [
            'contentTypeId' => $contentTypeDraft->id,
            'contentTypeGroupId' => $group->id,
        ]);
    }

    public function copyAction(Request $request, ContentTypeGroup $group, ContentType $contentType): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('class', 'create'));

        $contentTypeService = $this->contentTypeService;
        $notificationHandler = $this->notificationHandler;

        $copyData = new ContentTypeCopyData($contentType, $group);

        $form = $this->contentTypeFormFactory->contentTypeCopy($copyData);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle(
                $form,
                function (ContentTypeCopyData $data) use ($contentTypeService, $notificationHandler): RedirectResponse {
                    $contentType = $data->getContentType();

                    try {
                        $contentTypeService->copyContentType($contentType);

                        $notificationHandler->success(
                            /** @Desc("Content type '%name%' copied.") */
                            'content_type.copy.success',
                            ['%name%' => $contentType->getName()],
                            'ibexa_content_type'
                        );
                    } catch (UnauthorizedException) {
                        $notificationHandler->error(
                            /** @Desc("Content type '%name%' cannot be copied.") */
                            'content_type.copy.error',
                            ['%name%' => $contentType->getName()],
                            'ibexa_content_type'
                        );
                    }

                    return $this->redirectToRoute('ibexa.content_type_group.view', [
                        'contentTypeGroupId' => $data->getContentTypeGroup()->id,
                    ]);
                }
            );

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content_type_group.view', [
            'contentTypeGroupId' => $group->id,
        ]);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function updateAction(
        Request $request,
        ContentTypeGroup $group,
        ContentTypeDraft $contentTypeDraft,
        ?Language $language = null,
        ?Language $baseLanguage = null
    ): Response|ContentTypeEditView {
        if (!$language) {
            $language = $this->getDefaultLanguage($contentTypeDraft);
        }

        $form = $this->createUpdateForm($group, $contentTypeDraft, $language, $baseLanguage);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function () use (
                $form,
                $group,
                $contentTypeDraft,
                $language,
                $baseLanguage
            ): Response {
                $action = $form->getClickedButton() ? $form->getClickedButton()->getName() : self::PRIMARY_UPDATE_ACTION;
                $this->contentTypeActionDispatcher->dispatchFormAction(
                    $form,
                    $form->getData(),
                    $action,
                    ['languageCode' => $language->languageCode]
                );

                if ($response = $this->contentTypeActionDispatcher->getResponse()) {
                    return $response;
                }

                $this->notificationHandler->success(
                    /** @Desc("Content type '%name%' updated.") */
                    'content_type.update.success',
                    ['%name%' => $contentTypeDraft->getName()],
                    'ibexa_content_type'
                );

                if ($action === self::PRIMARY_UPDATE_ACTION) {
                    return $this->redirectToRoute('ibexa.content_type.view', [
                        'contentTypeGroupId' => $group->id,
                        'contentTypeId' => $contentTypeDraft->id,
                        'languageCode' => $language->getLanguageCode(),
                    ]);
                }

                return $this->redirectToRoute('ibexa.content_type.update', [
                    'contentTypeGroupId' => $group->id,
                    'contentTypeId' => $contentTypeDraft->id,
                    'toLanguageCode' => $language->languageCode,
                    'fromLanguageCode' => $baseLanguage ? $baseLanguage->getLanguageCode() : null,
                ]);
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        $view = new ContentTypeEditView(
            '@ibexadesign/content_type/edit.html.twig',
            $group,
            $contentTypeDraft,
            $baseLanguage ?? $language,
            $form
        );
        $view->addParameters([
            'field_type_toolbar' => $this->fieldTypeToolbarFactory->create(),
        ]);

        return $view;
    }

    public function addFieldDefinitionFormAction(
        ContentTypeGroup $group,
        ContentTypeDraft $contentTypeDraft,
        string $fieldDefinitionIdentifier,
        ?Language $language = null,
        ?Language $baseLanguage = null
    ): Response {
        $this->denyAccessUnlessGranted(new Attribute('class', 'update'));

        if ($language === null) {
            $language = $this->getDefaultLanguage($contentTypeDraft);
        }

        $contentTypeDraftData = $this->contentTypeDraftMapper->mapToFormData(
            $contentTypeDraft,
            [
                'language' => $language,
                'baseLanguage' => $baseLanguage,
            ]
        );

        $form = $this->createUpdateForm($group, $contentTypeDraft, $language, $baseLanguage);

        foreach ($form['fieldDefinitionsData'] as $fieldDefinitionsGroupForm) {
            if (!isset($fieldDefinitionsGroupForm[$fieldDefinitionIdentifier])) {
                continue;
            }

            return $this->render('@ibexadesign/content_type/part/field_definition_form.html.twig', [
                'form' => $fieldDefinitionsGroupForm[$fieldDefinitionIdentifier],
                'content_type_group' => $group,
                'content_type' => $contentTypeDraft,
                'language_code' => $baseLanguage ? $baseLanguage->getLanguageCode() : $language->getLanguageCode(),
                'is_translation' => $contentTypeDraftData->mainLanguageCode !== $contentTypeDraftData->languageCode,
            ]);
        }

        throw $this->createNotFoundException("Field definition with identifier $fieldDefinitionIdentifier not found");
    }

    /**
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function deleteAction(Request $request, ContentTypeGroup $group, ContentType $contentType): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('class', 'delete'));
        $form = $this->createDeleteForm($group, $contentType);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function () use ($contentType): void {
                $this->contentTypeService->deleteContentType($contentType);

                $this->notificationHandler->success(
                    /** @Desc("Content type '%name%' deleted.") */
                    'content_type.delete.success',
                    ['%name%' => $contentType->getName()],
                    'ibexa_content_type'
                );
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content_type_group.view', [
            'contentTypeGroupId' => $group->id,
        ]);
    }

    /**
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \InvalidArgumentException
     */
    public function bulkDeleteAction(Request $request, ContentTypeGroup $group): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('class', 'delete'));
        $form = $this->formFactory->deleteContentTypes(
            new ContentTypesDeleteData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentTypesDeleteData $data): void {
                foreach ($data->getContentTypes() as $contentTypeId => $selected) {
                    $contentType = $this->contentTypeService->loadContentType($contentTypeId);

                    $this->contentTypeService->deleteContentType($contentType);

                    $this->notificationHandler->success(
                        /** @Desc("Content type '%name%' deleted.") */
                        'content_type.delete.success',
                        ['%name%' => $contentType->getName()],
                        'ibexa_content_type'
                    );
                }
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute(
            'ibexa.content_type_group.view',
            ['contentTypeGroupId' => $group->id]
        );
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function viewAction(
        ContentTypeGroup $group,
        ContentType $contentType,
        Request $request
    ): Response {
        $contentTypeGroups = $contentType->getContentTypeGroups();
        $contentTypeGroupsIds = array_column($contentTypeGroups, 'id');
        if (!in_array($group->id, $contentTypeGroupsIds, true)) {
            throw $this->createNotFoundException(
                sprintf(
                    '%s content type does not belong to %s content type group.',
                    $contentType->getName(),
                    $group->identifier,
                ),
            );
        }

        $fieldDefinitionsByGroup = [];
        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            $fieldDefinitionsByGroup[$fieldDefinition->getFieldGroup() ?: 'content'][] = $fieldDefinition;
        }
        $languages = [];
        foreach ($contentType->languageCodes as $languageCode) {
            $languages[] = $this->languageService->loadLanguage($languageCode);
        }

        $contentTypeEdit = $this->contentTypeFormFactory->contentTypeEdit(
            new ContentTypeEditData(
                $contentType,
                $group
            ),
            null,
            ['contentType' => $contentType]
        );

        $canUpdate = $this->isGranted(new Attribute('class', 'update')) &&
            $this->isGranted(new Attribute('class', 'create'));

        $view = $request->attributes->get('view_template') ?? '@ibexadesign/content_type/index.html.twig';

        return $this->render($view, [
            'content_type_group' => $group,
            'content_type' => $contentType,
            'field_definitions_by_group' => $fieldDefinitionsByGroup,
            'can_update' => $canUpdate,
            'languages' => $languages,
            'form_content_type_edit' => $contentTypeEdit,
        ]);
    }

    /**
     * @return FormInterface<mixed>
     */
    public function createUpdateForm(
        ContentTypeGroup $contentTypeGroup,
        ContentTypeDraft $contentTypeDraft,
        ?Language $language = null,
        ?Language $baseLanguage = null
    ): FormInterface {
        $this->metaFieldDefinitionService->addMetaFieldDefinitions($contentTypeDraft, $language);
        $contentTypeData = $this->contentTypeDraftMapper->mapToFormData(
            $contentTypeDraft,
            [
                'language' => $language,
                'baseLanguage' => $baseLanguage,
            ]
        );

        return $this->createForm(ContentTypeUpdateType::class, $contentTypeData, [
            'method' => Request::METHOD_POST,
            'action' => $this->generateUrl('ibexa.content_type.update', [
                'contentTypeGroupId' => $contentTypeGroup->id,
                'contentTypeId' => $contentTypeDraft->id,
                'fromLanguageCode' => $baseLanguage?->getLanguageCode(),
                'toLanguageCode' => $language->getLanguageCode(),
            ]),
            'languageCode' => $language->getLanguageCode(),
            'mainLanguageCode' => $contentTypeDraft->mainLanguageCode,
        ]);
    }

    /**
     * @return FormInterface<mixed>
     */
    protected function createDeleteForm(ContentTypeGroup $group, ContentType $contentType): FormInterface
    {
        $formBuilder = $this->createFormBuilder(null, [
            'method' => Request::METHOD_DELETE,
            'action' => $this->generateUrl('ibexa.content_type.delete', [
                'contentTypeGroupId' => $group->id,
                'contentTypeId' => $contentType->id,
            ]),
        ]);

        return $formBuilder->getForm();
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType[] $contentTypes
     *
     * @return array<int, false>
     */
    private function getContentTypesNumbers(array $contentTypes): array
    {
        $contentTypesNumbers = array_column($contentTypes, 'id');

        return array_combine($contentTypesNumbers, array_fill_keys($contentTypesNumbers, false));
    }

    /**
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    private function getUserNameById(int $userId): ?string
    {
        try {
            $user = $this->userService->loadUser($userId);

            return $user->getName();
        } catch (Exception) {
            return $this->translator->trans(
                /** @Desc("another user") */
                'content_type.user_name.can_not_be_fetched',
                [],
                'ibexa_content_type'
            );
        }
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function getDefaultLanguage(ContentTypeDraft $contentTypeDraft): Language
    {
        $languages = $this->configResolver->getParameter('languages');
        $languageCode = reset($languages);

        foreach ($languages as $prioritizedLanguage) {
            if (isset($contentTypeDraft->names[$prioritizedLanguage])) {
                $languageCode = $prioritizedLanguage;
                break;
            }
        }

        return $this->languageService->loadLanguage($languageCode);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function tryToCreateContentTypeDraft(ContentType $contentType): ContentTypeDraft
    {
        try {
            $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentType->id);
            $this->contentTypeService->deleteContentType($contentTypeDraft);
        } catch (NotFoundException) {
            //do nothing
        } finally {
            return $this->contentTypeService->createContentTypeDraft($contentType);
        }
    }
}
