<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\UI\Action;

use Ibexa\AdminUi\UI\Action\UiActionEvent;
use Symfony\Component\Form\FormInterface;

interface FormUiActionMapperInterface
{
    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $form
     */
    public function map(FormInterface $form): UiActionEvent;

    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $form
     */
    public function supports(FormInterface $form): bool;
}
