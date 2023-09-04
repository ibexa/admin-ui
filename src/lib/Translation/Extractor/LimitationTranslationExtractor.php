<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Translation\Extractor;

use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\ExtractorInterface;

/**
 * Generates translation strings for limitation types.
 *
 * @deprecated Since ibexa/admin-ui 4.4: The "LimitationTranslationExtractor" class is deprecated, will be removed in 5.0.
 */
class LimitationTranslationExtractor implements ExtractorInterface
{
    public const MESSAGE_DOMAIN = 'ibexa_content_forms_policies';
    public const MESSAGE_ID_PREFIX = LimitationIdentifierToLabelConverter::MESSAGE_ID_PREFIX;

    /**
     * @var array
     */
    private $policyMap;

    /**
     * @param array $policyMap
     */
    public function __construct(array $policyMap)
    {
        $this->policyMap = $policyMap;
    }

    /**
     * @deprecated Since ibexa/admin-ui 4.4: The method "LimitationTranslationExtractor::extract()" method is deprecated, will be removed in 5.0.
     */
    public function extract()
    {
        trigger_deprecation('ibexa/admin', '4.4', 'The %s() method is deprecated, will be removed in 5.0.', __METHOD__);

        $catalogue = new MessageCatalogue();

        foreach ($this->getLimitationTypes() as $limitationType) {
            $id = self::MESSAGE_ID_PREFIX . strtolower($limitationType);

            $message = new Message\XliffMessage($id, self::MESSAGE_DOMAIN);
            $message->setNew(false);
            $message->setMeaning($limitationType);
            $message->setDesc($this->getReadableName($limitationType));
            $message->setLocaleString($limitationType);
            $message->addNote('key: ' . $id);

            $catalogue->add($message);
        }

        return $catalogue;
    }

    /**
     * @param string $limitationIdentifier
     *
     * @deprecated Since ibexa/admin-ui 4.4: The method "LimitationTranslationExtractor::identifierToLabel()" method is deprecated, will be removed in 5.0. Use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter::convert() instead.
     *
     * @return string
     */
    public static function identifierToLabel(string $limitationIdentifier): string
    {
        trigger_deprecation('ibexa/admin', '4.4', 'The %s() method is deprecated, will be removed in 5.0. Use %s::convert() instead.', __METHOD__, LimitationIdentifierToLabelConverter::class);

        return LimitationIdentifierToLabelConverter::convert($limitationIdentifier);
    }

    /**
     * Returns all known limitation types.
     *
     * @return array
     */
    private function getLimitationTypes(): array
    {
        $limitationTypes = [];
        foreach ($this->policyMap as $module) {
            foreach ($module as $policy) {
                if (null === $policy) {
                    continue;
                }

                foreach (array_keys($policy) as $limitationType) {
                    if (!in_array($limitationType, $limitationTypes)) {
                        $limitationTypes[] = $limitationType;
                    }
                }
            }
        }

        return $limitationTypes;
    }

    private function getReadableName(string $input): string
    {
        $parts = preg_split(
            '/(^[^A-Z]+|[A-Z][^A-Z]+)/',
            $input,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );

        if (!is_array($parts)) {
            return $input;
        }

        return implode(' ', $parts);
    }
}

class_alias(LimitationTranslationExtractor::class, 'EzSystems\EzPlatformAdminUi\Translation\Extractor\LimitationTranslationExtractor');
