<?php


namespace App\TestService\Examples;

use App\TestService\Calculator\MathTrait;
use App\TestService\Calculator\VectorsTrait;

class GradientDescent
{
    use MathTrait;
    use VectorsTrait;

    public function checkDifferenceBetweenDerivativeAndDifferenceQuotient(int $exp, float $h = 0.01)
    {
        $f = function ($x) use($exp) {
            return pow($x, $exp);
        };
        $derivative = function ($x) use($exp) {
            return $exp * pow($x, $exp - 1);
        };

        $derivatives = [];
        $quotients = [];

        for ($i = 0; $i < 30; $i++) {
            $d = $derivative($i);
            $derivatives[] = number_format($d, 3, '.', '');

            $q = $this->differenceQuotient($f, $i, $h);
            $quotients[] = number_format($q, 3, '.', '');
        }

        echo 'Derivatives: ', implode(', ', $derivatives), '<br />';
        echo 'Quotients:   ', implode(', ', $quotients), '<br />';
        exit();
    }

    public function findTheMinimumOfSumOfSquares(array $point = [], float $tolerance = 0.001)
    {
        if (empty($point)) {
            for ($i = 0; $i < 3; $i++) {
                $point[] = rand(-10, 10);
            }
        }

        $startPoint = $point;
        $calculatingSteps = 0;
        $time = microtime(true);

        $targetFn = function (array $v) {
            return $this->sumOfSquares($v);
        };
        $gradientFn = function (array $v) {
            return $this->sumOfSquaresGradient($v);
        };

        /*while (true) {
            // Calculate gradient in point "v"
            $gradient = $this->sumOfSquaresGradient($point);
            $calculatingSteps++;

            // Find the next point in the opposite direction of gradient
            $nextPoint = $this->step($point, $gradient, -0.01);
            // Check the distance between points - if it's smaller than tolerance, stop searching, else - go on
            if ($this->vectorsDistance($nextPoint, $point) < $tolerance) {
                break;
            }
            $point = $nextPoint;
        }*/

        $point = $this->minimizeBatch($targetFn, $gradientFn, $point, $tolerance);

        $time = number_format(microtime(true) - $time, 7);

        $point = array_map(function ($val) { return number_format($val, 7); }, $point);
        $distanceFrom0 = number_format(sqrt(array_sum(array_map(function ($val) { return $val * $val; }, $point))), 7);

        echo sprintf('Min point: [%s] (%s)<br />Tolerance: %s, Time: %s',
            implode(', ', $point), $distanceFrom0, number_format($tolerance, 7), $time);
    }


    private function sumOfSquares(array $v): float
    {
        $res = 0;
        foreach ($v as $val) {
            $res += $val * $val;
        }
        return $res;
    }

    private function sumOfSquaresGradient(array $v): array
    {
        $res = [];
        foreach ($v as $val) {
            $res[] = 2 * $val;
        }
        return $res;
    }


}