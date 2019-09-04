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
        $users = $friendshipsService->getUsers();
        $friendships = $friendshipsService->getFriendships();

        foreach ($friendships as $friendship) {
            list ($i, $j) = $friendship;
            $user1 = $friendshipsService->findUserByID($i);
            $user2 = $friendshipsService->findUserByID($j);
            if ($user1 === null || $user2 === null) {
                continue;
            }
            $user1->addFriend($user2);
        }

        dd($users);
    }
}