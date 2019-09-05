<?php


namespace App\TestService\UsersFriendships;

use App\TestService\AbstractEntity;

class UserEntity extends AbstractEntity
{
    public $id;
    public $name;
    public $friendsCount = 0;
    public $fiends = [];

    public function addFriend(UserEntity $friend)
    {
        if ($friend === $this || in_array($friend, $this->fiends)) {
            return;
        }
        $this->fiends[] = $friend;
        $friend->addFriend($this);
        $this->friendsCount++;
    }
}