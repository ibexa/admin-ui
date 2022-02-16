<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator;

use Ibexa\ContentForms\Validator\ValidationErrorsProcessor as BaseValidationErrorProcessor;

/**
 * @internal
 *
 * @deprecated Since eZ Platform 3.0.2 class moved to EzPlatformContentForms Bundle.
 * @see \Ibexa\ContentForms\Validator\ValidationErrorsProcessor.
 */
final class ValidationErrorsProcessor
{
    /** @var \Ibexa\ContentForms\Validator\ValidationErrorsProcessor */
    private $validationErrorsProcessor;

    public function __construct(BaseValidationErrorProcessor $validationErrorsProcessor)
    {
        $this->validationErrorsProcessor = $validationErrorsProcessor;
    }

    /**
     * Builds constraint violations based on given SPI validation errors.
     *
     * @param \Ibexa\Contracts\Core\FieldType\ValidationError[] $validationErrors
     */
    public function processValidationErrors(array $validationErrors): void
    {
        $this->validationErrorsProcessor->processValidationErrors($validationErrors);
    }
}

class_alias(ValidationErrorsProcessor::class, 'EzSystems\EzPlatformAdminUi\Validator\ValidationErrorsProcessor');
