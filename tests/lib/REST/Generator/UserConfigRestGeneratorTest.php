<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\REST\Generator;

use Ibexa\AdminUi\REST\Generator\UserConfigRestGenerator;
use Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestGeneratorInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitorDispatcher;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Rest\Output\Generator\Json;
use Ibexa\Rest\Output\Generator\Json\FieldTypeHashGenerator;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @covers \Ibexa\AdminUi\REST\Generator\UserConfigRestGenerator
 */
final class UserConfigRestGeneratorTest extends TestCase
{
    private const PARAMETER_USER = 'user';

    private ApplicationConfigRestGeneratorInterface $userConfigRestGenerator;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver&\PHPUnit\Framework\MockObject\MockObject */
    private PermissionResolver $permissionResolver;

    /** @var \Ibexa\Contracts\Rest\Output\Visitor&\PHPUnit\Framework\MockObject\MockObject */
    private Visitor $visitor;

    /** @var \Ibexa\Contracts\Core\Repository\Values\User\User&\PHPUnit\Framework\MockObject\MockObject */
    private User $user;

    protected function setUp(): void
    {
        $this->permissionResolver = $this->createMock(PermissionResolver::class);
        $this->userConfigRestGenerator = new UserConfigRestGenerator(
            $this->permissionResolver
        );

        $this->visitor = $this->createMock(Visitor::class);
        $this->user = $this->createMock(User::class);
        $this->user
            ->method('getContentType')
            ->willReturn(
                $this->createMock(ContentType::class)
            );
    }

    /**
     * @dataProvider provideDataForTestSupportsNamespace
     */
    public function testSupportsNamespace(
        bool $expected,
        string $namespace
    ): void {
        self::assertSame(
            $expected,
            $this->userConfigRestGenerator->supportsNamespace($namespace)
        );
    }

    /**
     * @dataProvider provideDataForTestSupportsParameter
     */
    public function testSupportsParameter(
        bool $expected,
        string $parameterName
    ): void {
        self::assertSame(
            $expected,
            $this->userConfigRestGenerator->supportsNamespace($parameterName)
        );
    }

    public function testGenerateParameterNotInstanceOfUser(): void
    {
        $value = new stdClass();
        $value->foo = 'foo';
        $value->bar = 'bar';

        $generator = $this->getGenerator(
            $this->getNormalizerMock(
                $value,
                [
                    'foo' => 'foo',
                    'bar' => 'bar',
                ]
            )
        );

        $generator->startDocument('test');
        $generator->startHashElement('user');

        $this->userConfigRestGenerator->generate(
            $value,
            $generator,
            $this->visitor
        );

        $generator->endHashElement('user');

        self::assertSame(
            '{"user":{"user":{"foo":"foo","bar":"bar"}}}',
            $generator->endDocument('test')
        );
    }

    public function testGenerateNoContentReadPermission(): void
    {
        $this->mockPermissionResolverCanUser($this->user, false);

        $generator = $this->getGenerator(
            $this->getNormalizerMock()
        );

        $generator->startDocument('test');
        $generator->startHashElement(self::PARAMETER_USER);

        $this->userConfigRestGenerator->generate(
            $this->user,
            $generator,
            $this->visitor
        );

        $generator->endHashElement(self::PARAMETER_USER);

        self::assertSame(
            '{"user":{}}',
            $generator->endDocument('test')
        );
    }

    public function testGenerate(): void
    {
        $this->mockPermissionResolverCanUser($this->user, true);
        $json = '{"user":{"user":{"User":{"id":1234,"_media-type": "application/vnd.ibexa.api.User+json","_href":"/api/ibexa/v2/user/users/1234"}}}}';
        $generator = $this->createMock(Generator::class);
        $generator
            ->method('startDocument')
            ->with('test');

        $generator
            ->method('endDocument')
            ->with('test')
            ->willReturn($json);

        $visitor = $this->getMockBuilder(Visitor::class)
            ->setConstructorArgs(
                [
                    $generator,
                    $this->createMock(ValueObjectVisitorDispatcher::class),
                ]
            )
            ->getMock();

        $visitor
            ->expects(self::once())
            ->method('visitValueObject')
            ->with($this->user);

        $this->userConfigRestGenerator->generate(
            $this->user,
            $generator,
            $visitor
        );

        self::assertSame(
            $json,
            $generator->endDocument('test')
        );
    }

    /**
     * @return iterable<array{
     *     bool,
     *     string,
     * }>
     */
    public function provideDataForTestSupportsNamespace(): iterable
    {
        yield 'Supported namespace' => [
            true,
            self::PARAMETER_USER,
        ];

        yield 'Unsupported namespace' => [
            false,
            'foo',
        ];
    }

    /**
     * @return iterable<array{
     *     bool,
     *     string,
     * }>
     */
    public function provideDataForTestSupportsParameter(): iterable
    {
        yield 'Supported parameter' => [
            true,
            self::PARAMETER_USER,
        ];

        yield 'Unsupported parameter' => [
            false,
            'foo',
        ];
    }

    private function getGenerator(NormalizerInterface $normalizer): Generator
    {
        return new Json(
            new FieldTypeHashGenerator($normalizer)
        );
    }

    /**
     * @param mixed $value
     * @param array<mixed>|null $normalizedValue
     *
     * @return \Symfony\Component\Serializer\Normalizer\NormalizerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function getNormalizerMock(
        $value = null,
        ?array $normalizedValue = null
    ): NormalizerInterface {
        $normalizer = $this->createMock(NormalizerInterface::class);

        if (
            null !== $value
            && null !== $normalizedValue
        ) {
            $normalizer
                ->method('normalize')
                ->with($value)
                ->willReturn($normalizedValue);
        }

        return $normalizer;
    }

    private function mockPermissionResolverCanUser(
        User $user,
        bool $canUser
    ): void {
        $this->permissionResolver
            ->method('canUser')
            ->with('content', 'read', $user)
            ->willReturn($canUser);
    }
}
