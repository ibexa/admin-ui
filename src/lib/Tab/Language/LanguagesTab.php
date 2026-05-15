<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Language;

use Ibexa\Contracts\AdminUi\Tab\AbstractTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use JMS\TranslationBundle\Annotation\Desc;

class LanguagesTab extends AbstractTab implements OrderedTabInterface
{
    public function getIdentifier(): string
    {
        return 'languages';
    }

    public function getName(): string
    {
        return /** @Desc("Languages") */
            $this->translator->trans('language.list', [], 'ibexa_language');
    }

    public function getOrder(): int
    {
        return 10;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function renderView(array $parameters): string
    {
        return $this->twig->render(
            '@ibexadesign/language/tab/languages.html.twig',
            $parameters
        );
    }
}
