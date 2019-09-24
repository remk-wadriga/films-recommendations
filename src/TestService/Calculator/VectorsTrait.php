<?php

namespace App\TestService\Calculator;

use App\Exception\ServiceException;

trait VectorsTrait
{
    /**
     * Calculate vectors sum (each element of result will be equals to sum of vectors elements with teh same indexes)
     *
     * @param array $v
     * @param array ...$vectors
     * @return array
     * @throws ServiceException
     */
    public function vectorsSum(array $v, array ...$vectors): array
    {
        $result = [];
        foreach ($vectors as $i => $w) {
            $this->checkVectorsLength($v, $w);
            foreach ($v as $index => $value) {
                $this->checkVectorIndex($w, $index);
                if (isset($result[$index])) {
                    $value = $result[$index];
                }
                $result[$index] = $value + $w[$index];
            }
        }
        return $result;
    }

    /**
     * Calculate vectors subtract (the same that "vectorsSum" but use operation "-" instead "+")
     *
     * @param array $v
     * @param array $w
     * @return array
     * @throws ServiceException
     */
    public function vectorsSubtract(array $v, array $w): array
    {
        $this->checkVectorsLength($v, $w);
        $result = [];
        foreach ($v as $index => $value) {
            $this->checkVectorIndex($w, $index);
            $result[$index] = $value - $w[$index];
        }
        return $result;
    }

    /**
     * Calculate vectors multiply (the same that "vectorsSum" but use operation "*" instead "+")
     *
     * @param array $v
     * @param array ...$vectors
     * @return array
     * @throws ServiceException
     */
    public function vectorsMultiply(array $v, array ...$vectors): array
    {
        $result = [];
        foreach ($vectors as $w) {
            $this->checkVectorsLength($v, $w);
            foreach ($v as $index => $value) {
                $this->checkVectorIndex($w, $index);
                if (isset($result[$index])) {
                    $value = $result[$index];
                }
                $result[$index] = $value * $w[$index];
            }
        }
        return $result;
    }

    /**
     * <<dot>>
     * Calculate vectors scalar multiply (just a sum of result of vectors multiply)
     *
     * @param array $v
     * @param array ...$vectors
     * @return float|int
     * @throws ServiceException
     */
    public function vectorsScalarMultiply(array $v, array ...$vectors)
    {
        $result = $v;
        foreach ($vectors as $w) {
            $result = $this->vectorsMultiply($result, $w);
        }
        return array_sum($result);
    }

    /**
     * <<scalar_multiply>>
     * Calculate the multiplication of vector and some scalar: float or integer (each element of result will be equals to multiplication of element of input vector and scalar)
     *
     * @param array $vector
     * @param $scalar
     * @return array
     */
    public function vectorMultiplyByScalar(array $vector, $scalar): array
    {
        return array_map(function ($value) use ($scalar) { return $value * $scalar; }, $vector);
    }

    /**
     * Calculate the average between vectors (each element of result will be equals to average value of vectors elements with the same indexes)
     *
     * @param array $v
     * @param array ...$vectors
     * @return array
     * @throws ServiceException
     */
    public function vectorsMean(array $v, array ...$vectors): array
    {
        $n = count($vectors) + 1;
        $sum = $v;
        while (count($vectors) > 0) {
            $sum = $this->vectorsSum($sum, array_shift($vectors));
        }
        return $this->vectorMultiplyByScalar($sum, 1/$n);
    }

    /**
     * Calculate the sum of squares of vector's elements
     *
     * @param array $vector
     * @return float|int
     * @throws ServiceException
     */
    public function vectorSumOfSquares(array $vector)
    {
        return $this->vectorsScalarMultiply($vector, $vector);
    }

    /**
     * Calculate the vector's length (the square root of the sum of squares of vector's elements)
     *
     * @param array $vector
     * @return float
     * @throws ServiceException
     */
    public function vectorMagnitude(array $vector): float
    {
        return sqrt($this->vectorSumOfSquares($vector));
    }

    /**
     * Calculate the square of the distance between vectors (sum of squared difference between vectors elements with the same indexes)
     *
     * @param array $v
     * @param array ...$vectors
     * @return float|int
     * @throws ServiceException
     */
    public function vectorsSquaredDistance(array $v, array ...$vectors)
    {
        $subtract = $v;
        while (count($vectors) > 0) {
            $subtract = $this->vectorsSubtract($subtract, array_shift($vectors));
        }
        return $this->vectorSumOfSquares($subtract);
    }

    /**
     * Calculate the distance between vectors (the square root of the sum of squared difference between vectors elements with the same indexes)
     *
     * @param array $v
     * @param array ...$vectors
     * @return float
     * @throws ServiceException
     */
    public function vectorsDistance(array $v, array ...$vectors)
    {
        /*$squaredDistances = 0;
        while (count($vectors) > 0) {
            $squaredDistances += $this->vectorsSquaredDistance($v, array_shift($vectors));
        }
        return sqrt($squaredDistances);*/
        $subtract = $v;
        while (count($vectors) > 0) {
            $subtract = $this->vectorsSubtract($subtract, array_shift($vectors));
        }
        return $this->vectorMagnitude($subtract);
    }

    /**
     * Get vector that means the direction of input vector
     *
     * @param array $v
     * @return array
     * @throws ServiceException
     */
    public function vectorDirection(array $v): array
    {
        $magnitude = $this->vectorMagnitude($v);
        return array_map(function ($val) use ($magnitude) { return $val / $magnitude; }, $v);
    }

    /**
     * Project vector "v" onto vector "direction"
     *
     * @param array $v
     * @param array $direction
     * @return array
     * @throws ServiceException
     */
    public function vectorProject(array $v, array $direction): array
    {
        $projectionLength = $this->vectorsScalarMultiply($v, $direction);
        return $this->vectorMultiplyByScalar($direction, $projectionLength);
    }

    /**
     * Remove projection from vector
     *
     * @param array $v
     * @param array $projection
     * @return array
     * @throws ServiceException
     */
    public function removeProjectionFromVector(array $v, array $projection): array
    {
        return $this->vectorsSubtract($v, $this->vectorProject($v, $projection));
    }

    /**
     * Transform vector "v" by components
     *
     * @param array $v
     * @param array $components
     * @return array
     * @throws ServiceException
     */
    public function transformVector(array $v, array $components): array
    {
        $result = [];
        foreach ($components as $component) {
            $result[] = $this->vectorsScalarMultiply($v, $component);
        }
        return $result;
    }


    private function checkVectorsLength(array $v, array $w)
    {
        $countV = count($v);
        $countW = count($w);
        if ($countV !== $countW) {
            throw new ServiceException(sprintf('Vectors must have the same length to make a sum. Length of vectors are %s and %s', $countV, $countW), ServiceException::CODE_INVALID_PARAMS);
        }
    }

    private function checkVectorIndex(array $w, $index)
    {
        if (!array_key_exists($index, $w)) {
            throw new ServiceException(sprintf('Vector 2 does not have argument with index "%s" from vector 1', $index), ServiceException::CODE_INVALID_PARAMS);
        }
    }
}