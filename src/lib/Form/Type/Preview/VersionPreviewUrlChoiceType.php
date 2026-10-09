<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Preview;

use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\SiteAccessChoiceLoader;
use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\VersionPreviewUrlChoiceLoader;
use Ibexa\AdminUi\Siteaccess\SiteAccessNameGeneratorInterface;
use Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface;
use Ibexa\Contracts\AdminUi\PreviewUrlResolver\VersionPreviewUrlResolverInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This form type provides a choice field for selecting a site access to preview a specific content version.
 *
 * @phpstan-extends \Symfony\Component\Form\AbstractType<string>
 */
final class VersionPreviewUrlChoiceType extends AbstractType
{
    private SiteAccessServiceInterface $siteAccessService;

    private SiteaccessResolverInterface $siteAccessResolver;

    private SiteAccessNameGeneratorInterface $siteAccessNameGenerator;

    private VersionPreviewUrlResolverInterface $previewUrlResolver;

    public function __construct(
        SiteAccessServiceInterface $siteAccessService,
        SiteaccessResolverInterface $siteAccessResolver,
        SiteAccessNameGeneratorInterface $siteAccessNameGenerator,
        VersionPreviewUrlResolverInterface $previewUrlResolver
    ) {
        $this->siteAccessService = $siteAccessService;
        $this->siteAccessResolver = $siteAccessResolver;
        $this->siteAccessNameGenerator = $siteAccessNameGenerator;
        $this->previewUrlResolver = $previewUrlResolver;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define('version_info')->allowedTypes(VersionInfo::class)->required();
        $resolver->define('location')->allowedTypes(Location::class)->required();
        $resolver->define('language')->allowedTypes(Language::class)->required();

        $resolver->setDefaults([
            'choice_loader' => function (Options $options): ChoiceLoaderInterface {
                /** @var VersionInfo $versionInfo */
                $versionInfo = $options['version_info'];
                /** @var Location $location */
                $location = $options['location'];
                /** @var Language $language */
                $language = $options['language'];

                return ChoiceList::loader(
                    $this,
                    new VersionPreviewUrlChoiceLoader(
                        $this->siteAccessService,
                        $this->previewUrlResolver,
                        new SiteAccessChoiceLoader(
                            $this->siteAccessResolver,
                            $this->siteAccessNameGenerator,
                            $location,
                            $language->getLanguageCode(),
                        ),
                        $versionInfo,
                        $location,
                        $language
                    ),
                    [
                        $versionInfo->getContentInfo()->getId(),
                        $versionInfo->getVersionNo(),
                        $location->getId(),
                        $language->getLanguageCode(),
                    ]
                );
            },
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
