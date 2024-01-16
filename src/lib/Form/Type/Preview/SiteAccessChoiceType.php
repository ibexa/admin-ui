<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Preview;

use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\SiteAccessChoiceLoader;
use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\SiteAccessPreviewChoiceLoader;
use Ibexa\AdminUi\Siteaccess\SiteAccessNameGeneratorInterface;
use Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SiteAccessChoiceType extends AbstractType
{
    private SiteaccessResolverInterface $siteAccessResolver;

    private SiteAccessNameGeneratorInterface $siteAccessNameGenerator;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        SiteaccessResolverInterface $siteAccessResolver,
        SiteAccessNameGeneratorInterface $siteAccessNameGenerator,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->siteAccessResolver = $siteAccessResolver;
        $this->siteAccessNameGenerator = $siteAccessNameGenerator;
        $this->urlGenerator = $urlGenerator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choice_loader' => function (Options $options): ChoiceLoaderInterface {
                    return ChoiceList::loader(
                        $this,
                        new SiteAccessPreviewChoiceLoader(
                            new SiteAccessChoiceLoader(
                                $this->siteAccessResolver,
                                $this->siteAccessNameGenerator,
                                $options['location']
                            ),
                            $this->urlGenerator,
                            $options['content']->id,
                            $options['languageCode'],
                            $options['versionNo'],
                        ),
                        [
                            $options['location'],
                            $options['content']->id,
                            $options['languageCode'],
                            $options['versionNo'],
                        ]
                    );
                },
            ]);

        $resolver->setRequired([
            'location',
            'content',
            'versionNo',
            'languageCode',
        ]);
        $resolver->setAllowedTypes('location', Location::class);
        $resolver->setAllowedTypes('content', Content::class);
        $resolver->setAllowedTypes('versionNo', 'integer');
        $resolver->setAllowedTypes('languageCode', 'string');
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
