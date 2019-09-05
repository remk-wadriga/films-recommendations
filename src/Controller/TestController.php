<?php


namespace App\Controller;

use App\TestService\UsersFriendshipsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class TestController extends AbstractController
{
    /**
     * @Route("/test/users", name="test_users_list", methods={"GET"})
     */
    public function users(UsersFriendshipsService $friendshipsService)
    {

        $user0 = $friendshipsService->findUserByID(0);
        $user1 = $friendshipsService->findUserByID(1);
        $user2 = $friendshipsService->findUserByID(2);
        $user3 = $friendshipsService->findUserByID(3);
        $user4 = $friendshipsService->findUserByID(4);
        $user5 = $friendshipsService->findUserByID(5);
        $user6 = $friendshipsService->findUserByID(6);
        $user7 = $friendshipsService->findUserByID(7);
        $user8 = $friendshipsService->findUserByID(8);
        $user9 = $friendshipsService->findUserByID(9);

        //dd($friendshipsService->getSalariesIndexedByTenures(['< 2', '< 5', '> 5']));
        dd($friendshipsService->calculateAverageSalariesForTenures(['< 2', '< 5', '> 5']));
    }
}