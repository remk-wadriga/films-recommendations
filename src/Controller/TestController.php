<?php


namespace App\Controller;

use App\TestService\Calculator;
use App\TestService\Examples\GradientDescent;
use App\TestService\Examples\StatisticsExamples;
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
        $n = 7;
        for ($i = 1; $i <= $n; $i++) {
            echo str_repeat('&nbsp;', $n - $i), str_repeat('*', $i), '<br />';
        }
        exit();
    }

    /**
     * @Route("/test/users/friends-count", name="test_users_friends_count", methods={"GET"})
     */
    public function usersFriendsCount(StatsService $service, Calculator $calc)
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
        $step = floatval($request->get('step', 0.2));

        return $this->json($service->getNormalDistribution($this->getRequestRange($request), $mu, $sigma, $step));
    }

    /**
     * @Route("/test/probability/normal-cdf", name="test_probability_normal_cdf", methods={"GET"})
     */
    public function normalCdf(Request $request, StatsService $service)
    {
        $mu = floatval($request->get('mu', 0));
        $sigma = floatval($request->get('sigma', 1));
        $step = floatval($request->get('step', 0.2));

        return $this->json($service->getNormalCDF($this->getRequestRange($request), $mu, $sigma, $step));
    }

    /**
     * @Route("/test/probability/binomial-distribution", name="test_probability_binomial_distribution", methods={"GET"})
     */
    public function binomialDistribution(Request $request, StatsService $service)
    {
        $p = floatval($request->get('p', 0.5));
        $n = intval($request->get('n', 100));
        $step = floatval($request->get('step', 0.3));

        return $this->json($service->getBinomialDistribution($p, $n, $step));
    }

    /**
     * @Route("/test/probability/beta-distribution", name="test_probability_beta_distribution", methods={"GET"})
     */
    public function betaDistribution(Request $request, StatsService $service)
    {
        $alpha = floatval($request->get('alpha', 1));
        $beta = floatval($request->get('beta', 1));
        $step = floatval($request->get('step', 0.02));
        return $this->json($service->getBetaDistribution($alpha, $beta, $step));
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