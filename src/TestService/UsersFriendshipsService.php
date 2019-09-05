<?php


namespace App\TestService;

use App\TestService\UsersFriendships\UserEntity;
use App\Exception\ServiceException;

class UsersFriendshipsService extends AbstractTestService
{
    protected $usersFile = 'users.json';
    protected $usersFriendshipsFile = 'users_friendships.json';
    protected $usersInterestsFile = 'users_interests.json';
    protected $usersSalariesAndTenuresFile = 'users_salaries_and_tenures.json';
    protected $users;
    protected $usersByInterests = [];
    protected $salariesByTenures = [];

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
        foreach ($this->getFileReader($this->usersFriendshipsFile)->readFile() as $friendship) {
            list($i, $j) = $friendship;
            $userI = $this->findUserByID($i);
            $userJ = $this->findUserByID($j);
            if ($userI === null || $userJ === null) {
                continue;
            }
            $userI->addFriend($userJ);
        }

        // Set up users interests
        foreach ($this->getFileReader($this->usersInterestsFile)->readFile() as $userInterest) {
            $user = $this->findUserByID($userInterest[0]);
            if ($user !== null) {
                $user->addInterest($userInterest[1]);
            }
        }

        // Set up users salaries and tenures
        foreach ($this->getFileReader($this->usersSalariesAndTenuresFile)->readFile() as $userSandT) {
            $user = $this->findUserByID($userSandT[0]);
            if ($user !== null) {
                $user->salary = $userSandT[1];
                $user->tenure = $userSandT[2];
            }
        }

        return $this->users;
    }

    public function findUserByID($id): ?UserEntity
    {
        if ($this->users === null) {
            $this->getUsers();
        }
        return isset($this->users[$id]) ? $this->users[$id] : null;
    }

    /**
     * @param array $interests
     * @param UserEntity $excluding
     *
     * @return UserEntity[]
     * @throws ServiceException
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

    public function getSalariesIndexedByTenures(array $tenures = [])
    {
        if (empty($tenures)) {
            foreach ($this->getUsers() as $user) {
                if (!in_array($user->tenure, $tenures)) {
                    $tenures[] = $user->tenure;
                }
            }
        }
        usort($tenures, function ($tenureI, $tenureJ) {
            return $tenureI > $tenureJ ? 1 : -1;
        });
        $key = implode(':', $tenures);
        if (isset($this->salariesByTenures[$key])) {
            return $this->salariesByTenures[$key];
        }

        $this->salariesByTenures[$key] = [];
        foreach ($tenures as $tenure) {
            $this->salariesByTenures[$key][(string)$tenure] = [];
        }

        foreach ($this->getUsers() as $user) {
            $tenure = (string)$user->tenure;
            if (!isset($this->salariesByTenures[$key][$tenure])) {
                $realIndex = null;
                foreach (array_keys($this->salariesByTenures[$key]) as $indexedTenure) {
                    if (!preg_match("/^(<|>) (\d+)$/", $indexedTenure, $matches) || !is_array($matches) || count($matches) !== 3) {
                        $matches = [$indexedTenure, '=', $tenure];
                    }
                    $tenureFloatVal = floatval($matches[2]);
                    switch ($matches[1]) {
                        case '<':
                            if ($user->tenure < $tenureFloatVal) {
                                $realIndex = $indexedTenure;
                            }
                            break;
                        case '>':
                            if ($user->tenure > $tenureFloatVal) {
                                $realIndex = $indexedTenure;
                            }
                            break;
                        default:
                            if ($user->tenure === $tenureFloatVal) {
                                $realIndex = $indexedTenure;
                            }
                            break;
                    }
                    if ($realIndex !== null) {
                        $tenure = $realIndex;
                        break;
                    }
                }
                if ($realIndex === null) {
                    continue;
                }
            }
            $this->salariesByTenures[$key][$tenure][] = $user->salary;
        }

        return $this->salariesByTenures[$key];
    }

    public function calculateAverageSalariesForTenures(array $tenures = [])
    {
        $indexedSalaries = $this->getSalariesIndexedByTenures($tenures);
        $result = [];
        foreach ($indexedSalaries as $tenure => $salaries) {
            $result[$tenure] = !empty($salaries) ? array_sum($salaries) / count($salaries) : 0;
        }
        return $result;
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