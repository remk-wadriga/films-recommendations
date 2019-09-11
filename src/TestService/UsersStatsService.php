<?php


namespace App\TestService;

use App\TestService\UsersStats\UserEntity;
use App\Exception\ServiceException;
use App\TestService\UsersStats\FileReaderTrait;
use App\TestService\UsersStats\DataIndexerTrait;
use App\TestService\UsersStats\FinderTrait;

class UsersStatsService extends AbstractTestService
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
    public function getNormalDistribution(array $range = [], float $mu = 0, float $sigma = 1)
    {
        if (!isset($range[0])) {
            $range[0] = -5;
        }
        if (!isset($range[1])) {
            $range[1] = 5;
        }

        $result = [];
        for ($i = $range[0]; $i <= $range[1]; $i++) {
            $result[] = ['index' => $i, 'value' => $this->calc->normalPDF($i, $mu, $sigma)];
        }

        return $result;
    }
}