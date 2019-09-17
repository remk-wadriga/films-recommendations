<?php


namespace App\TestService\Examples;

use App\TestService\Calculator\ProbabilityTrait;
use App\TestService\Stats\DataGeneratorTrait;
use App\TestService\Stats\DataIndexerTrait;

class StatisticsExamples
{
    use ProbabilityTrait;
    use DataIndexerTrait;
    use DataGeneratorTrait;

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

    // Если истинное значение вероятности p незветсно,
    // а налблюдения дают p = 0.525, то мы можем найти "доверительный интервал"
    public function illustrateFindingConfidentialInterval()
    {
        /*// На 1000 бросков выпало 525 "орлов"
        $pHat1 = 525 / 1000;
        $mu1 = $pHat1;
        $sigma1 = sqrt($pHat1 * (1 - $pHat1) / 1000); // 0.0158
        // С уверенностью 95% следующий интервал содержит истинное значение p:
        list($lo1, $hi1) = $this->normalTwoSidesBounds(0.95, $mu1, $sigma1); // [0.494, 0.556]
        // Так как 0.5 лежит в пределах "доверительного интервала", то мы можем сделать вывод о том, что монета уравновешена.*/

        /*// А если бы на 1000 бросков выпало 540 орлов, то получилось бы:
        $pHat2 = 540 / 1000;
        $mu2 = $pHat2;
        $sigma2 = sqrt($pHat2 * (1 - $pHat2) / 1000); // 0.0158
        list($lo2, $hi2) = $this->normalTwoSidesBounds(0.95, $mu2, $sigma2); // [0.509, 0.571]
        // Здесь утверждение, что "монета уравновешена" не лежит в "доверительном интервале", следовательно гипотеза не проходит проверки

        dd($lo2, $hi2);*/

        // Процедура, которая в 5% случаев ошибочно опровергает нулевую гипотезу, должна в 5% случаев её ошибочноо провергать.
        // Проведём эксперимент

        $rejections = 0;
        for ($i = 0; $i < 1000; $i++) {
            $heads = 0;
            for ($j = 0; $j < 1000; $j++) {
                if (rand(0, 1) === 0) {
                    $heads++;
                }
            }
            // Проверим количество "орлов", исользуя 5%-й уровень значимости:
            if ($heads < 469 || $heads > 531) {
                $rejections++;
            }
        }
        // Количество отклонение ~= 50

        dd($rejections / 1000); // ~=0.05
    }

    // У нас 2 рекламных банера: A b B
    // Поптаемся на основании статистики кликов выяснить, каой из банеров эффективнее
    public function illustrateABTesting()
    {
        $views = 1000;

        // 1. A получает 200 кликов на 1000 просмотров, а B - 180
        $AClicks1 = 200;
        $BClicks1 = 180;
        $z1 = $this->A_B_TestStatistics($views, $AClicks1, $views, $BClicks1);
        $PVal1ue1 = $this->twoSidedPValue($z1);

        // 2. A получает 200 кликов на 1000 просмотров, а B - 150
        $AClicks2 = 200;
        $BClicks2 = 150;
        $z2 = $this->A_B_TestStatistics($views, $AClicks2, $views, $BClicks2);
        $PVal1ue2 = $this->twoSidedPValue($z2);

        $textPattern = 'Ввероятность получить распределение кликов %s/%s на %s просмотров: %s';
        echo sprintf($textPattern, $AClicks1, $BClicks1, $views, $PVal1ue1);
        echo '<br />';
        echo sprintf($textPattern, $AClicks2, $BClicks2, $views, $PVal1ue2);
        exit();
    }

    public function generateUniformAndNormalDistributedData()
    {
        $uniform = [];
        $normal = [];
        for ($i = 0; $i < 10000; $i++) {
            $uniform[] = rand(-100, 100);
            $normal[] = 57 * $this->inverseNormalCDF(rand(0, 1000000) / 1000000);
        }

        $countedUniform = [];
        $countedNormal = [];
        foreach ($this->makeHistogram($uniform, 10) as $value => $count) {
            $countedUniform[] = ['name' => $value, 'count' => $count];
        }
        foreach ($this->makeHistogram($normal, 10) as $value => $count) {
            $countedNormal[] = ['name' => $value, 'count' => $count];
        }

        usort($countedUniform, function ($val1, $val2) {
            return $val2['name'] > $val1['name'] ? -1 : 1;
        });
        usort($countedNormal, function ($val1, $val2) {
            return $val2['name'] > $val1['name'] ? -1 : 1;
        });

        dd($countedUniform, $countedNormal);
    }


    private function estimatedParameters($N, $n)
    {
        $p = $n / $N;
        $sigma = sqrt($p * (1 - $p) / $N);
        return [$p, $sigma];
    }

    private function A_B_TestStatistics($Na, $na, $Nb, $nb)
    {
        list($pA, $sigmaA) = $this->estimatedParameters($Na, $na);
        list($pB, $sigmaB) = $this->estimatedParameters($Nb, $nb);
        return ($pB - $pA) / sqrt(pow($sigmaA, 2) + pow($sigmaB, 2));
    }
}