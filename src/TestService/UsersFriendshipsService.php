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
}