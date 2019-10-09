<?php


namespace App\TestService\Stats;

use App\Exception\ServiceException;
use App\Helpers\File\FileReaderInterface;
use App\TestService\Entities\LabeledPointEntity;
use App\TestService\Entities\VectorEntity;
use Faker\Factory;

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
    protected $languagesGeographyFile = 'languages_geography.json';

    protected $users;
    protected $friendships;
    protected $interests;
    protected $salariesAndTenures;
    protected $activities;
    protected $languagesGeography;


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

    public function getRandomPoints()
    {
        if ($this->randomPoints !== null) {
            return $this->randomPoints;
        }
        return $this->randomPoints = $this->getFileReader($this->randomPointsFile)->readFile();
    }

    /**
     * @return LabeledPointEntity[]
     */
    public function getLanguagesGeography()
    {
        if ($this->languagesGeography !== null) {
            return $this->languagesGeography;
        }
        $this->languagesGeography = [];
        foreach ($this->getFileReader($this->languagesGeographyFile)->readFile() as $language) {
            $this->languagesGeography[] = new LabeledPointEntity(new VectorEntity($language[0]), $language[1]);
        }
        return $this->languagesGeography;
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