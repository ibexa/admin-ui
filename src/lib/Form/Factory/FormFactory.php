<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Factory;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Bookmark\BookmarkRemoveData;
use Ibexa\AdminUi\Form\Data\Content\ContentVisibilityUpdateData;
use Ibexa\AdminUi\Form\Data\Content\CustomUrl\CustomUrlAddData;
use Ibexa\AdminUi\Form\Data\Content\CustomUrl\CustomUrlRemoveData;
use Ibexa\AdminUi\Form\Data\Content\Draft\ContentCreateData;
use Ibexa\AdminUi\Form\Data\Content\Draft\ContentEditData;
use Ibexa\AdminUi\Form\Data\Content\Draft\ContentRemoveData;
use Ibexa\AdminUi\Form\Data\Content\Location\ContentLocationAddData;
use Ibexa\AdminUi\Form\Data\Content\Location\ContentLocationRemoveData;
use Ibexa\AdminUi\Form\Data\Content\Location\ContentMainLocationUpdateData;
use Ibexa\AdminUi\Form\Data\Content\Translation\TranslationAddData;
use Ibexa\AdminUi\Form\Data\Content\Translation\TranslationDeleteData;
use Ibexa\AdminUi\Form\Data\ContentType\ContentTypesDeleteData;
use Ibexa\AdminUi\Form\Data\ContentTypeGroup\ContentTypeGroupCreateData;
use Ibexa\AdminUi\Form\Data\ContentTypeGroup\ContentTypeGroupDeleteData;
use Ibexa\AdminUi\Form\Data\ContentTypeGroup\ContentTypeGroupsDeleteData;
use Ibexa\AdminUi\Form\Data\ContentTypeGroup\ContentTypeGroupUpdateData;
use Ibexa\AdminUi\Form\Data\Language\LanguageCreateData;
use Ibexa\AdminUi\Form\Data\Language\LanguageDeleteData;
use Ibexa\AdminUi\Form\Data\Language\LanguagesDeleteData;
use Ibexa\AdminUi\Form\Data\Language\LanguageUpdateData;
use Ibexa\AdminUi\Form\Data\Location\LocationCopyData;
use Ibexa\AdminUi\Form\Data\Location\LocationCopySubtreeData;
use Ibexa\AdminUi\Form\Data\Location\LocationMoveData;
use Ibexa\AdminUi\Form\Data\Location\LocationSwapData;
use Ibexa\AdminUi\Form\Data\Location\LocationTrashData;
use Ibexa\AdminUi\Form\Data\Location\LocationUpdateData;
use Ibexa\AdminUi\Form\Data\Location\LocationUpdateVisibilityData;
use Ibexa\AdminUi\Form\Data\Notification\NotificationSelectionData;
use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateGroupCreateData;
use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateGroupDeleteData;
use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateGroupsDeleteData;
use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateGroupUpdateData;
use Ibexa\AdminUi\Form\Data\Policy\PoliciesDeleteData;
use Ibexa\AdminUi\Form\Data\Policy\PolicyCreateData;
use Ibexa\AdminUi\Form\Data\Policy\PolicyDeleteData;
use Ibexa\AdminUi\Form\Data\Policy\PolicyUpdateData;
use Ibexa\AdminUi\Form\Data\Role\RoleAssignmentCreateData;
use Ibexa\AdminUi\Form\Data\Role\RoleAssignmentDeleteData;
use Ibexa\AdminUi\Form\Data\Role\RoleAssignmentsDeleteData;
use Ibexa\AdminUi\Form\Data\Role\RoleCreateData;
use Ibexa\AdminUi\Form\Data\Role\RoleDeleteData;
use Ibexa\AdminUi\Form\Data\Role\RolesDeleteData;
use Ibexa\AdminUi\Form\Data\Role\RoleUpdateData;
use Ibexa\AdminUi\Form\Data\Section\SectionContentAssignData;
use Ibexa\AdminUi\Form\Data\Section\SectionCreateData;
use Ibexa\AdminUi\Form\Data\Section\SectionDeleteData;
use Ibexa\AdminUi\Form\Data\Section\SectionsDeleteData;
use Ibexa\AdminUi\Form\Data\Section\SectionUpdateData;
use Ibexa\AdminUi\Form\Data\URL\URLListData;
use Ibexa\AdminUi\Form\Data\URL\URLUpdateData;
use Ibexa\AdminUi\Form\Data\URLWildcard\URLWildcardData;
use Ibexa\AdminUi\Form\Data\URLWildcard\URLWildcardDeleteData;
use Ibexa\AdminUi\Form\Data\URLWildcard\URLWildcardUpdateData;
use Ibexa\AdminUi\Form\Data\User\UserDeleteData;
use Ibexa\AdminUi\Form\Data\User\UserEditData;
use Ibexa\AdminUi\Form\Data\Version\VersionRemoveData;
use Ibexa\AdminUi\Form\Type\Bookmark\BookmarkRemoveType;
use Ibexa\AdminUi\Form\Type\Content\ContentVisibilityUpdateType;
use Ibexa\AdminUi\Form\Type\Content\CustomUrl\CustomUrlAddType;
use Ibexa\AdminUi\Form\Type\Content\CustomUrl\CustomUrlRemoveType;
use Ibexa\AdminUi\Form\Type\Content\Draft\ContentCreateType;
use Ibexa\AdminUi\Form\Type\Content\Draft\ContentEditType;
use Ibexa\AdminUi\Form\Type\Content\Draft\ContentRemoveType;
use Ibexa\AdminUi\Form\Type\Content\Location\ContentLocationAddType;
use Ibexa\AdminUi\Form\Type\Content\Location\ContentLocationRemoveType;
use Ibexa\AdminUi\Form\Type\Content\Location\ContentMainLocationUpdateType;
use Ibexa\AdminUi\Form\Type\Content\Translation\TranslationAddType;
use Ibexa\AdminUi\Form\Type\Content\Translation\TranslationDeleteType;
use Ibexa\AdminUi\Form\Type\ContentType\ContentTypesDeleteType;
use Ibexa\AdminUi\Form\Type\ContentTypeGroup\ContentTypeGroupCreateType;
use Ibexa\AdminUi\Form\Type\ContentTypeGroup\ContentTypeGroupDeleteType;
use Ibexa\AdminUi\Form\Type\ContentTypeGroup\ContentTypeGroupsDeleteType;
use Ibexa\AdminUi\Form\Type\ContentTypeGroup\ContentTypeGroupUpdateType;
use Ibexa\AdminUi\Form\Type\Language\LanguageCreateType;
use Ibexa\AdminUi\Form\Type\Language\LanguageDeleteType;
use Ibexa\AdminUi\Form\Type\Language\LanguagesDeleteType;
use Ibexa\AdminUi\Form\Type\Language\LanguageUpdateType;
use Ibexa\AdminUi\Form\Type\Location\LocationCopySubtreeType;
use Ibexa\AdminUi\Form\Type\Location\LocationCopyType;
use Ibexa\AdminUi\Form\Type\Location\LocationMoveType;
use Ibexa\AdminUi\Form\Type\Location\LocationSwapType;
use Ibexa\AdminUi\Form\Type\Location\LocationTrashType;
use Ibexa\AdminUi\Form\Type\Location\LocationUpdateType;
use Ibexa\AdminUi\Form\Type\Location\LocationUpdateVisibilityType;
use Ibexa\AdminUi\Form\Type\Notification\NotificationSelectionType;
use Ibexa\AdminUi\Form\Type\ObjectState\ObjectStateGroupCreateType;
use Ibexa\AdminUi\Form\Type\ObjectState\ObjectStateGroupDeleteType;
use Ibexa\AdminUi\Form\Type\ObjectState\ObjectStateGroupsDeleteType;
use Ibexa\AdminUi\Form\Type\ObjectState\ObjectStateGroupUpdateType;
use Ibexa\AdminUi\Form\Type\Policy\PoliciesDeleteType;
use Ibexa\AdminUi\Form\Type\Policy\PolicyCreateType;
use Ibexa\AdminUi\Form\Type\Policy\PolicyCreateWithLimitationType;
use Ibexa\AdminUi\Form\Type\Policy\PolicyDeleteType;
use Ibexa\AdminUi\Form\Type\Policy\PolicyUpdateType;
use Ibexa\AdminUi\Form\Type\Role\RoleAssignmentCreateType;
use Ibexa\AdminUi\Form\Type\Role\RoleAssignmentDeleteType;
use Ibexa\AdminUi\Form\Type\Role\RoleAssignmentsDeleteType;
use Ibexa\AdminUi\Form\Type\Role\RoleCreateType;
use Ibexa\AdminUi\Form\Type\Role\RoleDeleteType;
use Ibexa\AdminUi\Form\Type\Role\RolesDeleteType;
use Ibexa\AdminUi\Form\Type\Role\RoleUpdateType;
use Ibexa\AdminUi\Form\Type\Search\SearchType;
use Ibexa\AdminUi\Form\Type\Section\SectionContentAssignType;
use Ibexa\AdminUi\Form\Type\Section\SectionCreateType;
use Ibexa\AdminUi\Form\Type\Section\SectionDeleteType;
use Ibexa\AdminUi\Form\Type\Section\SectionsDeleteType;
use Ibexa\AdminUi\Form\Type\Section\SectionUpdateType;
use Ibexa\AdminUi\Form\Type\URL\URLEditType;
use Ibexa\AdminUi\Form\Type\URL\URLListType;
use Ibexa\AdminUi\Form\Type\URLWildcard\URLWildcardDeleteType;
use Ibexa\AdminUi\Form\Type\URLWildcard\URLWildcardType;
use Ibexa\AdminUi\Form\Type\URLWildcard\URLWildcardUpdateType;
use Ibexa\AdminUi\Form\Type\User\UserDeleteType;
use Ibexa\AdminUi\Form\Type\User\UserEditType;
use Ibexa\AdminUi\Form\Type\Version\VersionRemoveType;
use Ibexa\Bundle\Search\Form\Data\SearchData;
use function is_string;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FormFactory
{
    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $formFactory;

    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    protected $urlGenerator;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator
    ) {
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    /**
     * @param array $options
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function contentEdit(
        ?ContentEditData $data = null,
        ?string $name = null,
        array $options = []
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        $data = $data ?? new ContentEditData();

        if (empty($options['language_codes']) && null !== $data->getVersionInfo()) {
            $options['language_codes'] = $data->getVersionInfo()->languageCodes;
        }

        return $this->formFactory->createNamed(
            $name,
            ContentEditType::class,
            $data,
            $options
        );
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function createContent(
        ?ContentCreateData $data = null,
        ?string $name = null
    ): FormInterface {
        $data = $data ?? new ContentCreateData();
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, ContentCreateType::class, $data);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function deleteContentTypes(
        ?ContentTypesDeleteData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, ContentTypesDeleteType::class, $data);
    }

    public function createContentTypeGroup(
        ?ContentTypeGroupCreateData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed(
            $name,
            ContentTypeGroupCreateType::class,
            $data ?? new ContentTypeGroupCreateData()
        );
    }

    public function updateContentTypeGroup(
        ?ContentTypeGroupUpdateData $data = null,
        ?string $name = null
    ): FormInterface {
        if ($name === null && $data === null) {
            throw new \InvalidArgumentException('Either $name or $data must be provided.');
        }
        $name = $name ?: sprintf('update-content-type-group-%d', $data->getContentTypeGroup()->id);

        return $this->formFactory->createNamed($name, ContentTypeGroupUpdateType::class, $data);
    }

    public function deleteContentTypeGroup(
        ?ContentTypeGroupDeleteData $data = null,
        ?string $name = null
    ): FormInterface {
        if ($name === null && $data === null) {
            throw new \InvalidArgumentException('Either $name or $data must be provided.');
        }
        $name = $name ?: sprintf('delete-content-type-group-%d', $data->getContentTypeGroup()->id);

        return $this->formFactory->createNamed($name, ContentTypeGroupDeleteType::class, $data);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function deleteContentTypeGroups(
        ?ContentTypeGroupsDeleteData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, ContentTypeGroupsDeleteType::class, $data);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function addTranslation(
        ?TranslationAddData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: sprintf('add-translation');

        return $this->formFactory->createNamed($name, TranslationAddType::class, $data ?? new TranslationAddData());
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function deleteTranslation(
        ?TranslationDeleteData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: sprintf('delete-translations');

        return $this->formFactory->createNamed($name, TranslationDeleteType::class, $data);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function removeVersion(
        ?VersionRemoveData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, VersionRemoveType::class, $data);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function addLocation(
        ?ContentLocationAddData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, ContentLocationAddType::class, $data);
    }

    public function removeLocation(
        ?ContentLocationRemoveData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, ContentLocationRemoveType::class, $data);
    }

    public function swapLocation(
        ?LocationSwapData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, LocationSwapType::class, $data);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function updateContentMainLocation(
        ?ContentMainLocationUpdateData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        $data = $data ?? new ContentMainLocationUpdateData();

        return $this->formFactory->createNamed(
            $name,
            ContentMainLocationUpdateType::class,
            $data
        );
    }

    public function trashLocation(
        ?LocationTrashData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        $data = $data ?? new LocationTrashData();

        return $this->formFactory->createNamed($name, LocationTrashType::class, $data);
    }

    public function moveLocation(
        ?LocationMoveData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, LocationMoveType::class, $data);
    }

    public function copyLocation(
        ?LocationCopyData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, LocationCopyType::class, $data);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function updateVisibilityLocation(
        ?LocationUpdateVisibilityData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, LocationUpdateVisibilityType::class, $data);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function updateVisibilityContent(
        ?ContentVisibilityUpdateData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        $data = $data ?? new ContentVisibilityUpdateData();

        return $this->formFactory->createNamed($name, ContentVisibilityUpdateType::class, $data);
    }

    public function updateLocation(
        ?LocationUpdateData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, LocationUpdateType::class, $data);
    }

    public function assignContentSectionForm(
        ?SectionContentAssignData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, SectionContentAssignType::class, $data);
    }

    public function deleteSection(
        ?SectionDeleteData $data = null,
        ?string $name = null
    ): FormInterface {
        if ($name !== null) {
            return $this->formFactory->createNamed($name, SectionDeleteType::class, $data);
        }

        if ($data === null || $data->getSection() === null) {
            throw new \InvalidArgumentException(
                'SectionDeleteData with Section must be provided when $name is not set.'
            );
        }

        $name = sprintf('delete-section-%d', $data->getSection()->id);

        return $this->formFactory->createNamed($name, SectionDeleteType::class, $data);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function deleteSections(
        ?SectionsDeleteData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, SectionsDeleteType::class, $data);
    }

    public function createSection(
        ?SectionCreateData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed(
            $name,
            SectionCreateType::class,
            $data ?? new SectionCreateData()
        );
    }

    public function updateSection(
        ?SectionUpdateData $data = null,
        ?string $name = null
    ): FormInterface {
        if ($name !== null) {
            return $this->formFactory->createNamed($name, SectionUpdateType::class, $data);
        }

        if ($data === null || $data->getSection() === null) {
            throw new \InvalidArgumentException(
                'SectionUpdateData with Section must be provided when $name is not set.'
            );
        }

        $name = sprintf('update-section-%d', $data->getSection()->id);

        return $this->formFactory->createNamed($name, SectionUpdateType::class, $data);
    }

    public function createLanguage(
        ?LanguageCreateData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed(
            $name,
            LanguageCreateType::class,
            $data ?? new LanguageCreateData()
        );
    }

    public function updateLanguage(
        LanguageUpdateData $data,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: sprintf('update-language-%d', $data->getLanguage()->id);

        return $this->formFactory->createNamed($name, LanguageUpdateType::class, $data);
    }

    public function deleteLanguage(
        LanguageDeleteData $data,
        ?string $name = null
    ): FormInterface {
        if ($name === null) {
            $language = $data->getLanguage();
            if ($language === null) {
                throw new \InvalidArgumentException('Language is not provided in LanguageDeleteData.');
            }
            $name = sprintf('delete-language-%d', $language->id);
        }

        return $this->formFactory->createNamed($name, LanguageDeleteType::class, $data);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function deleteLanguages(
        ?LanguagesDeleteData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, LanguagesDeleteType::class, $data);
    }

    public function createRole(
        ?RoleCreateData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, RoleCreateType::class, $data);
    }

    public function updateRole(
        RoleUpdateData $data,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: sprintf('update-role-%d', $data->getRole()->id);

        return $this->formFactory->createNamed($name, RoleUpdateType::class, $data);
    }

    public function deleteRole(
        RoleDeleteData $data,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: sprintf('delete-role-%d', $data->getRole()->id);

        return $this->formFactory->createNamed($name, RoleDeleteType::class, $data);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function deleteRoles(
        ?RolesDeleteData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: sprintf('delete-roles');

        return $this->formFactory->createNamed($name, RolesDeleteType::class, $data);
    }

    public function createRoleAssignment(
        ?RoleAssignmentCreateData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed(
            $name,
            RoleAssignmentCreateType::class,
            $data ?? new RoleAssignmentCreateData()
        );
    }

    public function deleteRoleAssignment(
        RoleAssignmentDeleteData $data,
        ?string $name = null
    ): FormInterface {
        $role = $data->getRoleAssignment()->getRole()->id;
        $limitation = !empty($data->getRoleAssignment()->getRoleLimitation())
            ? $data->getRoleAssignment()->getRoleLimitation()->getIdentifier()
            : 'none';

        $name = $name ?: sprintf(
            'delete-role-assignment-%s',
            hash('sha256', implode('/', [$role, $limitation]))
        );

        return $this->formFactory->createNamed($name, RoleAssignmentDeleteType::class, $data);
    }

    public function deleteRoleAssignments(
        ?RoleAssignmentsDeleteData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, RoleAssignmentsDeleteType::class, $data);
    }

    public function createPolicy(
        ?PolicyCreateData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, PolicyCreateType::class, $data);
    }

    public function createPolicyWithLimitation(
        ?PolicyCreateData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, PolicyCreateWithLimitationType::class, $data);
    }

    public function updatePolicy(
        PolicyUpdateData $data,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, PolicyUpdateType::class, $data);
    }

    public function deletePolicy(
        PolicyDeleteData $data,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, PolicyDeleteType::class, $data);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function deletePolicies(
        ?PoliciesDeleteData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, PoliciesDeleteType::class, $data);
    }

    /**
     * @param array $options
     */
    public function createSearchForm(
        ?SearchData $data = null,
        ?string $name = null,
        array $options = []
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, SearchType::class, $data, $options);
    }

    /**
     * @param array $options
     */
    public function createUrlListForm(
        ?URLListData $data = null,
        ?string $name = null,
        array $options = []
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, URLListType::class, $data, $options);
    }

    /**
     * @param array $options
     */
    public function createUrlEditForm(
        ?URLUpdateData $data = null,
        ?string $name = null,
        array $options = []
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, URLEditType::class, $data, $options);
    }

    public function deleteUser(
        ?UserDeleteData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, UserDeleteType::class, $data);
    }

    public function addCustomUrl(
        ?CustomUrlAddData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, CustomUrlAddType::class, $data ?? new CustomUrlAddData());
    }

    public function removeCustomUrl(
        ?CustomUrlRemoveData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, CustomUrlRemoveType::class, $data ?? new CustomUrlRemoveData());
    }

    public function createObjectStateGroup(
        ?ObjectStateGroupCreateData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed(
            $name,
            ObjectStateGroupCreateType::class,
            $data ?? new ObjectStateGroupCreateData()
        );
    }

    public function deleteObjectStateGroup(
        ?ObjectStateGroupDeleteData $data = null,
        ?string $name = null
    ): FormInterface {
        if ($name === null && $data === null) {
            throw new \InvalidArgumentException('Either $name or $data must be provided.');
        }
        $name = $name ?: sprintf('delete-object-state-group-%d', $data->getObjectStateGroup()->id);

        return $this->formFactory->createNamed($name, ObjectStateGroupDeleteType::class, $data);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function deleteObjectStateGroups(
        ?ObjectStateGroupsDeleteData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, ObjectStateGroupsDeleteType::class, $data);
    }

    public function updateObjectStateGroup(
        ?ObjectStateGroupUpdateData $data = null,
        ?string $name = null
    ): FormInterface {
        if ($name === null && $data === null) {
            throw new \InvalidArgumentException('Either $name or $data must be provided.');
        }
        $name = $name ?: sprintf('update-object-state-group-%d', $data->getObjectStateGroup()->id);

        return $this->formFactory->createNamed($name, ObjectStateGroupUpdateType::class, $data);
    }

    public function copyLocationSubtree(
        ?LocationCopySubtreeData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, LocationCopySubtreeType::class, $data);
    }

    public function removeBookmark(
        ?BookmarkRemoveData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, BookmarkRemoveType::class, $data);
    }

    public function editUser(
        ?UserEditData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        $data = $data ?? new UserEditData();
        $options = null !== $data->getVersionInfo()
            ? ['language_codes' => $data->getVersionInfo()->languageCodes]
            : [];

        return $this->formFactory->createNamed($name, UserEditType::class, $data, $options);
    }

    public function removeContentDraft(
        ?ContentRemoveData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed($name, ContentRemoveType::class, $data);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface<\Ibexa\AdminUi\Form\Data\Notification\NotificationSelectionData|null>
     */
    public function deleteNotification(
        NotificationSelectionData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed(
            $name,
            NotificationSelectionType::class,
            $data
        );
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function createURLWildcard(
        ?URLWildcardData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed(
            $name,
            URLWildcardType::class,
            $data ?? new URLWildcardData()
        );
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function createURLWildcardUpdate(
        ?URLWildcardUpdateData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed(
            $name,
            URLWildcardUpdateType::class,
            $data ?? new URLWildcardUpdateData()
        );
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function deleteURLWildcard(
        ?URLWildcardDeleteData $data = null,
        ?string $name = null
    ): FormInterface {
        $name = $name ?: StringUtil::fqcnToBlockPrefix(ContentEditType::class);

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException(
                'name',
                'The form name must be a non-empty string.'
            );
        }

        return $this->formFactory->createNamed(
            $name,
            URLWildcardDeleteType::class,
            $data ?? new URLWildcardDeleteData()
        );
    }
}

class_alias(FormFactory::class, 'EzSystems\EzPlatformAdminUi\Form\Factory\FormFactory');
