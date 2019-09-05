<?php


namespace App\TestService\UsersFriendships;

/**
 * Trait FinderTrait
 * @package App\TestService\UsersFriendships
 *
 * @property UserEntity[]|null $users
 */
trait FinderTrait
{
    protected $usersByInterests = [];

    /**
     * @param array $interests
     * @param UserEntity $excluding
     *
     * @return UserEntity[]
     */
    public function findUsersByInterests(array $interests, UserEntity $excluding = null)
    {
        $key = implode(':', $interests);
        if ($excluding !== null) {
            $key .= ':' . $excluding->id;
        }
        if (isset($this->usersByInterests[$key])) {
            return $this->usersByInterests[$key];
        }

        $this->usersByInterests[$key] = [];
        foreach ($this->getUsers() as $user) {
            if ($user === $excluding) {
                continue;
            }
            if (!empty($user->getInterestsFrom($interests))) {
                $this->usersByInterests[$key][] = $user;
            }
        }
        return $this->usersByInterests[$key];
    }

    public function findUserByID($id): ?UserEntity
    {
        if ($this->users === null) {
            $this->getUsers();
        }
        return isset($this->users[$id]) ? $this->users[$id] : null;
    }
}