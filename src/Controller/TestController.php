<?php


namespace App\Controller;

use App\TestService\Calculator;
use App\TestService\Examples\SpamFilterExample;
use App\TestService\LanguagesService;
use App\TestService\Models\NearestNeighbors;
use App\TestService\StatsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    /**
     * @Route("/test/users", name="test_users_list", methods={"GET"})
     */
    public function users(Calculator $calc, SpamFilterExample $spamFilter)
    {
        dd($spamFilter->run()->toArray(2));
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

    /**
     * @Route("/test/data/languages-geography", name="test_data_languages_geography", methods={"GET"})
     */
    public function languagesGeography(Request $request, LanguagesService $service)
    {
        $data = [];
        foreach ($service->getLanguagesGeography() as $lang) {
            $point = $lang->point->toArray();
            $data[] = [
                'index' => $lang->label,
                'value' => [
                    $this->formatNumber($point[0]),
                    $this->formatNumber($point[1]),
                ],
            ];
        }
        return $this->json($data);
    }

    /**
     * @Route("/test/data/distances-for-dimensions", name="test_data_distances_for_dimensions", methods={"GET"})
     */
    public function randomDistances(Request $request, NearestNeighbors $service)
    {
        set_time_limit(360);
        return $this->json($service->getDistancesForDimension($this->getRequestRange($request)));
    }

    /**
     * @Route("/test/data/languages-geography-knn-predictions", name="test_data_languages_geography_knn_preditions", methods={"GET"})
     */
    public function languagesGeographyPredictions(Request $request, LanguagesService $service)
    {
        $results = $service->getLanguagesKnnPredictions($this->getRequestRange($request));
        $data = [];
        foreach ($results as $result) {
            $data[] = $result->toArray(2);
        }
        return $this->json($data);
    }


    /**
     * @Route("/test/models/spam-filter", name="test_models_spam_filter", methods={"GET"})
     */
    public function spamFilter(Request $request, SpamFilterExample $spamFilter)
    {
        return $this->json($spamFilter->run($request->get('k')));
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