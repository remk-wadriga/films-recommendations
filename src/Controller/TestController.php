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
        $user = $friendshipsService->findUserByID(3);

        $friendsOfFriendsWithCommonFriendsCount = [];
        foreach ($user->getFriendsOfMyFriends() as $friend) {
            $friendsOfFriendsWithCommonFriendsCount[$friend->id] = count($user->getCommonFriendsWith($friend));
        }
        dd($friendsOfFriendsWithCommonFriendsCount);
    }
}