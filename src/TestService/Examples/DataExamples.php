<?php


namespace App\TestService\Examples;


use App\TestService\Calculator\MatrixTrait;
use App\TestService\Calculator\StatisticsTrait;
use App\TestService\Calculator\VectorsTrait;
use App\TestService\Stats\DataIndexerTrait;

class DataExamples
{
    use DataIndexerTrait;
    use VectorsTrait;
    use MatrixTrait;
    use StatisticsTrait;

    public function illustrateScaling()
    {
        $user_A = ['height_inch' => 63, 'height_centimeters' => 160, 'weight_pounds' => 150];
        $user_B = ['height_inch' => 67, 'height_centimeters' => 170.2, 'weight_pounds' => 160];
        $user_C = ['height_inch' => 70, 'height_centimeters' => 177.8, 'weight_pounds' => 171];

        $A_B_inch = $this->vectorsDistance([$user_A['height_inch'], $user_A['weight_pounds']], [$user_B['height_inch'], $user_B['weight_pounds']]); // 10.77
        $A_C_inch = $this->vectorsDistance([$user_A['height_inch'], $user_A['weight_pounds']], [$user_C['height_inch'], $user_C['weight_pounds']]); // 22.14
        $B_C_inch = $this->vectorsDistance([$user_B['height_inch'], $user_B['weight_pounds']], [$user_C['height_inch'], $user_C['weight_pounds']]); // 11.40

        $A_B_centimeters = $this->vectorsDistance([$user_A['height_centimeters'], $user_A['weight_pounds']], [$user_B['height_centimeters'], $user_B['weight_pounds']]); // 14.28
        $A_C_centimeters = $this->vectorsDistance([$user_A['height_centimeters'], $user_A['weight_pounds']], [$user_C['height_centimeters'], $user_C['weight_pounds']]); // 27.53
        $B_C_centimeters = $this->vectorsDistance([$user_B['height_centimeters'], $user_B['weight_pounds']], [$user_C['height_centimeters'], $user_C['weight_pounds']]); // 13.37

        // Как видим, "квклидово расстояние" отличается для юзеров в зависимости от единиц измерения роста
        // Стандартиризуем данные, чтобы извабиться от единиц измерения
        $dataMatrix = [$user_A, $user_B, $user_C];
        list($user_A, $user_B, $user_C) = $this->rescaleMatrix($dataMatrix);

        // Посчитаем расстояния снова
        $A_B_0 = $this->vectorsDistance([$user_A[0], $user_A[2]], [$user_B[0], $user_B[2]]); // 1.48
        $A_C_0 = $this->vectorsDistance([$user_A[0], $user_A[2]], [$user_C[0], $user_C[2]]); // 2.82
        $B_C_0 = $this->vectorsDistance([$user_B[0], $user_B[2]], [$user_C[0], $user_C[2]]); // 1.35

        $A_B_1 = $this->vectorsDistance([$user_A[1], $user_A[2]], [$user_B[1], $user_B[2]]); // 1.48
        $A_C_1 = $this->vectorsDistance([$user_A[1], $user_A[2]], [$user_C[1], $user_C[2]]); // 2.82
        $B_C_1 = $this->vectorsDistance([$user_B[1], $user_B[2]], [$user_C[1], $user_C[2]]); // 1.35

        // Теперь арсстояние между векторами от единиц не зависит

        dd([$A_B_0, $A_C_0, $B_C_0], [$A_B_1, $A_C_1, $B_C_1]);
    }
}