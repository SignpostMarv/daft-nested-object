<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests;

use Generator;
use SignpostMarv\DaftObject\DaftObject;

trait DataProviderTrait
{
    /**
    * @psalm-return Generator<int, array{0:class-string<DaftObject>}, mixed, void>
    */
    public function dataProviderImplementations_class_or_interface() : Generator
    {
        foreach (
            [
                '/src/*.php' => 'SignpostMarv\\DaftObject\\',
            ] as $glob => $ns
        ) {
            $files = glob(__DIR__ . '/..' . $glob);
            foreach ($files as $file) {
                if (
                    is_file($file) &&
                    (
                        class_exists($className = ($ns . pathinfo($file, PATHINFO_FILENAME))) ||
                        interface_exists($className)
                    ) &&
                    is_a($className, DaftObject::class, true)
                ) {
                    yield [$className];
                }
            }
        }
    }
}
