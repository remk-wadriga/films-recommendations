<?php


namespace App\Controller;

use App\TestService\Calculator;
use App\TestService\Stats\UserEntity;
use App\TestService\StatsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class TestController extends AbstractController
{
    /**
     * @Route("/test/users", name="test_users_list", methods={"GET"})
     */
    public function users(StatsService $service, Calculator $calc)
    {
        $calc->illustrateCoinBalanceCheckAlternative();
        dd('OK');
    }

    /**
     * @Route("/test/users/friends-count", name="test_users_friends_count", methods={"GET"})
     */
    public function usersFriendsCount(StatsService $service)
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
    public function usersCountsToFriendsCountRelation(StatsService $service)
    {
        return $this->json($service->getUsersCountSortedByFriendsCount());
    }

    /**
     * @Route("/test/probability/normal-distribution", name="test_probability_normal_distribution", methods={"GET"})
     */
    public function normalDistribution(Request $request, StatsService $service)
    {
        $mu = floatval($request->get('mu', 0));
        $sigma = floatval($request->get('sigma', 1));

        return $this->json($service->getNormalDistribution($this->getRequestRange($request), $mu, $sigma));
    }

    /**
     * @Route("/test/probability/normal-cdf", name="test_probability_normal_cdf", methods={"GET"})
     */
    public function normalCdf(Request $request, StatsService $service)
    {
        $mu = floatval($request->get('mu', 0));
        $sigma = floatval($request->get('sigma', 1));

        return $this->json($service->getNormalCDF($this->getRequestRange($request), $mu, $sigma));
    }

    /**
     * @Route("/test/probability/binomial-distribution", name="test_probability_binomial_distribution", methods={"GET"})
     */
    public function binomialDistribution(Request $request, StatsService $service)
    {
        $p = floatval($request->get('p', 0.5));
        $n = intval($request->get('n', 100));
        return $this->json($service->getBinomialDistribution($p, $n));
    }

    private function getRequestRange(Request $request): array
    {
        $range = $request->get('range');
        if (!empty($range) && preg_match("/^([\d-]+)-(\d+)$/", $range, $matches)) {
            $range = [floatval($matches[1]), floatval($matches[2])];
        } else {
            $range = [];
        }
        return $range;
    }
}