<?php


namespace App\TestService\Calculator;

use App\Exception\ServiceException;
use \Closure;

trait MathTrait
{
    /**
     * Calculate the "difference quotient" (almost derivative) of some function (the smaller h - the better)
     *
     * @param Closure $f
     * @param float $x
     * @param float $h
     * @return float|int
     */
    public function differenceQuotient(Closure $f, float $x, float $h = 0.00001)
    {
        return ($f($x + $h) - $f($x)) / $h;
    }

    /**
     * Calculate the "partial difference quotient" (almost partial derivative) of some function of several variables (the smaller h - the better)
     *
     * @param Closure $f
     * @param array $v
     * @param int $i
     * @param float $h
     * @return float|int
     */
    public function partialDifferenceQuotient(Closure $f, array $v, int $i, float $h = 0.00001)
    {
        $w = [];
        foreach ($v as $index => $val) {
            $w[] = $index === $i ? $val + $h : $val;
        }
        return ($f($w) - $f($v)) / $h;
    }

    /**
     * Estimate function value gradient in point "v" (this is the vector, array of numbers)
     *
     * @param Closure $f
     * @param array $v
     * @param float $h
     * @return array
     */
    public function estimateGradient(Closure $f, array $v, float $h = 0.00001): array
    {
        $res = [];
        foreach ($v as $i => $val) {
            $res[] = $this->partialDifferenceQuotient($f, $v, $i, $h);
        }
        return $res;
    }

    /**
     * Make a step for point "v" in direction "direction"
     *
     * @param array $v
     * @param array $direction
     * @param float $stepSize
     * @return array
     */
    public function step(array $v, array $direction, float $stepSize): array
    {
        $res = [];
        foreach ($v as $i => $val) {
            $value = $val + $direction[$i] * $stepSize;
            $res[] = $value;
        }
        return $res;
    }

    /**
     * Find the point where the function "targetFn" has a minimum value
     *    using the packet "batch" of step sizes
     *
     * @param Closure $targetFn
     * @param Closure $gradientFn
     * @param array $theta0
     * @param float $tolerance
     * @param array $stepSizes
     * @return array|null
     */
    public function minimizeBatch(Closure $targetFn, Closure $gradientFn, array $theta0, float $tolerance = 0.000001, array $stepSizes = []): array
    {
        if (empty($stepSizes)) {
            $stepSizes = [100, 10, 1, 0.1, 0.01, 0.001, 0.0001, 0.00001];
        }

        // Make the safe version of target function
        $targetFnSafe = function ($theta) use ($targetFn) {
            try {
                return $targetFn($theta);
            } catch (\Exception $e) {
                return null;
            }
        };

        // For the start, final point (point of target function minimum) - this is just a start point "theta0"
        $theta = $theta0;
        // Find the final (minimum) value, for the start - just value of the "targetFn" function for start point "theta0"
        $value = $targetFnSafe($theta);
        if ($value === null) {
            return null;
        }

        $steps = 0;

        while (true) {
            $steps++;
            // Find the gradient for current point
            $gradient = $gradientFn($theta);

            $nextTheta = [];
            $minVal = null;
            // Find values for all steps sizes and the smaller of them
            foreach ($stepSizes as $stepSize) {
                $tmpTheta = $this->step($theta, $gradient, -$stepSize);
                // ...and find the smaller of them
                $tmpVal = $targetFnSafe($tmpTheta);
                if ($tmpVal === null) {
                    continue;
                }
                if ($minVal === null || $tmpVal < $minVal) {
                    $minVal = $tmpVal;
                    $nextTheta = $tmpTheta;
                }
            }
            if (empty($nextTheta) || $minVal === null) {
                continue;
            }

            $nextValue = $minVal;

            if ($value - $nextValue < $tolerance) {
                echo 'Tolerance: ', number_format($tolerance, 7), '<br />';
                echo 'Steps count:', $steps, '<br />';
                return $theta;
            } else {
                $theta = $nextTheta;
                $value = $nextValue;
            }
        }

        return null;
    }

    /**
     * Find the point where the function "targetFn" has a maximum value
     *    using the packet "batch" of step sizes
     *
     * @param Closure $targetFn
     * @param Closure $gradientFn
     * @param array $theta0
     * @param float $tolerance
     * @param array $stepSizes
     * @return array|null
     */
    public function maximizeBatch(Closure $targetFn, Closure $gradientFn, array $theta0, float $tolerance = 0.000001, array $stepSizes = [])
    {
        $negateFn = function ($theta) use ($targetFn) {
            return -$targetFn($theta);
        };
        $negateAllFn = function ($theta) use ($gradientFn) {
            return array_map(function ($y) { return -$y; }, $gradientFn($theta));
        };

        return $this->minimizeBatch($negateFn, $negateAllFn, $theta0, $tolerance, $stepSizes);
    }

    /**
     * Find the point where the function "targetFn" has a minimum value
     *    using the "Stochastic gradient descent" method (https://en.wikipedia.org/wiki/Stochastic_gradient_descent)
     *
     * @param Closure $targetFn
     * @param Closure $gradientFn
     * @param array $x
     * @param array $y
     * @param array $theta0
     * @param float $alpha0
     * @return array|null
     * @throws ServiceException
     */
    public function minimizeStochastic(Closure $targetFn, Closure $gradientFn, array $x, array $y, array $theta0, float $alpha0 = 0.01): ?array
    {
        if (count($x) !== count($y)) {
            throw new ServiceException('Input arrays mus have the equal sizes', ServiceException::CODE_INVALID_PARAMS);
        }

        $data = array_combine($x, $y);

        // Initial hypothesis
        $theta = $theta0;
        // Initial step size
        $alpha = $alpha0;

        // Initial minimum
        $minTheta = null;
        $minVal = null;

        $iterationsWithNoImprovement = 0;

        // Stop if we have no improvements after 100 iterations
        while ($iterationsWithNoImprovement < 100) {
            $val = 0;
            foreach ($data as $xi => $yi) {
                $val += $targetFn($xi, $yi, $theta);
            }
            if ($minVal === null || $val < $minVal) {
                // We have found a new minimum, so let's remember it and return to initial step size
                $minTheta = $theta;
                $minVal = $val;
                $iterationsWithNoImprovement = 0;
                $alpha = $alpha0;
            } else {
                // We have no found a minimum, so let's get smaller the step size
                $iterationsWithNoImprovement++;
                $alpha *= 0.9;
                // And make the step of gradient for each of data points
                foreach ($theta->randomizeArrayOrder($data) as $xi => $yi) {
                    $gradientI = $gradientFn($xi, $yi, $theta);
                    $theta = $this->vectorsSubtract($theta, $this->vectorMultiplyByScalar($gradientI, $alpha));
                }
            }
        }

        return $minTheta;
    }

    /**
     * Find the point where the function "targetFn" has a maximum value
     *    using the "Stochastic gradient descent" method (https://en.wikipedia.org/wiki/Stochastic_gradient_descent)
     *
     * @param Closure $targetFn
     * @param Closure $gradientFn
     * @param array $x
     * @param array $y
     * @param array $theta0
     * @param float $alpha0
     * @return array|null
     * @throws ServiceException
     */
    public function maximizeStochastic(Closure $targetFn, Closure $gradientFn, array $x, array $y, array $theta0, float $alpha0 = 0.01): ?array
    {
        $negateFn = function ($theta) use ($targetFn) {
            return -$targetFn($theta);
        };
        $negateAllFn = function ($theta) use ($gradientFn) {
            return array_map(function ($y) { return -$y; }, $gradientFn($theta));
        };
        return $this->minimizeStochastic($negateFn, $negateAllFn, $x, $y, $theta0, $alpha0);
    }

    /**
     * Get the random ordered array with his keys
     *
     * @param array $data
     * @return array
     */
    public function randomizeArrayOrder(array $data): array
    {
        $keys = array_keys($data);
        shuffle($keys);
        $res = [];
        foreach ($keys as $key) {
            $res[$key] = $data[$key];
        }
        return $res;
    }

    /**
     * Round to the next smallest multiple interval with size "bucketSize"
     *
     * @param float $point
     * @param int $bucketSize
     * @return int
     */
    public function bucketize(float $point, int $bucketSize): int
    {
        return $bucketSize * floor($point / $bucketSize);
    }

    /**
     * Split data for two arrays
     *
     * @param array $data
     * @param float $prob
     * @return array
     */
    public function splitData(array $data, float $prob): array
    {
        $result = [[], []];
        foreach ($data as $key => $row) {
            $index = rand(0, 1000) / 1000 < $prob ? 0 : 1;
            $result[$index][$key] = $row;
        }
        return $result;
    }

    /**
     * Split data to "test" and "control" couples
     *
     * @param array $x
     * @param array $y
     * @param float $testPct
     * @return array
     * @throws ServiceException
     */
    public function trainTestSplitData(array $x, array $y, float $testPct)
    {
        if (count($x) !== count($y)) {
            throw new ServiceException('The count of arrays "x" and "y" must be equals to split them to the "train" and "test" data', ServiceException::CODE_INVALID_PARAMS);
        }
        $data = array_combine($x, $y);
        list($train, $test) = $this->splitData($data, 1 - $testPct);
        list($xTrain, $yTrain) = [array_keys($train), array_values($train)];
        list($xTest, $yTest) = [array_keys($test), array_values($test)];
        return [$xTrain, $yTrain, $xTest, $yTest];
    }

    /**
     * Calculate the results accuracy:
     *   "tp" - true positive, "fp" - false positive, "fn" - false negative, "tn" - true negative
     *
     * @param float $tp
     * @param float $fp
     * @param float $fn
     * @param float $tn
     * @return float
     */
    public function accuracy(float $tp, float $fp, float $fn, float $tn): float
    {
        $correct = $tp + $tn;
        return $correct / ($correct + $fp + $fn);
    }

    /**
     * Calculate the accuracy (as the degree of positive forecast values)
     *    "tp" - true positive, "fp" - false positive, "fn" - false negative, "tn" - true negative
     *
     * @param float $tp
     * @param float $fp
     * @param float $fn
     * @param float $tn
     * @return float
     */
    public function precision(float $tp, float $fp, float $fn, float $tn): float
    {
        return $tp / ($tp + $fp);
    }

    /**
     * Calculate the recall (the proportion of positive predicted that the model identified)
     *
     * @param float $tp
     * @param float $fp
     * @param float $fn
     * @param float $tn
     * @return float
     */
    public function recall(float $tp, float $fp, float $fn, float $tn): float
    {
        return $tp / ($tp + $fn);
    }

    /**
     * Calculate the "F1 metric" (the combination of precision and recall)
     *    "tp" - true positive, "fp" - false positive, "fn" - false negative, "tn" - true negative
     *    * https://en.wikipedia.org/wiki/Harmonic_mean
     *
     * @param float $tp
     * @param float $fp
     * @param float $fn
     * @param float $tn
     * @return float
     */
    public function F1Score(float $tp, float $fp, float $fn, float $tn): float
    {
        $precision = $this->precision($tp, $fp, $fn, $tn);
        $recall = $this->recall($tp, $fp, $fn, $tn);
        return (2 * $precision * $recall) / ($precision + $recall);
    }
}