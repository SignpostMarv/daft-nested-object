<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use TypeError;

class DaftNestedWriteableIntObject extends DaftNestedIntObject implements DaftNestedWriteableObject
{
    public function SetIntNestedParentId(int $value) : void
    {
        $this->NudgePropertyValue('intNestedParentId', $value);
    }

    public function SetId(int $value) : void
    {
        $this->NudgePropertyValue('id', $value);
    }
}
