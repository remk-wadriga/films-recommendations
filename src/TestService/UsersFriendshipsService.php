<?php


namespace App\TestService;

use App\TestService\UsersFriendships\UserEntity;
use App\Exception\ServiceException;
use App\TestService\UsersFriendships\FileReaderTrait;
use App\TestService\UsersFriendships\DataIndexerTrait;
use App\TestService\UsersFriendships\FinderTrait;

class UsersFriendshipsService extends AbstractTestService
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
        $sorted = $this->getUsers();
        usort($sorted, function (UserEntity $userI, UserEntity $userJ) {
            return $userI->friendsCount > $userJ->friendsCount ? -1 : 1;
        });
        return $sorted;
    }


}