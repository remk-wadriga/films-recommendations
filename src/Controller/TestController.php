<?php


namespace App\Controller;

use App\TestService\Calculator;
use App\TestService\UsersStats\UserEntity;
use App\TestService\UsersStatsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class TestController extends AbstractController
{
    /**
     * @Route("/test/users", name="test_users_list", methods={"GET"})
     */
    public function users(UsersStatsService $service, Calculator $calc)
    {
        //$v1 = [1, 2, 3, 4, 5, 6, 7];
        //$v2 = [13, 45, 15, 12, 3, 0, 19];
        //$v2 = [12, 23, 34, 43, 55, 60, 71];
        //$activeMinutes = $friendsCount;
        $numFriends = $service->getNumFriendsData();
        $dailyMinutes = $service->getDailyMinutesData();
        $outlier = array_search(100, $numFriends);

        $numFriendsGood = $numFriends;
        $dailyMinutesGood = $dailyMinutes;
        unset($numFriendsGood[$outlier]);
        unset($dailyMinutesGood[$outlier]);

        dd($calc->correlation($numFriendsGood, $dailyMinutesGood));
    }

    /**
     * @Route("/test/users/friends-count", name="test_users_friends_count", methods={"GET"})
     */
    public function usersFriendsCount(UsersStatsService $service)
    {
        $data = [];
        foreach ($service->getUsers() as $user) {
            $data[] = [
                'name' => $user->name,
                'count' => $user->friendsCount,
            ];
        }
        return $this->json($data);
    }

    /**
     * @Route("/test/users/count-to-friends-count-relation", name="test_users_count_to_friends_count_relation", methods={"GET"})
     */
    public function usersCountsToFriendsCountRelation(UsersStatsService $service)
    {
        return $this->json($service->getUsersCountSortedByFriendsCount());
    }
}