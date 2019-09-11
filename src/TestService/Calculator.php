<?php

namespace App\TestService;

use App\TestService\Calculator\VectorsTrait;
use App\TestService\Calculator\MatrixTrait;
use App\TestService\Calculator\StatisticsTrait;
use App\TestService\Calculator\ProbabilityTrait;

class Calculator
{
    use VectorsTrait;
    use MatrixTrait;
    use StatisticsTrait;
    use ProbabilityTrait;

}