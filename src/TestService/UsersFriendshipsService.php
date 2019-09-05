<?php


namespace App\TestService;

use App\TestService\UsersFriendships\UserEntity;
use App\Exception\ServiceException;

class UsersFriendshipsService extends AbstractTestService
{
    protected $usersFile = 'users.json';
    protected $usersFriendshipsFile = 'users_friendships.json';
    protected $users;
    protected $friendships;

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

        foreach ($this->getFriendships() as $friendship) {
            list($i, $j) = $friendship;
            $userI = $this->findUserByID($i);
            $userJ = $this->findUserByID($j);
            if ($userI === null || $userJ === null) {
                continue;
            }
            $userI->addFriend($userJ);
        }

        return $this->users;
    }

    public function getFriendships()
    {
        if ($this->friendships !== null) {
            return $this->friendships;
        }
        return $this->friendships = $this->getFileReader($this->usersFriendshipsFile)->readFile();
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
}