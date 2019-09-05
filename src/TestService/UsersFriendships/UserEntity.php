<?php


namespace App\TestService\UsersFriendships;

use App\TestService\AbstractEntity;

class UserEntity extends AbstractEntity
{
    public $id;
    public $name;
    public $friendsCount = 0;
    /** @var UserEntity[] */
    public $friends = [];

    protected $commonFriends = [];

    public function addFriend(UserEntity $friend)
    {
        if ($friend === $this || in_array($friend, $this->friends)) {
            return;
        }
        $this->friends[] = $friend;
        $friend->addFriend($this);
        $this->friendsCount++;
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
}