<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Base;

use Generator;
use InvalidArgumentException;
use SignpostMarv\DaftObject\ClassDoesNotImplementClassException;
use SignpostMarv\DaftObject\DaftNestedObject;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\NotPublicSetterPropertyException;
use SignpostMarv\DaftObject\Tests\DaftObject\DaftObjectImplementationTest as BaseTest;

/**
* @template T as DaftNestedObject
*
* @template-extends BaseTest<T>
*/
class DaftObjectImplementationTest extends BaseTest
{
    public function dataProviderImplementations() : Generator
    {
        foreach (
            [
                '/src/*.php' => 'SignpostMarv\\DaftObject\\',
                '/Tests/Fixtures/*.php' => 'SignpostMarv\\DaftObject\\DaftNestedObject\\Tests\\Fixtures\\',
            ] as $glob => $ns
        ) {
            $files = glob(__DIR__ . '/../..' . $glob);

            foreach ($files as $file) {
                if (
                    is_file($file) &&
                    class_exists($className = ($ns . pathinfo($file, PATHINFO_FILENAME))) &&
                    is_a($className, DaftNestedObject::class, true)
                ) {
                    yield [$className];
                }
            }
        }
    }

    /**
    * @param mixed $value
    *
    * @dataProvider DataProviderAbstractNestedNotWriteable
    */
    public function testAbstractNestedNotWriteable(
        string $implementation,
        string $property,
        $value
    ) : void {
        if ( ! is_a($implementation, Fixtures\DaftNestedIntObject::class, true)) {
            static::assertTrue(is_a($implementation, Fixtures\DaftNestedIntObject::class, true));

            return;
        }

        $instance = new $implementation();

        if (
            'intNestedParentId' === $property &&
            ! ($instance instanceof Fixtures\DaftNestedWriteableIntObject)
        ) {
            $this->expectException(NotPublicSetterPropertyException::class);
            $this->expectExceptionMessage(
                'Property not a public setter: ' .
                $implementation .
                '::$' .
                $property
            );
        } else {
            $this->expectException(ClassDoesNotImplementClassException::class);
            $this->expectExceptionMessage(
                $implementation .
                ' does not implement ' .
                DaftNestedWriteableObject::class
            );
        }

        $instance->__set($property, $value);
    }

    public function DataProviderAbstractNestedNotWriteable() : Generator
    {
        /**
        * @var array
        */
        foreach ($this->FuzzingImplementationsViaGenerator() as $args) {
            if (Fixtures\DaftNestedWriteableIntObject::class === $args[0]) {
                /**
                * @var scalar
                */
                foreach ($args[1] as $arg => $value) {
                    if ('id' === $arg) {
                        continue;
                    }
                    yield [
                        Fixtures\DaftNestedIntObject::class,
                        $arg,
                        $value,
                    ];
                }
            }
        }
    }

    public function DataProviderObjectParentId() : Generator
    {
        /**
        * @var array
        * @var string $args[0]
        * @var array<string, mixed> $args[1]
        */
        foreach ($this->FuzzingImplementationsViaGenerator() as $args) {
            $implementation = (string) $args[0];

            if ( ! is_a($implementation, DaftNestedObject::class, true)) {
                throw new InvalidArgumentException(
                    'Index 0 yielded from ' .
                    static::class .
                    '::FuzzingImplementationsViaGenerator() must be an implementation of ' .
                    DaftNestedObject::class .
                    ', ' .
                    $implementation .
                    ' given!'
                );
            }

            /**
            * @var string[]
            */
            $props = $implementation::DaftNestedObjectParentIdProperties();

            if (
                0 === count(array_diff($props, array_keys($args[1])))
            ) {
                yield $args;
            }
        }
    }

    /**
    * @dataProvider DataProviderObjectParentId
    *
    * @param array<string, scalar|array|object|null> $args
    */
    public function testNestedObjectParentId(string $implementation, array $args) : void
    {
        if ( ! is_a($implementation, DaftNestedObject::class, true)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftNestedObject::class .
                ', ' .
                $implementation .
                ' given!'
            );
        }

        /**
        * @var DaftNestedObject
        */
        $instance = new $implementation($args);

        /**
        * @var string[]
        */
        $props = $implementation::DaftNestedObjectParentIdProperties();

        $actual = array_map(
            /**
            * @return mixed
            */
            function (string $prop) use ($instance) {
                return $instance->__get($prop);
            },
            $props
        );

        static::assertSame($instance->ObtainDaftNestedObjectParentId(), $actual);

        /**
        * @var (scalar|array|object|null)[]
        */
        $id = array_map(
            /**
            * @return scalar|array|object|null
            */
            function (string $prop) use ($args) {
                return $args[$prop];
            },
            $props
        );

        if ($instance instanceof DaftNestedWriteableObject) {
            /**
            * @var DaftNestedWriteableObject
            */
            $instance = new $implementation();

            $instance->AlterDaftNestedObjectParentId($id);

            foreach ($props as $prop) {
                static::assertSame($args[$prop], $instance->__get($prop));
            }

            static::assertSame($id, $instance->ObtainDaftNestedObjectParentId());
        }

        if ($instance instanceof Fixtures\DaftNestedWriteableIntObject) {
            $instance = new Fixtures\DaftNestedIntObject();

            $this->expectException(ClassDoesNotImplementClassException::class);
            $this->expectExceptionMessage(
                Fixtures\DaftNestedIntObject::class .
                ' does not implement ' .
                DaftNestedWriteableObject::class
            );

            $instance->AlterDaftNestedObjectParentId($id);
        }
    }

    protected function FuzzingImplementationsViaGenerator() : Generator
    {
        yield from [
            [
                Fixtures\DaftNestedWriteableIntObject::class,
                [
                    'intNestedLeft' => 0,
                    'intNestedRight' => 0,
                    'intNestedLevel' => 0,
                    'id' => 0,
                    'intNestedParentId' => 0,
                    'intNestedSortOrder' => 0,
                ],
            ],
        ];
    }
}
