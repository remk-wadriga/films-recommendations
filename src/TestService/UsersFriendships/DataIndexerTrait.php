<?php


namespace App\TestService\UsersFriendships;

trait DataIndexerTrait
{
    protected $salariesByTenures = [];

    /**
     * @param array $tenures
     * @return array
     */
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
}