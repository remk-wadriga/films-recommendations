<?php


namespace App\TestService\UsersStats;

use App\TestService\AbstractEntity;
use App\TestService\UsersStatsService;
use App\Exception\ServiceException;

/**
 * Class UserEntity
 * @package App\TestService\UsersFriendships
 *
 * @property UsersStatsService $service
 */
class UserEntity extends AbstractEntity
{
    public $id;
    public $name;
    public $salary = 0;
    public $tenure = 0;
    public $friendsCount = 0;
    /** @var UserEntity[] */
    public $friends = [];
    public $interests = [];

    /** @var UserEntity[] */
    protected $friendsOfMyFriends;

    protected $commonFriends = [];

    /** @var UserEntity[] */
    protected $usersWithMyInterests;

    public function addFriend(UserEntity $user)
    {
        if (isset($this->friends[$user->id]) || $user === $this) {
            return;
        }
        $this->friends[$user->id] = $user;
        $user->addFriend($this);
        $this->friendsCount++;
    }

    public function isFriend(UserEntity $user): bool
    {
        return isset($this->friends[$user->id]);
    }

    public function addInterest(string $interest)
    {
        if (!in_array($interest, $this->interests)) {
            $this->interests[] = $interest;
        }
    }

    /**
     * @return UserEntity[]
     */
    public function getFriendsOfMyFriends()
    {
        if ($this->friendsOfMyFriends !== null) {
            return $this->friendsOfMyFriends;
        }

        $this->friendsOfMyFriends = [];
        foreach ($this->friends as $friend) {
            foreach ($friend->friends as $friendOfFriend) {
                if ($friendOfFriend !== $this && !in_array($friendOfFriend, $this->friends) && !in_array($friendOfFriend, $this->friendsOfMyFriends)) {
                    $this->friendsOfMyFriends[] = $friendOfFriend;
                }
            }
        }

        return $this->friendsOfMyFriends;
    }

    /**
     * @param UserEntity $user
     * @return UserEntity[]
     */
    public function getCommonFriendsWith(UserEntity $user)
    {
        if (isset($this->commonFriends[$user->id])) {
            return $this->commonFriends[$user->id];
        }
        $this->commonFriends[$user->id] = [];
        foreach ($user->friends as $friend) {
            if (in_array($friend, $this->friends)) {
                $this->commonFriends[$user->id][] = $friend;
            }
        }
        return $this->commonFriends[$user->id];
    }

    /**
     * @param UserEntity $user
     * @return array
     */
    public function getCommonInterestsWith(UserEntity $user)
    {
        return $user === $this ? [] : $user->getInterestsFrom($this->interests);
    }

    /**
     * @return UserEntity[]
     * @throws ServiceException
     */
    public function getUsersWithMyInterestsSortedByInterestsCount()
    {
        if ($this->usersWithMyInterests !== null) {
            return $this->usersWithMyInterests;
        }

        $this->usersWithMyInterests = $this->service->findUsersByInterests($this->interests, $this);
        usort($this->usersWithMyInterests, function (UserEntity $userI, UserEntity $userJ) {
            return count($userI->getCommonInterestsWith($this)) > count($userJ->getCommonInterestsWith($this)) ? -1 : 1;
        });

        return $this->usersWithMyInterests;
    }

    /**
     * @param array $interests
     * @return array
     */
    public function getInterestsFrom(array $interests)
    {
        return array_intersect($this->interests, $interests);
    }
}