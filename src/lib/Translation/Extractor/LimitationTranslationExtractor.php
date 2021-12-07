<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Translation\Extractor;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\ExtractorInterface;

/**
 * Generates translation strings for limitation types.
 */
class LimitationTranslationExtractor implements ExtractorInterface
{
    public const MESSAGE_DOMAIN = 'ezplatform_content_forms_policies';
    public const MESSAGE_ID_PREFIX = 'policy.limitation.identifier.';

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

    public function extract()
    {
        $catalogue = new MessageCatalogue();

        foreach ($this->getLimitationTypes() as $limitationType) {
            $id = self::MESSAGE_ID_PREFIX . strtolower($limitationType);

            $message = new Message\XliffMessage($id, self::MESSAGE_DOMAIN);
            $message->setNew(false);
            $message->setMeaning($limitationType);
            $message->setDesc($limitationType);
            $message->setLocaleString($limitationType);
            $message->addNote('key: ' . $id);

            $catalogue->add($message);
        }

        return $catalogue;
    }

    /**
     * @param string $limitationIdentifier
     *
     * @return string
     */
    public static function identifierToLabel(string $limitationIdentifier): string
    {
        return self::MESSAGE_ID_PREFIX . strtolower($limitationIdentifier);
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
}

class_alias(LimitationTranslationExtractor::class, 'EzSystems\EzPlatformAdminUi\Translation\Extractor\LimitationTranslationExtractor');
