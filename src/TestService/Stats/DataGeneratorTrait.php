<?php


namespace App\TestService\Stats;


trait DataGeneratorTrait
{
    /**
     * Get random number from normal distributed data
     *
     * @return float
     */
    public function getRandomNormal(): float
    {
        return $this->inverseNormalCDF(rand(0, 10000000) / 10000000);
    }

    /**
     * Generate normal distributed random data
     *
     * @param int $count
     * @return array
     */
    public function generateRandomNormalData(int $count = 1000): array
    {
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $data[] = $this->getRandomNormal();
        }
        return $data;
    }
}