<?php

namespace App\TestService;

use App\TestService\Calculator\VectorsTrait;
use App\TestService\Calculator\MatrixTrait;
use App\TestService\Calculator\StatisticsTrait;

class Calculator
{
    use VectorsTrait;
    use MatrixTrait;
    use StatisticsTrait;

}