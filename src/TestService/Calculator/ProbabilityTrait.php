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
        $pi = pi();
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
        $sqrt2pi = sqrt(2 * pi());
        return (exp(-pow($x - $mu, 2) / (2 * $sigma))) / ($sqrt2pi * $sigma);
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
        return (1 + $this->erf($x - $mu) / sqrt(2) / $sigma) / 2;
    }
}