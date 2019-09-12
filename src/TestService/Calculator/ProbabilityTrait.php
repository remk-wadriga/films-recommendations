<?php


namespace App\TestService\Calculator;

trait ProbabilityTrait
{
    private function getRandomKid()
    {
        return rand(0, 1) === 0 ? 'boy' : 'girl';
    }

    // Иллюстрация "парадокса мальчика и девочки"
    // В семье два ребёнка, пол одного из них не зависит от пола другого.
    // Вероятсность того, что Обра ребёнка девочки при условии, что старшая девочка - 1/2
    // Вероятсность того, что Обра ребёнка девочкипри при условии, что как минимум одна из них девочка - 1/3
    // Нужно для понимания "условной вероятности" (P(E|F) - вероятность E при условии, что известно F):
    // P(E|F) = P(E,F) / P(F)
    public function illustrateChildrenParadox()
    {
        $bothGirls = 0;
        $olderGirl = 0;
        $eitherGirl = 0;
        for ($i = 0; $i < 1000; $i++) {
            $younger = $this->getRandomKid();
            $older = $this->getRandomKid();
            if ($older == 'girl') { // старшая девочка - 1/2
                $olderGirl++;
            }
            if ($older == 'girl' && $younger == 'girl') { // Обе девочки - 1/4
                $bothGirls++;
            }
            if ($older == 'girl' || $younger == 'girl') { // кто-то их них девочка - 3/4
                $eitherGirl++;
            }
        }
        echo 'P(обе | старшая): ' . ($bothGirls / $olderGirl), '<br />'; // Обе девочки при условии, что старшая девочка - 1/2
        echo 'P(обе | любая): ' . ($bothGirls / $eitherGirl); // Обе девочки при условии, что как минимум одна из них девочка - 1/3
        exit();
    }

    // Иллюстрация проверки сбалансированнсти монетки
    // допстим, решено сделать 1000 бросков. Если гипотеза о уравновешенности правильная, то случайная величина X
    // должна быть распределена приближённо нормально со средним значением 500 (mu0) и стандартным отклонением 15.8 (sigma0).
    // Мы готовы ошибочно отклонить правильную величину H0 (нулевая гипотеза) в 5-ти процентах случаев.
    public function illustrateCoinBalanceCheck()
    {
        list($mu0, $sigma0) = $this->normalApproximationToBinomial(1000, 0.5); // 500, 15.8

        // -- Проверка "ошибки 1-го рода" ---
        // Двусторонние границы нормальной случайной величины
        // Если H0 правильная, то вероятность того, что X будет лежать за пределами данных границ - 5%
        // 95% границы при условии, что p = 0.5
        list($lo1, $h1) = $this->normalTwoSidesBounds(0.95, $mu0, $sigma0); // [469, 531]

        // -- Проверка "ошибки 2-го рода" ---
        // Проверим, что случится, если p = 0.55
        // Фактические mu и sigma при p = 0.55
        list($mu1, $sigma1) = $this->normalApproximationToBinomial(1000, 0.55); // 500, 15.8

        // Ошибка 2-го рода означает "не удалось отклонить нулевую гипотезу"
        // Это происходит, когда X всё ещё внутри первоначального интервала
        $type21Probability = $this->normalProbabilityBetween($lo1, $h1, $mu1, $sigma1);
        // Моцность проверки (вероятность не допустить ошибку 2-го рода):
        $power21 = 1 - $type21Probability; // 0.887

        // Теперь проверим, что будет, если p <= 0.5
        // В этом случае проверка будет отклонять гипотезу, когда  X > 500 и не будет, когда X < 500
        $h2 = $this->normalUpperBound(0.95, $mu0, $sigma0); // 526 (меньше 531, поскольку нужно больше вероятности в верхнем хвосте)
        // Вероятность ошибки второго рода
        $type22Probability = $this->normalProbabilityBelow($h2, $mu1, $sigma1);
        // Мощность проверки
        $power22 = 1 - $type22Probability; // 0.936
        // Мощность второй проверки выше, потому что вместо того, чтобы отвергать H0 когда X < 469 (что врядли вообще произойдёт, если H1 верна),
        // Отвергаем H1, когда X лежит между 526 и 531 (что вполне возможно, если H1 верна).

        dd($power22);
    }

    // Двусторонняя проверка гипотезы об уравновершенности монеты (с использованием P-"значений")
    // Асльтернативный вариант оценки мощности проверки
    public function illustrateCoinBalanceCheckAlternative()
    {
        list($mu0, $sigma0) = $this->normalApproximationToBinomial(1000, 0.5); // 500, 15.8

        // Двухстороннее P-значение при выпадении 530-ти орлов:
        // 529.5 а не 530, потому что вызов normalProbabilityBetween(529.5, 531.5) даёт более точный резульат, чем normalProbabilityBetween(531, 532)
        $twoP1 = $this->twoSidedPValue(529.5, $mu0, $sigma0); // 0.062


        // Проведём модельное испытание
        /*$extremeValuesCount = 0; // Число предельных значений
        for ($i = 0; $i < 100000; $i++) {
            // Подсчитаем количество оролов при 1000 бросков
            $numHeads = 0;
            for ($j = 0; $j < 1000; $j++) {
                if (rand(0, 1) === 0) {
                    $numHeads++;
                }
            }
            if ($numHeads <= 470 || $numHeads >= 530) {
                $extremeValuesCount++;
            }
        }
        dd($extremeValuesCount / 100000); // ~0.062*/

        // Поскльку P-значение превышает заданный 5%-й уровень значимости, то H0 не опровергается.
        // И, напротив, при выпадении 532-х орлов P-значение будет:
        $twoP2 = $this->twoSidedPValue(531.5, $mu0, $sigma0); // 0.046
        // 0.046 меньше 5%-го уровня значимости, поэтому в данном случае H0 будет отвергнута.

        // Для проведения односторонней проверки выпадения 525-ти орлов вызываем
        $upperP1 = $this->upperPValue(524.5, $mu0, $sigma0); // 0.060
        // И, следовательно, H0 не будет опровернута

        // При выпадении 527-и орлов получим:
        $upperP2 = $this->upperPValue(526.5, $mu0, $sigma0); // 0.047
        // И, H0 будет опровернута
        dd($upperP2);
    }


    /**
     * Calculate "PDF" (probability density function) for number
     *
     * @param float $x
     * @return int
     */
    public function uniformPDF(float $x)
    {
        return $x >= 0 && $x < 1 ? 1 : 0;
    }

    /**
     * Calculate "CDF" (cumulative distribution function) for number
     *
     * @param float $x
     * @return float
     */
    public function uniformCDF(float $x): float
    {
        if ($x < 0) {
            return 0;
        } elseif ($x < 1) {
            return $x;
        } else {
            return 1;
        }
    }

    /**
     * Calculate "sgn" of number
     *    * Signum function (https://en.wikipedia.org/wiki/Sgn)
     *
     * @param float $x
     * @return int
     */
    public function sgn(float $x): int
    {
        if ($x > 0) {
            return 1;
        } elseif ($x == 0) {
            return 0;
        } else {
            return -1;
        }
    }

    /**
     * Calculate "erf" of number
     *    * Error function (https://en.wikipedia.org/wiki/Error_function)
     *
     * @param float $x
     * @return float
     */
    public function erf(float $x): float
    {
        $pi = M_PI;
        $a = (8 * ($pi - 3)) / (3 * $pi * (4 - $pi));
        $px = pow($x, 2);
        $apx = $a * $px;
        return $this->sgn($x) * sqrt(1 - exp(-$px * (4 / $pi + $apx) / (1 + $apx)));
    }

    /**
     * Calculate normal distribution for number
     *
     * @param float $x
     * @param float $mu
     * @param float $sigma
     * @return float
     */
    public function normalPDF(float $x, float $mu = 0, float $sigma = 1)
    {
        $sqrt2pi = sqrt(2 * M_PI);
        return exp(-pow($x - $mu, 2) / 2 / pow($sigma, 2)) / ($sqrt2pi * $sigma);
    }

    /**
     * Calculate "CDF" for normal distribution of number
     *    * Cumulative distribution function (https://en.wikipedia.org/wiki/Cumulative_distribution_function)
     *
     * @param float $x
     * @param float $mu
     * @param float $sigma
     * @return float;
     */
    public function normalCDF(float $x, float $mu = 0, float $sigma = 1)
    {
        return (1 + $this->erf(($x - $mu) / sqrt(2) / $sigma)) / 2;
    }

    /**
     * Find the approximate inverse of normal CDF using "binary search"
     *    * Binary search (https://en.wikipedia.org/wiki/Binary_search_algorithm)
     *
     * @param float $p
     * @param float $mu
     * @param float $sigma
     * @param float $tolerance
     * @return float|int
     */
    public function inverseNormalCDF(float $p, float $mu = 0, float $sigma = 1, float $tolerance = 0.00001)
    {
        // If this is not a standard distribution - standardize
        if ($mu != 0 || $sigma != 1) {
            return $mu + $sigma * $this->inverseNormalCDF($p, 0, 1, $tolerance);
        }

        $lowZ = -10; // normalCDF(-10) is very close to 0
        $hiZ = 10; // normalCDF(10) is very close to 1

        $midZ = 0;
        $i = 0;
        while ($hiZ - $lowZ > $tolerance) {
            $midZ = ($lowZ + $hiZ) / 2; // Get the middle
            $midP = $this->normalCDF($midZ); // adn the "CDF" in of this point
            if ($midP < $p) {
                // The value of the middle point is very low, keep searching up...
                $lowZ = $midZ;
            } else {
                $hiZ = $midZ;
            }
            $i++;
        }

        return $midZ;
    }

    /**
     * Calculate "bernoulli variance" for some number between 0 and 1
     *  * Bernoulli variance (https://en.wikipedia.org/wiki/Bernoulli_trial)
     *
     * @param float $p
     * @return int
     */
    public function bernoulliTrial(float $p): int
    {
        return $p > (rand(0, 10000000) / 10000000) ? 1 : 0;
    }

    /**
     * Calculate "binomial distribution" for number $p (between 0 and 1) in range $n
     *    * Binomial distribution (https://en.wikipedia.org/wiki/Binomial_distribution)
     *
     * @param int $n
     * @param float $p
     * @return int
     */
    public function binomial(int $n, float $p): int
    {
        $res = 0;
        for ($i = 0; $i < $n; $i++) {
            $res += $this->bernoulliTrial($p);
        }
        return $res;
    }

    /**
     * Approximation of binomial random value by normal distribution
     *    * Find the "mu" and "sigma" which correspond to binomial(n, p)
     *    * Will return the array like this: [mu, sigma]
     *
     * @param int $n
     * @param float $p
     * @return array
     */
    public function normalApproximationToBinomial(int $n, float $p): array
    {
        $mu = $n * $p;
        $sigma = sqrt($p * (1 - $p) * $n);
        return [$mu, $sigma];
    }

    /**
     * Calculate the probability that value of normal random value is below that some number
     *
     * @param float $hi
     * @param float $mu
     * @param float $sigma
     * @return float
     */
    public function normalProbabilityBelow(float $hi, float $mu = 0, float $sigma = 1)
    {
        return $this->normalCDF($hi, $mu, $sigma);
    }

    /**
     * Calculate the probability that value of normal random value is above that some number is it's not below
     *    * The probability that normal value is is below of some number: normal_probability_below = normal_cdf
     *
     * @param float $lo
     * @param float $mu
     * @param float $sigma
     * @return float|int
     */
    public function normalProbabilityAbove(float $lo, float $mu = 0, float $sigma = 1)
    {
        return 1 - $this->normalCDF($lo, $mu, $sigma);
    }

    /**
     * Calculate the probability that value of normal random value is between of two numbers
     *    if it's below than hi and not below that lo
     *
     * @param float $lo
     * @param float $hi
     * @param float $mu
     * @param float $sigma
     * @return float
     */
    public function normalProbabilityBetween(float $lo, float $hi, float $mu = 0, float $sigma = 1)
    {
        return $this->normalCDF($hi, $mu, $sigma) - $this->normalCDF($lo, $mu, $sigma);
    }

    /**
     * Calculate the probability that value of normal random value is outside of two numbers
     *    if it's not between of them
     *
     * @param float $lo
     * @param float $hi
     * @param float $mu
     * @param float $sigma
     * @return float
     */
    public function normalProbabilityOutside(float $lo, float $hi, float $mu = 0, float $sigma = 1)
    {
        return 1 - $this->normalProbabilityBetween($lo, $hi, $mu, $sigma);
    }

    /**
     * The upper limit of normal value
     *    Will return z for that P(Z <= z) = probability
     *
     * @param float $probability
     * @param float $mu
     * @param float $sigma
     * @return float|int
     */
    public function normalUpperBound(float $probability, float $mu = 0, float $sigma = 1)
    {
        return $this->inverseNormalCDF($probability, $mu, $sigma);
    }

    /**
     * The lower limit of normal value
     *    Will return z for that P(Z >= z) = probability
     *
     * @param float $probability
     * @param float $mu
     * @param float $sigma
     * @return float|int
     */
    public function normalLowerBound(float $probability, float $mu = 0, float $sigma = 1)
    {
        return $this->inverseNormalCDF(1 - $probability, $mu, $sigma);
    }

    /**
     * Two-sides limits of normal value.
     *    WIll return symmetric (around average) limits within which the specified probability is contained
     *
     * @param float $probability
     * @param float $mu
     * @param float $sigma
     * @return array
     */
    public function normalTwoSidesBounds(float $probability, float $mu = 0, float $sigma = 1): array
    {
        $tailProbability = (1 - $probability) / 2;

        // The upper limit must have value of tail probability. Tail probability is upper than it
        $upperBound = $this->normalLowerBound($tailProbability, $mu, $sigma);

        // The lower limit must have value of tail probability. Tail probability is lower than it
        $lowerBound = $this->normalUpperBound($tailProbability, $mu, $sigma);

        return [$lowerBound, $upperBound];
    }

    /**
     * Upper limit of "P-value"
     *
     * @param float $x
     * @param float $mu
     * @param float $sigma
     * @return float|int
     */
    public function upperPValue(float $x, float $mu = 0, float $sigma = 1)
    {
        return $this->normalProbabilityAbove($x, $mu, $sigma);
    }

    /**
     * Lower limit of "P-value"
     *
     * @param float $x
     * @param float $mu
     * @param float $sigma
     * @return float|int
     */
    public function lowerPValue(float $x, float $mu = 0, float $sigma = 1)
    {
        return $this->normalProbabilityBelow($x, $mu, $sigma);
    }

    /**
     * Get two sided "P-value"
     *
     * @param float $x
     * @param float $mu
     * @param float $sigma
     * @return float|int
     */
    public function twoSidedPValue(float $x, float $mu = 0, float $sigma = 1)
    {
        return $x >= $mu ? 2 * $this->normalProbabilityAbove($x, $mu, $sigma) : 2 * $this->normalProbabilityBelow($x, $mu, $sigma);
    }
}