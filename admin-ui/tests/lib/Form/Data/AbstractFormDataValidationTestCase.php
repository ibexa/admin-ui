<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\Data;

use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;

abstract class AbstractFormDataValidationTestCase extends TypeTestCase
{
    /**
     * @phpstan-return iterable<string, array{array<string, mixed>, \Ibexa\Tests\AdminUi\Form\Data\FormErrorDataTestWrapper[]}>
     */
    abstract public static function getDataForTestFormSubmitValidation(): iterable;

    abstract protected function getForm(): FormInterface;

    /**
     * @dataProvider getDataForTestFormSubmitValidation
     *
     * @param array<mixed> $formData
     *
     * @phpstan-param \Ibexa\Tests\AdminUi\Form\Data\FormErrorDataTestWrapper[] $expectedFormErrors
     */
    final public function testFormSubmitValidation(array $formData, array $expectedFormErrors): void
    {
        $form = $this->getForm();

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
        self::assertEquals(
            $expectedFormErrors,
            $this->mapFormErrors($form),
            var_export($this->mapFormErrors($form), true)
        );

        self::assertSame(empty($expectedFormErrors), $form->isValid());
    }

    /**
     * @return \Ibexa\Tests\AdminUi\Form\Data\FormErrorDataTestWrapper[]
     */
    private function mapFormErrors(FormInterface $form): array
    {
        return array_map(
            static function (FormError $error): FormErrorDataTestWrapper {
                $cause = $error->getCause();
                if (!$cause instanceof ConstraintViolation) {
                    throw new \LogicException(
                        'Expected a ConstraintViolation, got "' . get_class($cause) . '"',
                        1,
                        $cause instanceof \Throwable ? $cause : null
                    );
                }

                return new FormErrorDataTestWrapper(
                    $error->getMessage(),
                    $error->getMessageParameters(),
                    $cause->getPropertyPath()
                );
            },
            iterator_to_array($form->getErrors(true))
        );
    }

    /**
     * @return list<\Symfony\Component\Form\FormExtensionInterface>
     */
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
                               ->enableAttributeMapping()
                               ->getValidator()
        ;

        return [
            new ValidatorExtension($validator),
        ];
    }
}
