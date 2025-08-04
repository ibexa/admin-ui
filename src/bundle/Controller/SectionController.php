<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Exception;
use Ibexa\AdminUi\Form\Data\Section\SectionContentAssignData;
use Ibexa\AdminUi\Form\Data\Section\SectionCreateData;
use Ibexa\AdminUi\Form\Data\Section\SectionDeleteData;
use Ibexa\AdminUi\Form\Data\Section\SectionsDeleteData;
use Ibexa\AdminUi\Form\Data\Section\SectionUpdateData;
use Ibexa\AdminUi\Form\DataMapper\SectionCreateMapper;
use Ibexa\AdminUi\Form\DataMapper\SectionUpdateMapper;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\UI\Service\PathService;
use Ibexa\Bundle\AdminUi\View\IbexaPagerfantaView;
use Ibexa\Bundle\AdminUi\View\Template\IbexaPagerfantaTemplate;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\NewSectionLimitation;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use Ibexa\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use JMS\TranslationBundle\Annotation\Desc;
use JMS\TranslationBundle\Annotation\Ignore;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SectionController extends Controller
{
    public function __construct(
        private readonly TranslatableNotificationHandlerInterface $notificationHandler,
        private readonly SectionService $sectionService,
        private readonly SearchService $searchService,
        private readonly FormFactory $formFactory,
        private readonly SectionCreateMapper $sectionCreateMapper,
        private readonly SectionUpdateMapper $sectionUpdateMapper,
        private readonly SubmitHandler $submitHandler,
        private readonly LocationService $locationService,
        private readonly PathService $pathService,
        private readonly PermissionResolver $permissionResolver,
        private readonly PermissionCheckerInterface $permissionChecker,
        private readonly ConfigResolverInterface $configResolver
    ) {
    }

    public function performAccessCheck(): void
    {
        parent::performAccessCheck();

        $this->denyAccessUnlessGranted(new Attribute('section', 'view'));
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function listAction(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $pagerfanta = new Pagerfanta(
            new ArrayAdapter(iterator_to_array($this->sectionService->loadSections()))
        );

        $pagerfanta->setMaxPerPage(
            $this->configResolver->getParameter('pagination.section_limit')
        );
        $pagerfanta->setCurrentPage(min($page, $pagerfanta->getNbPages()));

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Section[] $sectionList */
        $sectionList = $pagerfanta->getCurrentPageResults();
        $contentCountBySectionId = [];
        $deletableSections = [];
        $assignableSections = [];

        $deleteSectionsForm = $this->formFactory->deleteSections(
            new SectionsDeleteData($this->getSectionsNumbers($sectionList))
        );

        $assignContentForms = $this->formFactory->assignContentSectionForm(
            new SectionContentAssignData()
        );

        foreach ($sectionList as $section) {
            $contentCountBySectionId[$section->id] = $this->sectionService->countAssignedContents($section);
            $deletableSections[$section->id] = !$this->sectionService->isSectionUsed($section);
            $assignableSections[$section->id] = $this->canUserAssignSectionToSomeContent($section);
        }

        $canEdit = $this->permissionResolver->hasAccess('section', 'edit');
        $canAssign = $this->permissionResolver->hasAccess('section', 'assign');

        // User can add Section only if he has access to edit and view.
        // View Policy must be without any limitation because the user must see newly created Section.
        $canAdd = $this->permissionResolver->hasAccess('section', 'view') === true
            && $this->permissionResolver->hasAccess('section', 'edit') === true;

        return $this->render('@ibexadesign/section/list.html.twig', [
            'can_add' => $canAdd,
            'can_edit' => $canEdit,
            'can_assign' => $canAssign,
            'pager' => $pagerfanta,
            'content_count' => $contentCountBySectionId,
            'deletable' => $deletableSections,
            'assignable' => $assignableSections,
            'form_sections_delete' => $deleteSectionsForm,
            'form_section_content_assign' => $assignContentForms,
        ]);
    }

    public function viewAction(Section $section): Response
    {
        $sectionDeleteForm = $this->formFactory->deleteSection(
            new SectionDeleteData($section)
        )->createView();

        return $this->render('@ibexadesign/section/view.html.twig', [
            'section' => $section,
            'form_section_delete' => $sectionDeleteForm,
            'deletable' => !$this->sectionService->isSectionUsed($section),
            'can_edit' => $this->isGranted(new Attribute('section', 'edit')),
        ]);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function viewSectionContentAction(Section $section, int $page = 1, int $limit = 10): Response
    {
        $sectionContentAssignForm = $this->formFactory->assignContentSectionForm(
            new SectionContentAssignData($section)
        )->createView();

        $query = new Query();
        $query->sortClauses[] = new SortClause\ContentName(Query::SORT_ASC);
        $query->filter = new Query\Criterion\SectionId([
            $section->id,
        ]);

        $pagerfanta = new Pagerfanta(new ContentSearchAdapter($query, $this->searchService));
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        $assignedContent = [];
        foreach ($pagerfanta as $content) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
            $mainLocationId = $content->getContentInfo()->getMainLocationId();
            $assignedContent[] = [
                'id' => $content->getId(),
                'name' => $content->getName(),
                'type' => $content->getContentType()->getName(),
                'path' => $mainLocationId !== null ? $this->pathService->loadPathLocations(
                    $this->locationService->loadLocation($mainLocationId)
                ) : [],
            ];
        }

        $routeGenerator = function ($page) use ($section): string {
            return $this->generateUrl('ibexa.section.view', [
                'sectionId' => $section->id,
                'page' => $page,
            ]);
        };

        $pagination = (new IbexaPagerfantaView(new IbexaPagerfantaTemplate()))->render($pagerfanta, $routeGenerator);

        return $this->render('@ibexadesign/section/assigned_content.html.twig', [
            'section' => $section,
            'form_section_content_assign' => $sectionContentAssignForm,
            'assigned_content' => $assignedContent,
            'pagerfanta' => $pagerfanta,
            'pagination' => $pagination,
            'can_assign' => $this->canUserAssignSectionToSomeContent($section),
        ]);
    }

    public function deleteAction(Request $request, Section $section): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('section', 'edit'));
        $form = $this->formFactory->deleteSection(
            new SectionDeleteData($section)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (SectionDeleteData $data): RedirectResponse {
                $section = $data->getSection();

                $this->sectionService->deleteSection($section);

                $this->notificationHandler->success(
                    /** @Desc("Section '%name%' removed.") */
                    'section.delete.success',
                    ['%name%' => $section->name],
                    'ibexa_section'
                );

                return new RedirectResponse($this->generateUrl('ibexa.section.list'));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.section.list');
    }

    public function bulkDeleteAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('section', 'edit'));
        $form = $this->formFactory->deleteSections(
            new SectionsDeleteData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (SectionsDeleteData $data): void {
                foreach ($data->getSections() as $sectionId => $selected) {
                    $section = $this->sectionService->loadSection($sectionId);
                    $this->sectionService->deleteSection($section);

                    $this->notificationHandler->success(
                        /** @Desc("Section '%name%' removed.") */
                        'section.delete.success',
                        ['%name%' => $section->getName()],
                        'ibexa_section'
                    );
                }
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.section.list');
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function assignContentAction(Request $request, Section $section): Response
    {
        if (!$this->canUserAssignSectionToSomeContent($section)) {
            $exception = $this->createAccessDeniedException();
            $exception->setAttributes('state');
            $exception->setSubject('assign');

            throw $exception;
        }

        $form = $this->formFactory->assignContentSectionForm(
            new SectionContentAssignData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (SectionContentAssignData $data): RedirectResponse {
                $section = $data->getSection();

                $contentInfos = array_column($data->getLocations(), 'contentInfo');

                foreach ($contentInfos as $contentInfo) {
                    $this->sectionService->assignSection($contentInfo, $section);
                }

                $this->notificationHandler->success(
                    /** @Desc("%contentItemsCount% Content items assigned to '%name%'") */
                    'section.assign_content.success',
                    ['%name%' => $section->getName(), '%contentItemsCount%' => \count($contentInfos)],
                    'ibexa_section'
                );

                return new RedirectResponse($this->generateUrl('ibexa.section.view', [
                    'sectionId' => $section->getId(),
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.section.view', [
            'sectionId' => $section->getId(),
        ]);
    }

    public function createAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('section', 'edit'));
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formFactory->createSection(
            new SectionCreateData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $sectionCreateStruct = $this->sectionCreateMapper->reverseMap($data);
                $section = $this->sectionService->createSection($sectionCreateStruct);

                $this->notificationHandler->success(
                    /** @Desc("Section '%name%' created.") */
                    'section.create.success',
                    ['%name%' => $section->getName()],
                    'ibexa_section'
                );

                return new RedirectResponse($this->generateUrl('ibexa.section.view', [
                    'sectionId' => $section->getId(),
                ]));
            } catch (Exception $e) {
                $this->notificationHandler->error(/** @Ignore */
                    $e->getMessage()
                );
            }
        }

        return $this->render('@ibexadesign/section/create.html.twig', [
            'form_section_create' => $form,
        ]);
    }

    public function updateAction(Request $request, Section $section): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('section', 'edit'));
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formFactory->updateSection(
            new SectionUpdateData($section)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $sectionUpdateStruct = $this->sectionUpdateMapper->reverseMap($data);
                $section = $this->sectionService->updateSection($data->getSection(), $sectionUpdateStruct);

                $this->notificationHandler->success(
                    /** @Desc("Section '%name%' updated.") */
                    'section.update.success',
                    ['%name%' => $section->name],
                    'ibexa_section'
                );
            } catch (Exception $e) {
                $this->notificationHandler->error(/** @Ignore */
                    $e->getMessage()
                );
            }
        }

        return $this->render('@ibexadesign/section/update.html.twig', [
            'section' => $section,
            'form_section_update' => $form,
        ]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Section[] $sections
     *
     * @return array<int, mixed>
     */
    private function getSectionsNumbers(array $sections): array
    {
        $sectionsNumbers = array_column($sections, 'id');

        return array_combine($sectionsNumbers, array_fill_keys($sectionsNumbers, false));
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function canUserAssignSectionToSomeContent(Section $section): bool
    {
        $hasAccess = $this->permissionResolver->hasAccess('section', 'assign');

        if (is_bool($hasAccess)) {
            return $hasAccess;
        }

        $restrictedNewSections = $this->permissionChecker->getRestrictions($hasAccess, NewSectionLimitation::class);
        if (!empty($restrictedNewSections)) {
            return in_array($section->getId(), array_map('intval', $restrictedNewSections), true);
        }

        // If a user has other limitation than NewSectionLimitation, then a decision will be taken later, based on selected Content.
        return true;
    }
}
