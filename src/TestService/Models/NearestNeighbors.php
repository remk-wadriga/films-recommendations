<?php

namespace App\TestService\Models;

use App\TestService\Calculator\VectorsTrait;
use App\TestService\Entities\LabeledPointEntity;
use App\TestService\Entities\ListEntity;
use App\TestService\Entities\VectorEntity;

class NearestNeighbors
{
    use VectorsTrait;

    /**
     * Calculate elements count in array and return the most common element
     *    If where are a few "winners" in array - will be returned random one
     *
     * @param ListEntity $votes
     * @return mixed
     */
    public function rawMajorityVote(ListEntity $votes)
    {
        $mostPopular = $votes->mostCommon(1)[0];
        return $mostPopular[0];
    }

    /**
     * Calculate elements count in array and return the most common element
     *    If where are a few "winners" in array - will be returned the first (from begin of array)
     *    It is understood that data is ordered from the closest to the most distant
     *
     * @param ListEntity|array $votes
     * @param int $maxCount
     * @return string|null
     */
    public function majorityVote($votes, int $maxCount = 0): ?string
    {
        if (empty($votes)) {
            return null;
        }
        if ($maxCount === 0) {
            $votes = $votes->getSortedByCount();
            $maxCount = max($votes);
        }
        $winnersCount = 0;

        $winner = null;
        foreach ($votes as $voter => $count) {
            if ($count == $maxCount) {
                $winnersCount++;
                $maxCount = $count;
                $winner = $voter;
            }
        }
        unset($votes[$winner]);

        return $winnersCount === 1 ? $winner : $this->majorityVote($votes, $maxCount);
    }

    /**
     * Classify based on K nearest neighbors
     *
     * @param int $k
     * @param ListEntity $labeledPoints
     * @param VectorEntity $newPoint
     * @return string|null
     */
    public function knnClassify(int $k, ListEntity $labeledPoints, VectorEntity $newPoint): ?string
    {
        // Order the labeled points from nearest to farthest
        $sortedByDistance = $labeledPoints->toArray();
        usort($sortedByDistance, function (LabeledPointEntity $v1, LabeledPointEntity $v2) use ($newPoint) {
            return $this->vectorsDistance($v2->point, $newPoint) > $this->vectorsDistance($v1->point, $newPoint) ? -1: 1;
        });

        // Find the labels for the k closest
        $kNearestLabels = array_map(function (LabeledPointEntity $point) { return $point->label; }, $sortedByDistance);

        // and let them vote.
        return $this->majorityVote(new ListEntity(array_slice($kNearestLabels, 0, $k)));
    }


    public function randomPoint(int $dim): VectorEntity
    {
        $coordinates = [];
        for ($i = 0; $i < $dim; $i++) {
            $coordinates[] = rand(0, 1000000) / 1000000;
        }
        return new VectorEntity($coordinates);
    }

    public function randomDistances(int $dim, int $numPairs): ListEntity
    {
        $data = [];
        for ($i = 0; $i < $numPairs; $i++) {
            $data[] = $this->vectorsDistance($this->randomPoint($dim), $this->randomPoint($dim));
        }
        return new ListEntity($data);
    }
}