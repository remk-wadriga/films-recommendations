<?php

namespace App\TestService\Calculator;

use App\Exception\ServiceException;
use \Closure;

trait MatrixTrait
{
    public $randomMatrixMaxRowsNum = 20;
    public $randomMatrixMaxColsNum = 20;
    public $randomMatrixElemMaxVal = 100;

    /**
     * Calculate matrix rows and columns length. This function will return the array like this:
     *   [3, 4] where 3 - rows count, 4 - columns count
     *
     * @param array $matrix
     * @param bool $needToCheck
     * @return array
     * @throws ServiceException
     */
    public function matrixShape(array &$matrix, bool $needToCheck = true): array
    {
        $rowsNum = $needToCheck ? $this->checkMatrix($matrix) : count($matrix);
        $colsNum = $rowsNum > 0 ? count($matrix[0]) : 0;
        return [$rowsNum, $colsNum];
    }

    /**
     * Search the row in matrix by index and return it or throw exception if matrix has no row with given index
     *
     * @param array $matrix
     * @param int $row
     * @param bool $needToCheck
     * @return array
     * @throws ServiceException
     */
    public function getMatrixRow(array &$matrix, int $row, bool $needToCheck = true): array
    {
        $rowsNum = $needToCheck ? $this->checkMatrix($matrix) : count($matrix);
        if ($row >= $rowsNum) {
            throw new ServiceException(sprintf('This matrix has no row #%s', $row), ServiceException::CODE_INVALID_PARAMS);
        }
        return $matrix[$row];
    }

    /**
     * Search the column in matrix by index and return it or throw exception if matrix has no column with given index
     *
     * @param array $matrix
     * @param int $col
     * @param bool $needToCheck
     * @return array
     * @throws ServiceException
     */
    public function getMatrixCol(array &$matrix, int $col, bool $needToCheck = true): array
    {
        list(, $colsNum) = $this->matrixShape($matrix, $needToCheck);
        if ($col >= $colsNum) {
            throw new ServiceException(sprintf('This matrix has no col #%s', $col), ServiceException::CODE_INVALID_PARAMS);
        }
        return array_map(function ($row) use ($colsNum, $col, $needToCheck) {
            if ($needToCheck) {
                $this->checkMatrixRow($row, $colsNum);
            }
            return $row[$col];
        }, $matrix);
    }

    /**
     * Generate matrix with specific shape using callback-function
     *
     * @param Closure $factory
     * @param int $rowsNum
     * @param int $colsNum
     * @return array
     */
    public function createMatrix(Closure $factory, int $rowsNum, int $colsNum = 0): array
    {
        $matrix = [];
        if ($colsNum === 0) {
            $colsNum = $rowsNum;
        }
        for ($row = 0; $row < $rowsNum; $row++) {
            $matrix[$row] = [];
            for ($col = 0; $col < $colsNum; $col++) {
                $matrix[$row][$col] = $factory($row, $col, $matrix);
            }
        }
        return $matrix;
    }

    /**
     * Generate matrix with random elements and random or specified shape
     *
     * @param int $rowsNum
     * @param int $colsNum
     * @param int|null $maxElemVal
     * @param int $maxRowsNum
     * @param int $maxColsNum
     * @return array
     */
    public function createRandomMatrix(int $rowsNum = 0, int $colsNum = 0, int $maxElemVal = null, int $maxRowsNum = 0, int $maxColsNum = 0): array
    {
        if ($rowsNum === 0) {
            $rowsNum = rand(1, $maxRowsNum > 0 ? $maxRowsNum : rand(1, $this->randomMatrixMaxRowsNum));
        }
        if ($colsNum === 0) {
            $colsNum = rand(1, $maxColsNum > 0 ? $maxColsNum : rand(1, $this->randomMatrixMaxColsNum));
        }
        return $this->createMatrix(function () use ($maxElemVal) {
            return rand(0, $maxElemVal !== null ? $maxElemVal : $this->randomMatrixElemMaxVal);
        }, $rowsNum, $colsNum);
    }

    /**
     * Find the "matrix correlation", the correlation between each matrix columns
     *
     * @param array $matrix
     * @return array
     * @throws ServiceException
     */
    public function findMatrixCorrelation(array $matrix): array
    {
        $this->checkMatrix($matrix);
        list($rowsCount, $numsCount) = $this->matrixShape($matrix, false);

        $matrixEntry = function (int $i, int $j) use ($matrix) {
            return $this->correlation($this->getMatrixCol($matrix, $i, false), $this->getMatrixCol($matrix, $j, false));
        };

        return $this->createMatrix($matrixEntry, $numsCount, $numsCount);
    }

    /**
     * Get means and standard deviations for each matrix column
     *
     * @param array $matrix
     * @param bool $needToCheck
     * @return array
     * @throws ServiceException
     */
    public function scaleMatrix(array &$matrix, bool $needToCheck = true): array
    {
        list($rowsNum, $colsNum) = $this->matrixShape($matrix, $needToCheck);
        $means = []; // Means for each column
        $stDeviations = []; // Standard deviations for each column
        for ($i = 0; $i < $colsNum; $i++) {
            $column = $this->getMatrixCol($matrix, $i, false);
            $means[] = $this->mean($column);
            $stDeviations[] = $this->standardDeviation($column);
        }
        return [$means, $stDeviations];
    }

    /**
     * "Rescale matrix" - make a new matrix with scaled values. For example it's used when we need to skip the measure
     *
     * @param array $matrix
     * @param bool $needToCheck
     * @return array
     * @throws ServiceException
     */
    public function rescaleMatrix(array &$matrix, bool $needToCheck = true): array
    {
        list($means, $stDeviations) = $this->scaleMatrix($matrix, $needToCheck);
        list($rowsNum, $colsNum) = $this->matrixShape($matrix, false);

        $rescaled = function (int $i, int $j) use ($matrix, $means, $stDeviations) {
            return $stDeviations[$j] > 0 ? ($matrix[$i][$j] - $means[$j]) / $stDeviations[$j] : $matrix[$i][$j];
        };

        return $this->createMatrix($rescaled, $rowsNum, $colsNum);
    }

    /**
     * Center the matrix, i.e. subtract the average
     *
     * @param array $matrix
     * @param bool $needToCheck
     * @return array
     * @throws ServiceException
     */
    public function deMeanMatrix(array &$matrix, bool $needToCheck = true): array
    {
        list($rowsNum, $colsNum) = $this->matrixShape($matrix, $needToCheck);
        list($means, $deviations) = $this->scaleMatrix($matrix, false);
        return $this->createMatrix(function ($i, $j) use ($matrix, $means) {
            return $matrix[$i][$j] - $means[$j];
        }, $rowsNum, $colsNum);
    }

    /**
     * Find the variance of data in the direction "direction"
     *
     * @param array $v
     * @param array $direction
     * @param bool $needToCheck
     * @return float
     * @throws ServiceException
     */
    public function matrixDirectionalVariance(array $v, array $direction, bool $needToCheck = true): float
    {
        if ($needToCheck) {
            $this->checkMatrix($v);
        }
        $result = 0;
        foreach ($v as $row) {
            $result += pow($this->vectorsScalarMultiply($row, $this->vectorDirection($direction)), 2);
        }
        return $result;
    }

    /**
     * Find the gradient of directional data variance
     *
     * @param array $v
     * @param array $direction
     * @param bool $needToCheck
     * @return array
     * @throws ServiceException
     */
    public function matrixDirectionalVarianceGradient(array $v, array $direction, bool $needToCheck = true): array
    {
        if (empty($v)) {
            return [];
        }

        $dLength = count($direction);
        list($rowsNum, $colsNum) = $this->matrixShape($v, $needToCheck);
        if ($dLength !== $colsNum) {
            throw new ServiceException('Vector "direction" must have the same length that the length of matrix row to calculate the gradient of directional data variance', ServiceException::CODE_INVALID_PARAMS);
        }

        $result = [];

        $WDir = $this->vectorDirection($direction);
        for ($i = 0; $i < $dLength; $i++) {
            if (!is_numeric($i)) {
                throw new ServiceException('Vector "direction" must be an array of numbers to calculate the gradient of directional data variance', ServiceException::CODE_INVALID_PARAMS);
            }
            if (!isset($result[$i])) {
                $result[$i] = 0;
            }
            foreach ($v as $row) {
                if (!isset($row[$i])) {
                    continue;
                }
                $result[$i] += 2 * $this->vectorsScalarMultiply($row, $WDir) * $row[$i];
            }
        }

        return $result;
    }

    /**
     * Find the "First principal component"
     *    "First principal component" - direction that maximizes the function of directional dispersion (https://en.wikipedia.org/wiki/Principal_component_analysis)
     *
     * @param array $v
     * @param int $n
     * @param float $stepSIze
     * @param bool $needToCheck
     * @return array
     * @throws ServiceException
     */
    public function firstPrincipalComponent(array &$v, int $n = 100, float $stepSIze = 0.1, bool $needToCheck = true): array
    {
        if (empty($v)) {
            return [];
        }
        list($rowsNum, $colsNum) = $this->matrixShape($v, $needToCheck);

        // Start with a random guess
        $guess = [];
        for ($i = 0; $i < $colsNum; $i++) {
            $guess[] = 1;
        }

        for ($i = 0; $i < $n; $i++) {
            $gradient = $this->matrixDirectionalVarianceGradient($v, $guess, false);
            $guess = $this->step($guess, $gradient, $stepSIze);
        }

        return $this->vectorDirection($guess);
    }

    /**
     * Remove projection from data
     *
     * @param array $matrix
     * @param array $projection
     * @param bool $needToCheck
     * @return array
     * @throws ServiceException
     */
    public function removeProjection(array &$matrix, array $projection, bool $needToCheck = true): array
    {
        list($rowsNum, $colsNum) = $this->matrixShape($matrix, $needToCheck);
        if ($colsNum !== count($projection)) {
            throw new ServiceException('The length of vector "projection" must be equals to length of matrix row to remove the direction from matrix', ServiceException::CODE_INVALID_PARAMS);
        }

        $result = [];
        foreach ($matrix as $row) {
            $result[] = $this->removeProjectionFromVector($row, $projection);
        }

        return $result;
    }

    /**
     * Find the "numComponents" count of "principal component" for data "matrix"
     *
     * @param array $matrix
     * @param int $numComponents
     * @param bool $needToCheck
     * @return array
     * @throws ServiceException
     */
    public function principalComponentAnalysis(array &$matrix, int $numComponents, bool $needToCheck = true): array
    {
        if ($needToCheck) {
            $this->checkMatrix($matrix);
        }
        $data = $matrix;
        $components = [];
        for ($i = 0; $i < $numComponents; $i++) {
            $component = $this->firstPrincipalComponent($data, 100, 0.1, false);
            $components[] = $component;
            $data = $this->removeProjection($data, $component);
        }
        unset($data);
        return $components;
    }

    /**
     * Transform data "matrix" to space covered by smaller components
     *
     * @param array $matrix
     * @param array $components
     * @param bool $needToCheck
     * @return array
     * @throws ServiceException
     */
    public function transformMatrix(array &$matrix, array $components, bool $needToCheck = true): array
    {
        if ($needToCheck) {
            $this->checkMatrix($matrix);
        }
        $result = [];
        foreach ($matrix as $row) {
            $result[] = $this->transformVector($row, $components);
        }
        return $result;
    }


    /**
     * Check the matrix format - it must be and array of arrays
     * This function gets an array by reference and modify it - numerates the rows by numbers index from 0 to rows count
     *
     * @param array $matrix
     * @return int
     * @throws ServiceException
     */
    private function checkMatrix(array &$matrix): int
    {
        if (empty($matrix)) {
            return 0;
        }
        if (!array_key_exists(0, $matrix)) {
            $matrix = array_values($matrix);
        }
        $rowLength = count($matrix[0]);
        foreach ($matrix as $index => $row) {
            $this->checkMatrixRow($row, $rowLength);
            $matrix[$index] = $row;
        }
        return count($matrix);
    }

    /**
     * Check the matrix row format - it must be an array.
     * If second param ("len" - row elements count) is specified, check is this row has a correct count of elements
     * This function gets an array by reference and modify it - numerates the elements by numbers index from 0 to elements count
     *
     * @param mixed $row
     * @param int|null $len
     * @throws ServiceException
     */
    private function checkMatrixRow(&$row, int $len = null)
    {
        if (!is_array($row)) {
            throw new ServiceException('Each row of matrix mas be an array', ServiceException::CODE_INVALID_PARAMS);
        }
        $rowLen = count($row);
        if ($len !== null && $rowLen !== $len) {
            throw new ServiceException(sprintf('Length of each matrix row must be equals to %s', $len), ServiceException::CODE_INVALID_PARAMS);
        }
        if ($rowLen > 0 && !array_key_exists(0, $row)) {
            $row = array_values($row);
        }
    }
}