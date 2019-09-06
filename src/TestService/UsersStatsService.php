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
     * @return UserEntity[]
     * @throws ServiceException
     */
    public function getUsersSortedByFiendsCount()
    {
        $list = $this->getUsers();
        usort($list, function (UserEntity $userI, UserEntity $userJ) {
            return $userI->friendsCount > $userJ->friendsCount ? -1 : 1;
        });
        return $list;
    }

    /**
     * Calculate frequency for words in users interests
     *
     * @param array $words
     * @return mixed
     */
    public function getInterestsWordsCountsSortedByCount(array $words = [])
    {
        $v = [1, 2, 3, 5, 7, 9, 13];
        $w = [3, 2, 1, 5, 7, 2, 6];
        $z = [1, 1, 3];
        //dd($this->calc->vectorsDistance($v, $w));

        $time = microtime(true);
        for ($i = 0; $i < 9000000; $i++) {
            $this->calc->vectorsDistance($v, $w);
        }
        dd(microtime(true) - $time, $this->calc->vectorsDistance($v, $w));


        $list = $this->getInterestsWordsCountsIndexedByWords($words);
        uasort($list, function ($countI, $countJ) {
            return $countI > $countJ ? -1 : 1;
        });
        return $list;
    }

}