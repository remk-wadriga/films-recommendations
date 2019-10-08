<?php

namespace App\TestService\Calculator;

use App\Exception\ServiceException;
use App\TestService\Entities\VectorEntity;

trait VectorsTrait
{
    /**
     * Calculate vectors sum (each element of result will be equals to sum of vectors elements with teh same indexes)
     *
     * @param VectorEntity $v
     * @param VectorEntity[] ...$vectors
     * @return array
     * @throws ServiceException
     */
    public function vectorsSum(VectorEntity $v, ...$vectors): VectorEntity
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
        return new VectorEntity($result);
    }

    /**
     * Calculate vectors subtract (the same that "vectorsSum" but use operation "-" instead "+")
     *
     * @param VectorEntity $v
     * @param VectorEntity $w
     * @return VectorEntity
     * @throws ServiceException
     */
    public function vectorsSubtract(VectorEntity $v, VectorEntity $w): VectorEntity
    {
        $this->checkVectorsLength($v, $w);
        $result = [];
        foreach ($v as $index => $value) {
            $this->checkVectorIndex($w, $index);

            $result[$index] = $value - $w[$index];
        }
        return new VectorEntity($result);
    }

    /**
     * Calculate vectors multiply (the same that "vectorsSum" but use operation "*" instead "+")
     *
     * @param VectorEntity $v
     * @param VectorEntity[] ...$vectors
     * @return array
     * @throws ServiceException
     */
    public function vectorsMultiply(VectorEntity $v, ...$vectors): array
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
     * @param VectorEntity $v
     * @param VectorEntity[] ...$vectors
     * @return float|int
     * @throws ServiceException
     */
    public function vectorsScalarMultiply(VectorEntity $v, ...$vectors)
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
     * @param VectorEntity $vector
     * @param float $scalar
     * @return VectorEntity
     */
    public function vectorMultiplyByScalar(VectorEntity $vector, float $scalar): VectorEntity
    {
        $data = array_map(function ($value) use ($scalar) { return $value * $scalar; }, $vector);
        return new VectorEntity($data);
    }

    /**
     * Calculate the average between vectors (each element of result will be equals to average value of vectors elements with the same indexes)
     *
     * @param VectorEntity $v
     * @param VectorEntity[] ...$vectors
     * @return VectorEntity
     * @throws ServiceException
     */
    public function vectorsMean(VectorEntity $v, ...$vectors): VectorEntity
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
     * @param VectorEntity $vector
     * @return float|int
     * @throws ServiceException
     */
    public function vectorSumOfSquares(VectorEntity $vector)
    {
        return $this->vectorsScalarMultiply($vector, $vector);
    }

    /**
     * Calculate the vector's length (the square root of the sum of squares of vector's elements)
     *
     * @param VectorEntity $vector
     * @return float
     * @throws ServiceException
     */
    public function vectorMagnitude(VectorEntity $vector): float
    {
        return sqrt($this->vectorSumOfSquares($vector));
    }

    /**
     * Calculate the square of the distance between vectors (sum of squared difference between vectors elements with the same indexes)
     *
     * @param VectorEntity $v
     * @param VectorEntity[] ...$vectors
     * @return float|int
     * @throws ServiceException
     */
    public function vectorsSquaredDistance(VectorEntity $v, ...$vectors)
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
     * @param VectorEntity $v
     * @param VectorEntity[] ...$vectors
     * @return float
     * @throws ServiceException
     */
    public function vectorsDistance(VectorEntity $v, ...$vectors)
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
     * @param VectorEntity $v
     * @return VectorEntity
     * @throws ServiceException
     */
    public function vectorDirection(VectorEntity $v): VectorEntity
    {
        $magnitude = $this->vectorMagnitude($v);
        return array_map(function ($val) use ($magnitude) { return $val / $magnitude; }, $v);
    }

    /**
     * Project vector "v" onto vector "direction"
     *
     * @param VectorEntity $v
     * @param VectorEntity $direction
     * @return array
     * @throws ServiceException
     */
    public function vectorProject(VectorEntity $v, VectorEntity $direction): VectorEntity
    {
        $projectionLength = $this->vectorsScalarMultiply($v, $direction);
        $data = $this->vectorMultiplyByScalar($direction, $projectionLength);
        return new VectorEntity($data);
    }

    /**
     * Remove projection from vector
     *
     * @param VectorEntity $v
     * @param VectorEntity $projection
     * @return array
     * @throws ServiceException
     */
    public function removeProjectionFromVector(VectorEntity $v, VectorEntity $projection): VectorEntity
    {
        $data = $this->vectorsSubtract($v, $this->vectorProject($v, $projection));
        return new VectorEntity($data);
    }

    /**
     * Transform vector "v" by components
     *
     * @param VectorEntity $v
     * @param VectorEntity $components
     * @return array
     * @throws ServiceException
     */
    public function transformVector(VectorEntity $v, VectorEntity $components): VectorEntity
    {
        $result = [];
        foreach ($components as $component) {
            $result[] = $this->vectorsScalarMultiply($v, $component);
        }
        return new VectorEntity($result);
    }


    private function checkVectorsLength(VectorEntity $v, VectorEntity $w)
    {
        $countV = count($v);
        $countW = count($w);
        if ($countV !== $countW) {
            throw new ServiceException(sprintf('Vectors must have the same length to make a sum. Length of vectors are %s and %s', $countV, $countW), ServiceException::CODE_INVALID_PARAMS);
        }
    }

    private function checkVectorIndex(VectorEntity $w, $index)
    {
        if (!isset($w[$index])) {
            throw new ServiceException(sprintf('Vector 2 does not have argument with index "%s" from vector 1', $index), ServiceException::CODE_INVALID_PARAMS);
        }
    }
}