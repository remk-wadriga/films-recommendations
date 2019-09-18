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
        $stDevs = []; // Standard deviations for each column
        for ($i = 0; $i < $colsNum; $i++) {
            $column = $this->getMatrixCol($matrix, $i, false);
            $means[] = $this->mean($column);
            $stDevs[] = $this->standardDeviation($column);
        }
        return [$means, $stDevs];
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
        list($means, $stDevs) = $this->scaleMatrix($matrix, $needToCheck);
        list($rowsNum, $colsNum) = $this->matrixShape($matrix, false);

        $rescaled = function (int $i, int $j) use ($matrix, $means, $stDevs) {
            return $stDevs[$j] > 0 ? ($matrix[$i][$j] - $means[$j]) / $stDevs[$j] : $matrix[$i][$j];
        };

        return $this->createMatrix($rescaled, $rowsNum, $colsNum);
    }


    /**
     * Check the matrix format - it must be and array of arrays
     * This function gets an array by reference and modify it - numerates the rows by numbers index from 0 to rows count
     *
     * @param array $matrix
     * @return int
     * @throws ServiceException
     */
    protected function checkMatrix(array &$matrix): int
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
    protected function checkMatrixRow(&$row, int $len = null)
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