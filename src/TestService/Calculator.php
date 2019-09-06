<?php

namespace App\TestService;

use App\Exception\ServiceException;

class Calculator
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
     * Calculate vectors scalar multiply (the same that "vectorsSum" but use operation "*" instead "+")
     *
     * @param array $v
     * @param array ...$vectors
     * @return float|int
     * @throws ServiceException
     */
    public function vectorsScalarMultiply(array $v, array ...$vectors)
    {
        $result = [];
        foreach ($vectors as $i => $w) {
            $this->checkVectorsLength($v, $w);
            foreach ($v as $index => $value) {
                $this->checkVectorIndex($w, $index);
                if (isset($result[$index])) {
                    $value = $result[$index];
                }
                $result[$index] = $value * $w[$index];
            }
        }
        return array_sum($result);
    }

    /**
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
     * Calculate the average between vectors (each element of result will be equals to average value of vectors elements with teh same indexes)
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
    public function vectorMagnitude(array $vector)
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