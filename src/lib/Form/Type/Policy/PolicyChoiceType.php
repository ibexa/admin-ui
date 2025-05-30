<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Policy;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class PolicyChoiceType extends AbstractType
{
    public const MESSAGE_DOMAIN = 'forms';
    public const MESSAGE_ID_PREFIX = 'role.policy.';
    public const ALL_MODULES = 'all_modules';
    public const ALL_FUNCTIONS = 'all_functions';
    public const ALL_MODULES_ALL_FUNCTIONS = 'all_modules_all_functions';

    private array $policyChoices;

    /**
     * @param array $policyMap
     */
    public function __construct(TranslatorInterface $translator, array $policyMap)
    {
        $this->policyChoices = $this->buildPolicyChoicesFromMap($policyMap);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new class() implements DataTransformerInterface {
            /**
             * @param array{module: string, function: string}|null $value
             */
            public function transform(mixed $value): ?string
            {
                if (is_array($value)) {
                    return $value['module'] . '|' . $value['function'];
                }

                return null;
            }

            /**
             * @return array{module: ?string, function: ?string}
             */
            public function reverseTransform(mixed $value): array
            {
                $module = null;
                $function = null;

                if ($value) {
                    list($module, $function) = explode('|', $value);
                }

                return [
                    'module' => $module,
                    'function' => $function,
                ];
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'forms',
            'choices' => $this->policyChoices,
        ]);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    /**
     * Returns a usable hash for the policy choice widget.
     * Key is the translation key based on "module" name.
     * Value is a hash with translation key based on "module" and "function as a key and "<module>|<function"> as a value.
     *
     * @param array $policyMap
     *
     * @return array
     */
    private function buildPolicyChoicesFromMap(array $policyMap): array
    {
        $policyChoices = [
            self::MESSAGE_ID_PREFIX . self::ALL_MODULES => [
                self::MESSAGE_ID_PREFIX . self::ALL_MODULES_ALL_FUNCTIONS => '*|*',
             ],
        ];

        foreach ($policyMap as $module => $functionList) {
            $moduleKey = self::MESSAGE_ID_PREFIX . $module;
            // For each module, add possibility to grant access to all functions.
            $policyChoices[$moduleKey] = [
                $moduleKey . '.' . self::ALL_FUNCTIONS => "$module|*",
            ];

            foreach ($functionList as $function => $limitationList) {
                $moduleFunctionKey = self::MESSAGE_ID_PREFIX . "{$module}.{$function}";
                $policyChoices[$moduleKey][$moduleFunctionKey] = "$module|$function";
            }
        }

        return $policyChoices;
    }
}
