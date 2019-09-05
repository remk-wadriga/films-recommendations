<?php


namespace App\Controller;

use App\TestService\UsersStatsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class TestController extends AbstractController
{
    /**
     * @Route("/test/users", name="test_users_list", methods={"GET"})
     */
    public function users(UsersStatsService $service)
    {
        $user0 = $service->findUserByID(0);
        $user1 = $service->findUserByID(1);
        $user2 = $service->findUserByID(2);
        $user3 = $service->findUserByID(3);
        $user4 = $service->findUserByID(4);
        $user5 = $service->findUserByID(5);
        $user6 = $service->findUserByID(6);
        $user7 = $service->findUserByID(7);
        $user8 = $service->findUserByID(8);
        $user9 = $service->findUserByID(9);


        dd($service->getInterestsWordsCountsSortedByCount());
        //dd($friendshipsService->getUsersSortedByFiendsCount());

        //dd($friendshipsService->getSalariesIndexedByTenures(['< 2', '< 5', '> 5']));
        dd($service->calculateAverageSalariesForTenures(['< 2', '< 5', '> 5']));
    }
}