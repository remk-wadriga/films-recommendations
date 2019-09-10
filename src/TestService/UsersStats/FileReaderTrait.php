<?php


namespace App\TestService\UsersStats;

use App\Exception\ServiceException;
use App\Helpers\File\FileReaderInterface;

/**
 * Trait FileReaderTrait
 * @package App\TestService\UsersFriendships
 *
 * @method FileReaderInterface getFileReader
 * @method UserEntity findUserByID
 */
trait FileReaderTrait
{
    protected $usersFile = 'users.json';
    protected $usersFriendshipsFile = 'users_friendships.json';
    protected $usersInterestsFile = 'users_interests.json';
    protected $usersSalariesAndTenuresFile = 'users_salaries_and_tenures.json';
    protected $usersActivitiesFile = 'users_activities.json';

    protected $users;
    protected $friendships;
    protected $interests;
    protected $salariesAndTenures;
    protected $activities;


    /**
     * @return UserEntity[]
     * @throws ServiceException
     */
    public function getUsers()
    {
        if ($this->users !== null) {
            return $this->users;
        }

        // Create users list
        $this->users = [];
        foreach ($this->getFileReader($this->usersFile)->readFile() as $data) {
            /** @var UserEntity $user */
            $user = $this->createObject(UserEntity::class, $data);
            $this->users[$user->id] = $user;
        }

        // Set up users friendships
        foreach ($this->getFriendships() as $friendship) {
            list($i, $j) = $friendship;
            $userI = $this->findUserByID($i);
            $userJ = $this->findUserByID($j);
            if ($userI === null || $userJ === null) {
                continue;
            }
            $userI->addFriend($userJ);
        }

        // Set up users interests
        foreach ($this->getInterests() as $userInterest) {
            $user = $this->findUserByID($userInterest[0]);
            if ($user !== null) {
                $user->addInterest($userInterest[1]);
            }
        }

        // Set up users salaries and tenures
        foreach ($this->getSalariesAndTenures() as $userSandT) {
            $user = $this->findUserByID($userSandT[0]);
            if ($user !== null) {
                $user->salary = $userSandT[1];
                $user->tenure = $userSandT[2];
            }
        }

        // Set up users activities (minutes of activity)
        foreach ($this->getActivities() as $userActivity) {
            $user = $this->findUserByID($userActivity[0]);
            if ($user !== null) {
                $user->activeMinutes = $userActivity[1];
            }
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

    public function getInterests()
    {
        if ($this->interests !== null) {
            return $this->interests;
        }
        return $this->interests = $this->getFileReader($this->usersInterestsFile)->readFile();
    }

    public function getSalariesAndTenures()
    {
        if ($this->salariesAndTenures !== null) {
            return $this->salariesAndTenures;
        }
        return $this->salariesAndTenures = $this->getFileReader($this->usersSalariesAndTenuresFile)->readFile();
    }

    public function getActivities()
    {
        if ($this->activities !== null) {
            return $this->activities;
        }
        return $this->activities = $this->getFileReader($this->usersActivitiesFile)->readFile();
    }


    public function getNumFriendsData()
    {
        return $this->getFileReader('num_friends_data.json')->readFile();
    }
    public function getDailyMinutesData()
    {
        return $this->getFileReader('daily_minutes_data.json')->readFile();
    }
}