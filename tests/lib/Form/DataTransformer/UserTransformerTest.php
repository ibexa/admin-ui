<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\DataTransformer\UserTransformer;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content as API;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Core\Repository\Values\Content as Core;
use Ibexa\Core\Repository\Values\User\User as CoreUser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UserTransformerTest extends TestCase
{
    /** @var \Ibexa\AdminUi\Form\DataTransformer\UserTransformer */
    private $userTransformer;

    protected function setUp(): void
    {
        /** @var \Ibexa\Contracts\Core\Repository\UserService|\PHPUnit\Framework\MockObject\MockObject $userService */
        $userService = $this->createMock(UserService::class);
        $userService->expects(self::any())
            ->method('loadUser')
            ->with(123456)
            ->willReturn($this->generateUser(123456));

        $this->userTransformer = new UserTransformer($userService);
    }

    /**
     * @dataProvider transformDataProvider
     *
     * @param $value
     * @param $expected
     */
    public function testTransform($value, $expected)
    {
        $result = $this->userTransformer->transform($value);

        self::assertEquals($expected, $result);
    }

    /**
     * @dataProvider transformWithInvalidInputDataProvider
     *
     * @param $value
     */
    public function testTransformWithInvalidInput($value)
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a ' . User::class . ' object.');

        $this->userTransformer->transform($value);
    }

    /**
     * @dataProvider reverseTransformDataProvider
     *
     * @param $value
     * @param $expected
     */
    public function testReverseTransform($value, $expected)
    {
        $result = $this->userTransformer->reverseTransform($value);

        self::assertEquals($expected, $result);
    }

    /**
     * @dataProvider reverseTransformWithInvalidInputDataProvider
     *
     * @param $value
     */
    public function testReverseTransformWithInvalidInput($value)
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a numeric string.');

        $this->userTransformer->reverseTransform($value);
    }

    public function testReverseTransformWithNotFoundException()
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('User not found');

        /** @var \Ibexa\Contracts\Core\Repository\UserService|\PHPUnit\Framework\MockObject\MockObject $service */
        $service = $this->createMock(UserService::class);
        $service->method('loadUser')
            ->will(self::throwException(new class('User not found') extends NotFoundException {
            }));

        $transformer = new UserTransformer($service);

        $transformer->reverseTransform(654321);
    }

    /**
     * @return array
     */
    public function transformDataProvider(): array
    {
        $user = $this->generateUser(123456);

        return [
            'user_with_id' => [$user, 123456],
            'null' => [null, null],
        ];
    }

    /**
     * @return array
     */
    public function reverseTransformDataProvider(): array
    {
        $user = $this->generateUser(123456);

        return [
            'integer' => [123456, $user],
            'null' => [null, null],
        ];
    }

    /**
     * @return array
     */
    public function transformWithInvalidInputDataProvider(): array
    {
        return [
            'string' => ['string'],
            'integer' => [123456],
            'bool' => [true],
            'float' => [12.34],
            'array' => [[]],
            'object' => [new \stdClass()],
        ];
    }

    /**
     * @return array
     */
    public function reverseTransformWithInvalidInputDataProvider(): array
    {
        return [
            'string' => ['string'],
            'bool' => [true],
            'array' => [['element']],
            'object' => [new \stdClass()],
            'user' => [$this->generateUser()],
        ];
    }

    /**
     * @param int $id
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\User
     */
    private function generateUser(int $id = null): User
    {
        $contentInfo = new API\ContentInfo(['id' => $id]);
        $versionInfo = new Core\VersionInfo(['contentInfo' => $contentInfo]);
        $content = new Core\Content(['versionInfo' => $versionInfo]);

        return new CoreUser(['content' => $content]);
    }
}
