<?php


namespace App\TestService;

use App\TestService\Stats\UserEntity;
use App\Exception\ServiceException;
use App\TestService\Stats\FileReaderTrait;
use App\TestService\Stats\DataIndexerTrait;
use App\TestService\Stats\FinderTrait;

class StatsService extends AbstractTestService
{
    use FileReaderTrait;
    use DataIndexerTrait;
    use FinderTrait;

    /**
     *  Calculate average salaries for user's tenures or tenures periods (when "tenures" argument is
     *   something like this: ['< 2', '< 5', '> 5'], the result will looks like ['< 2' => 48000.00, '< 5' => 61500.00, '> 5' => 79166.67])
     *
     * @param array $tenures
     * @return array
     */
    public function calculateAverageSalariesForTenures(array $tenures = [])
    {
        $indexedSalaries = $this->getSalariesIndexedByTenures($tenures);
        $result = [];
        foreach ($indexedSalaries as $tenure => $salaries) {
            $result[$tenure] = !empty($salaries) ? array_sum($salaries) / count($salaries) : 0;
        }
        return $result;
    }

    /**
     * Calculate "Degree Centrality": the more user has friends the closer he is to the "centre"
     *
     * @param bool $desc
     * @return UserEntity[]
     * @throws ServiceException
     */
    public function getUsersSortedByFiendsCount(bool $desc = false)
    {
        $list = $this->getUsers();
        usort($list, function (UserEntity $userI, UserEntity $userJ) use ($desc) {
            $res = $desc ? $userI->friendsCount > $userJ->friendsCount : $userJ->friendsCount > $userI->friendsCount;
            return $res ? -1 : 1;
        });
        return $list;
    }

    /**
     * Get users list sorted by active minutes
     *
     * @param bool $desc
     * @return UserEntity[]
     * @throws ServiceException
     */
    public function getUsersSortedByActiveMinutes(bool $desc = false)
    {
        $list = $this->getUsers();
        usort($list, function (UserEntity $userI, UserEntity $userJ) use ($desc) {
            $res = $desc ? $userI->activeMinutes > $userJ->activeMinutes : $userJ->activeMinutes > $userI->activeMinutes;
            return $res ? -1 : 1;
        });
        return $list;
    }

    /**
     * Calculate users count for each specific friends count (in other words "How much users has 3 friends, 5 friends, 10 friends etc")
     *    This function will return the array luke that:
     *    [
     *       [
     *          "friends": 3,
     *          "users": 10,
     *       ],
     *       [
     *          "friends": 7,
     *          "users": 4,
     *       ],
     *       ...
     *    ]
     *   The array will be sorted by friends count
     *
     * @param bool $desc
     * @return array
     * @throws ServiceException
     */
    public function getUsersCountSortedByFriendsCount($desc = false)
    {
        $data = [];
        foreach ($this->getUsersSortedByFiendsCount($desc) as $user) {
            if (!isset($data[$user->friendsCount])) {
                $data[$user->friendsCount] = [
                    'friends' => $user->friendsCount,
                    'users' => 0
                ];
            }
            $data[$user->friendsCount]['users']++;
        }
        return array_values($data);
    }

    /**
     * Calculate frequency for words in users interests
     *
     * @param array $words
     * @return mixed
     */
    public function getInterestsWordsCountsSortedByCount(array $words = [])
    {
        $list = $this->getInterestsWordsCountsIndexedByWords($words);
        uasort($list, function ($countI, $countJ) {
            return $countI > $countJ ? -1 : 1;
        });
        return $list;
    }

    /**
     * Calculate "normal distribution" for some range of numbers
     *
     * @param array $range
     * @param float $mu
     * @param float $sigma
     * @return array
     */
    public function getNormalDistribution(array $range = [], float $mu = 0, float $sigma = 1):array
    {
        if (!isset($range[0])) {
            $range[0] = -5;
        }
        if (!isset($range[1])) {
            $range[1] = 5;
        }
        list($from, $to) = [$range[0] * 10, $range[1] * 10];

        $result = [];
        for ($i = $from; $i <= $to; $i++) {
            $x = $i / 10;
            $result[] = ['index' => $x, 'value' => $this->calc->normalPDF($x, $mu, $sigma)];
        }

        return $result;
    }

    /**
     * Calculate "CDF" for normal distribution of some range of numbers
     *
     * @param array $range
     * @param float $mu
     * @param float $sigma
     * @return array
     */
    public function getNormalCDF(array $range = [], float $mu = 0, float $sigma = 1):array
    {
        if (!isset($range[0])) {
            $range[0] = -5;
        }
        if (!isset($range[1])) {
            $range[1] = 5;
        }
        list($from, $to) = [$range[0] * 10, $range[1] * 10];

        $result = [];
        for ($i = $from; $i <= $to; $i++) {
            $x = $i / 10;
            $result[] = ['index' => $x, 'value' => $this->calc->normalCDF($x, $mu, $sigma)];
        }

        return $result;
    }

    /**
     * Calculate "binomial distribution" of some range of numbers
     *    * Binomial distribution (https://en.wikipedia.org/wiki/Binomial_distribution)
     *
     * @param float $p
     * @param int $n
     * @param array $range
     * @return array
     * @throws ServiceException
     */
    public function getBinomialDistribution(float $p = 0.5, int $n = 100, array $range = [])
    {
        if ($p <= 0 || $p >= 1) {
            throw new ServiceException('Param "p" for binomial distribution must be a flat between 0 and 1', ServiceException::CODE_INVALID_PARAMS);
        }

        if (!isset($range[0])) {
            $range[0] = 0;
        }
        if (!isset($range[1])) {
            $range[1] = 1000;
        }

        $data = [];
        for ($i = $range[0]; $i <= $range[1]; $i++) {
            $data[] = $this->calc->binomial($n, $p);
        }

        list($min, $max) = [min($data), max($data) + 1];
        $mu = $p * $n;
        $sigma = sqrt($n * $p * (1 - $p));

        $result = [];
        for ($i = $min; $i < $max; $i++) {
            $result[] = [
                'index' => $i,
                'value' => $this->calc->normalCDF($i + 0.5, $mu, $sigma) - $this->calc->normalCDF($i - 0.5, $mu, $sigma),
            ];
        }

        return $result;
    }
}