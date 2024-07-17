<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\REST\Input\Parser;

use Ibexa\Contracts\Rest\Input\Parser\Query\Criterion\BaseCriterionProcessor;

/**
 * @phpstan-type TCriterionProcessor \Ibexa\Contracts\Rest\Input\Parser\Query\Criterion\CriterionProcessorInterface<
 *     \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion
 * >
 *
 * @extends \Ibexa\Contracts\Rest\Input\Parser\Query\Criterion\BaseCriterionProcessor<
 *     \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion
 * >
 *
 * @internal
 */
final class CriterionProcessor extends BaseCriterionProcessor
{
    protected function getMediaTypePrefix(): string
    {
        return 'application/vnd.ibexa.api.internal.criterion.';
    }

    protected function getParserInvalidCriterionMessage(string $criterionName): string
    {
        return "Invalid Criterion <$criterionName>";
    }
}
