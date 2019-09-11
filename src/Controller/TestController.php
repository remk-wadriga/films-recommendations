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
        dd($calc->normalPDF(3));
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

    /**
     * @Route("/test/probability/normal-distribution", name="test_probability_normal_distribution", methods={"GET"})
     */
    public function normalDistribution(Request $request, UsersStatsService $service)
    {
        $range = $request->get('range');
        if (!empty($range) && preg_match("/^([\d-]+)-(\d+)$/", $range, $matches)) {
            $range = [floatval($matches[1]), floatval($matches[2])];
        } else {
            $range = [];
        }
        $mu = floatval($request->get('mu', 0));
        $sigma = floatval($request->get('sigma', 1));

        return $this->json($service->getNormalDistribution($range, $mu, $sigma));
    }
}