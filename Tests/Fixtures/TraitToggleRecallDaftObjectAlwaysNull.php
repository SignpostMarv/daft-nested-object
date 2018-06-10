<?php
/**
* Base daft nested objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures;

use SignpostMarv\DaftObject\DaftObject;

trait TraitToggleRecallDaftObjectAlwaysNull
{
    /**
    * @var bool
    */
    protected $ToggleRecallDaftObjectAlwaysNull = true;

    /**
    * @var bool
    */
    protected $ToggleRecallDaftObjectAfterCalls = false;

    /**
    * @var integer
    */
    protected $ToggleRecallDaftObjectAfterCallsCount = 0;

    /**
    * @var integer
    */
    protected $ToggleRecallDaftObjectAfterCallsAfter = 0;

    public function ToggleRecallDaftObjectAlwaysNull(bool $value) : void
    {
        $this->ToggleRecallDaftObjectAlwaysNull = $value;
    }

    public function ToggleRecallDaftObjectAfterCalls(bool $value, int $after) : void
    {
        if ($value) {
            $this->ToggleRecallDaftObjectAlwaysNull(false);
        }
        $this->ToggleRecallDaftObjectAfterCalls = $value;
        $this->ToggleRecallDaftObjectAfterCallsAfter = $after;
        $this->ToggleRecallDaftObjectAfterCallsCount = 0;
    }

    public function RecallDaftObject($id) : ? DaftObject
    {
        if ($this->ToggleRecallDaftObjectAfterCalls) {
            if ((++$this->ToggleRecallDaftObjectAfterCallsCount) > $this->ToggleRecallDaftObjectAfterCallsAfter) {

                $this->ToggleRecallDaftObjectAlwaysNull(true);
            }
        }

        return $this->ToggleRecallDaftObjectAlwaysNull ? null : parent::RecallDaftObject($id);
    }
}
