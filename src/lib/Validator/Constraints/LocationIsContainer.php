<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

final class LocationIsContainer extends Constraint implements TranslationContainerInterface
{
    public string $message = 'ezplatform.copy_subtree.is_not_container';

    /**
     * @param array<string, mixed>|null $options
     * @param array<string>|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null
    ) {
        if (is_array($options)) {
            trigger_deprecation(
                'ibexa/admin-ui',
                '6.0',
                'Passing an options array to "%s" is deprecated, use named arguments instead.',
                self::class
            );

            $message ??= $options['message'] ?? null;
            $groups ??= $options['groups'] ?? null;
            $payload ??= $options['payload'] ?? null;
        }

        parent::__construct(null, $groups, $payload);

        $this->message = $message ?? $this->message;
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create('ezplatform.copy_subtree.is_not_container', 'validators')
                ->setDesc('Selected Location cannot contain children.'),
        ];
    }
}
