<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Base;

use Generator;
use SignpostMarv\DaftObject\ClassDoesNotImplementClassException;
use SignpostMarv\DaftObject\DaftNestedObject;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\NotPublicSetterPropertyException;
use SignpostMarv\DaftObject\Tests\DaftObjectImplementationTest as BaseTest;

class DaftObjectImplementationTest extends BaseTest
{
    public function dataProviderImplementations() : Generator
    {
        yield from [
            [
                Fixtures\DaftNestedIntObject::class,
            ],
            [
                Fixtures\DaftNestedWriteableIntObject::class,
            ],
        ];
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
        static::assertTrue(is_a($implementation, Fixtures\DaftNestedIntObject::class, true));

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

        $instance->$property = $value;
    }

    public function DataProviderAbstractNestedNotWriteable() : Generator
    {
        /**
        * @var array $args
        */
        foreach ($this->FuzzingImplementationsViaGenerator() as $args) {
            if (Fixtures\DaftNestedWriteableIntObject::class === $args[0]) {
                /**
                * @var scalar $value
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
        * @var array $args
        * @var string $args[0]
        * @var array<string, mixed> $args[1]
        */
        foreach ($this->FuzzingImplementationsViaGenerator() as $args) {
            $implementation = (string) $args[0];

            /**
            * @var string[]
            */
            $props = $implementation::DaftNestedObjectParentIdProperties();

            if (
                is_a($implementation, DaftNestedObject::class, true) &&
                0 === count(array_diff($props, array_keys($args[1])))
            ) {
                yield $args;
            }
        }
    }

    /**
    * @dataProvider DataProviderObjectParentId
    */
    public function testNestedObjectParentId(string $implementation, array $args) : void
    {
        /**
        * @var DaftNestedObject $instance
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
                return $instance->$prop;
            },
            $props
        );

        static::assertSame($instance->ObtainDaftNestedObjectParentId(), $actual);

        $id = array_map(
            /**
            * @return mixed
            */
            function (string $prop) use ($args) {
                return $args[$prop];
            },
            $props
        );

        if ($instance instanceof DaftNestedWriteableObject) {
            /**
            * @var DaftNestedWriteableObject $instance
            */
            $instance = new $implementation();

            $instance->AlterDaftNestedObjectParentId($id);

            foreach ($props as $prop) {
                static::assertSame($args[$prop], $instance->$prop);
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
