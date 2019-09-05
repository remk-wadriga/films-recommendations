<?php


namespace App\TestService;

use App\TestService\UsersFriendships\UserEntity;
use App\Exception\ServiceException;

class UsersFriendshipsService extends AbstractTestService
{
    protected $usersFile = 'users.json';
    protected $usersFriendshipsFile = 'users_friendships.json';
    protected $usersInterestsFile = 'users_interests.json';
    protected $users;

    /**
     * @return UserEntity[]
     * @throws ServiceException
     */
    public function getUsers()
    {
        if ($this->users !== null) {
            return $this->users;
        }

        $this->users = [];
        foreach ($this->getFileReader($this->usersFile)->readFile() as $data) {
            $this->users[] = $this->createObject(UserEntity::class, $data);
        }

        foreach ($this->getFileReader($this->usersFriendshipsFile)->readFile() as $friendship) {
            list($i, $j) = $friendship;
            $userI = $this->findUserByID($i);
            $userJ = $this->findUserByID($j);
            if ($userI === null || $userJ === null) {
                continue;
            }
            $userI->addFriend($userJ);
        }

        foreach ($this->getFileReader($this->usersInterestsFile)->readFile() as $userInterest) {
            $user = $this->findUserByID($userInterest[0]);
            if ($user !== null) {
                $user->addInterest($userInterest[1]);
            }
        }

        return $this->users;
    }

    public function findUserByID($id): ?UserEntity
    {
        foreach ($this->getUsers() as $user) {
            if ($user->id == $id) {
                return $user;
            }
        }
        return null;
    }

    public function calculateConnectionsCount()
    {
        $totalCount = 0;
        foreach ($this->getUsers() as $user) {
            $totalCount += $user->friendsCount;
        }
        return $totalCount;
    }

    public function calculateAverageConnectionsCount()
    {
        return $this->calculateConnectionsCount() / count($this->getUsers());
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

    /**
     * @param UserEntity $user
     * @return UserEntity[]
     */
    public function getFriendsOfUserFriends(UserEntity $user)
    {
        $friends = [];
        foreach ($user->friends as $friend) {
            foreach ($friend->friends as $friendOfFriend) {
                if ($friendOfFriend !== $user && !in_array($friendOfFriend, $user->friends) && !in_array($friendOfFriend, $friends)) {
                    $friends[] = $friendOfFriend;
                }
            }
        }
        return $friends;
    }
}