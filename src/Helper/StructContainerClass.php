<?php

namespace LTSC\Helper;


class StructContainerClass
{
    public const SCS_TYPE_STRING = 0;
    public const SCS_TYPE_CALLBACK = 1;
    public const SCS_TYPE_OBJECT = 2;

    public $injection;
    public $argNums;
    public $type;

    public function __construct($injection = null, int $argNums = 0, int $type = 0) {
        $this->injection = $injection;
        $this->argNums = $argNums;
        $this->type = $type;
    }
}