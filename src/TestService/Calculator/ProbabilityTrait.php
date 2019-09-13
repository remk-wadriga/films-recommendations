<?php


namespace App\TestService\Calculator;

trait ProbabilityTrait
{
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
     * Calculate "gamma" for number
     *
     * @param float $x
     * @return float|int
     */
    public function gamma(float $x)
    {
        $gamma = 1;
        for($i = 1; $i < $x-1; $i++) {
            $gamma += $i * $gamma;
        }
        return $gamma;
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

    /**
     * Calculate "PDF" for "beta-distribution"
     *    * Beta-distribution (https://en.wikipedia.org/wiki/Beta_distribution)
     *
     * @param float $x
     * @param float $alpha
     * @param float $beta
     * @return float|int
     */
    public function getBetaPDF(float $x, float $alpha = 0, float $beta = 1)
    {
        if ($x < 0 || $x > 1) {
            return 0;
        }
        $normalizationConstant = $this->getBetaNormalizationConstant($alpha, $beta);
        return $normalizationConstant != 0 ? pow($x, $alpha - 1) * pow(1 - $x, $beta - 1) / $normalizationConstant : 0;
    }


    public function getBetaNormalizationConstant(float $alpha = 0, float $beta = 1)
    {
        return $this->gamma($alpha) * $this->gamma($beta) / $this->gamma($alpha + $beta);
    }
}