<?php


namespace App\TestService\Calculator;


trait StatisticsTrait
{
    /**
     * Calculate average value in array of numbers
     *
     * @param array $data
     * @return float
     */
    public function mean(array $data): float
    {
        $dataCount = count($data);
        return $dataCount > 0 ? array_sum($data) / $dataCount : 0;
    }

    /**
     * Sort array of numbers
     *
     * @param array $data
     * @param bool $desc
     */
    public function sort(array &$data, bool $desc = false)
    {
        usort($data, function ($val1, $val2) use ($desc) {
            $res = $desc ? $val2 > $val1 : $val1 > $val2;
            return $res ? 1 : -1;
        });
    }

    /**
     * Calculate "median" in the list of numbers
     *    "Median" this is the central element of the given list or average value between two closest to center elements
     *
     * @param array $data
     * @param bool $needToSort
     * @return float|null
     */
    public function median(array &$data, bool $needToSort = true): ?float
    {
        $dataCount = count($data);
        if ($dataCount === 0) {
            return null;
        }
        if ($needToSort) {
            $this->sort($data);
        }

        $midPoint = (int)floor($dataCount / 2);
        return $dataCount %2 === 1 ? $data[$midPoint] : ($data[$midPoint - 1] + $data[$midPoint]) / 2;
    }

    /**
     * Calculate "quantile" in the list of numbers
     *    "Quantile" this is the element of the given list, before that some percent (param "percent") of elements are located.
     *
     * @param array $data
     * @param int $percent
     * @param bool $needToSort
     * @return int|float|null
     */
    public function quantile(array &$data, int $percent, bool $needToSort = true)
    {
        $dataCount = count($data);
        if ($dataCount <= 1) {
            return null;
        }
        if ($needToSort) {
            $this->sort($data);
        }

        $index = (int)floor($dataCount * $percent / 100);
        return $data[$index];
    }

    /**
     * Get the "most popular" elements from the list of numbers
     *
     * @param array $data
     * @return array
     */
    public function mode(array $data): array
    {
        $dataCount = count($data);
        if ($dataCount === 0) {
            return null;
        }

        $maxCount = 0;
        $countedValues = [];
        $floatValues = false;
        foreach ($data as $val) {
            if (is_float($val)) {
                $val = (string)$val;
                $floatValues = true;
            }
            if (!isset($countedValues[$val])) {
                $countedValues[$val] = 0;
            }
            $countedValues[$val]++;
            if ($countedValues[$val] >= $maxCount) {
                $maxCount = $countedValues[$val];
            }
        }
        if ($maxCount === 1) {
            return [];
        }

        $res = array_keys($countedValues, $maxCount);
        return $floatValues ? array_map(function ($val) { return floatval($val); }, $res) : $res;
    }

    /**
     * Get the difference between max and min value of the numbers list
     *
     * @param array $data
     * @return int|float
     */
    public function range(array $data)
    {
        return max($data) - min($data);
    }

    /**
     * Calculate difference between each element of list of numbers and average value of this list
     *
     * @param array $data
     * @return array
     */
    public function deviationsOfMean(array $data): array
    {
        $mean = $this->mean($data);
        return array_map(function ($val) use ($mean) {
            return $val - $mean;
        }, $data);
    }

    /**
     * Calculate "variance" of the list of numbers
     *    "Variance" this is the average value of sum of squares of differences between each element and "mean of list" (just average value)
     *
     * @param array $data
     * @return float|int
     */
    public function variance(array $data)
    {
        $dataCount = count($data);
        if ($dataCount <= 1) {
            return 0;
        }

        $deviationsSquares = array_map(function ($val) { return $val * $val; }, $this->deviationsOfMean($data));
        return array_sum($deviationsSquares) / ($dataCount - 1);
    }

    /**
     * Calculate "standard deviation" of the list of numbers
     *    This is just a square root of this list "variance"
     *
     * @param array $data
     * @return float
     */
    public function standardDeviation(array $data)
    {
        return sqrt($this->variance($data));
    }

    public function interquantileRange(array &$data, bool $needToSort = true)
    {
        if ($needToSort) {
            $this->sort($data);
        }
        return $this->quantile($data, 75, false) - $this->quantile($data, 25, false);
    }
}