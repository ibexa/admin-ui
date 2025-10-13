<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Symfony\Component\Form\FormInterface;

/**
 * Interface for LimitationType form mappers.
 *
 * It maps a LimitationType's supported values to editing form.
 */
interface LimitationFormMapperInterface
{
    /**
     * "Maps" Limitation form to current LimitationType, in order to display one or several fields
     * representing limitation values supported by the LimitationType.
     *
     * Implementors MUST either:
     * - Add a "limitationValues" form field
     * - OR add field(s) that map to "limitationValues" property from $data.
     *
     * @param \Symfony\Component\Form\FormInterface<mixed> $form form for current Limitation
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Limitation $data underlying data for current Limitation form
     */
    public function mapLimitationForm(FormInterface $form, Limitation $data): void;

    /**
     * Returns the Twig template to use to render the limitation form.
     */
    public function getFormTemplate(): ?string;

    /**
     * This method will be called when FormEvents::SUBMIT is called.
     * It gives the opportunity to filter/manipulate limitation values.
     */
    public function filterLimitationValues(Limitation $limitation): void;
}
