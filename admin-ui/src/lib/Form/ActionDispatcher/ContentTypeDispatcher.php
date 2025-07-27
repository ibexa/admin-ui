<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\ActionDispatcher;

use Ibexa\ContentForms\Form\ActionDispatcher\AbstractActionDispatcher;
use Ibexa\Contracts\AdminUi\Event\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypeDispatcher extends AbstractActionDispatcher
{
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('languageCode');
    }

    protected function getActionEventBaseName(): string
    {
        return FormEvents::CONTENT_TYPE_UPDATE;
    }
}
