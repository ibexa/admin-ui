<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\UI\Action;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

interface UiActionEventInterface
{
    public const string TYPE_SUCCESS = 'success';
    public const string TYPE_FAILURE = 'failure';

    public function getName(): string;

    public function setName(string $name): void;

    public function getType(): string;

    public function setType(string $type): void;

    public function getForm(): FormInterface;

    public function setForm(FormInterface $form): void;

    public function getResponse(): ?Response;

    public function setResponse(?Response $response): void;
}
