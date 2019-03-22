<?php

namespace LTSC\Helper;


class StructContainerSingle
{
    public $injection;
    public $getInstance;
    public $argNums;

    public function __construct(string $injection = null, string $getInstance = 'getInstance', int $argNums = 0) {
        $this->injection = $injection;
        $this->getInstance = $getInstance;
        $this->argNums = $argNums;
    }
}