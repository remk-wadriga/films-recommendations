<?php


namespace App\TestService\Entities;


class LabeledPointEntity
{
    /**
     * @var VectorEntity
     */
    public $point;
    /**
     * @var string
     */
    public $label;

    public function __construct(VectorEntity $point, string $label)
    {
        $this->point = $point;
        $this->label = $label;
    }
}