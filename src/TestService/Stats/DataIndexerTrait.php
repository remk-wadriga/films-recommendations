<?php


namespace App\TestService\Stats;

trait DataIndexerTrait
{
    protected $salariesByTenures = [];
    protected $interestsWordsCountsIndexedByWords = [];

    /**
     * @param array $tenures
     * @return array
     */
    public function getSalariesIndexedByTenures(array $tenures = [])
    {
        $key = !empty($tenures) ? implode(':', $tenures) : 'all';
        if (isset($this->salariesByTenures[$key])) {
            return $this->salariesByTenures[$key];
        }

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

    public function getInterestsWordsCountsIndexedByWords(array $words = [])
    {
        $key = !empty($words) ? implode(':', $words) : 'all';
        if (isset($this->interestsWordsCountsIndexedByWords[$key])) {
            return $this->interestsWordsCountsIndexedByWords[$key];
        }

        if (empty($words)) {
            foreach ($this->getInterests() as $interest) {
                foreach (explode(' ', $interest[1]) as $word) {
                    if (!in_array($word, $words)) {
                        $words[] = $word;
                    }
                }
            }
        }

        $this->interestsWordsCountsIndexedByWords[$key] = [];
        foreach ($words as $word) {
            if (!isset($this->interestsWordsCountsIndexedByWords[$key][$word])) {
                $this->interestsWordsCountsIndexedByWords[$key][$word] = 0;
            }
            foreach ($this->getInterests() as $interest) {
                foreach (explode(' ', $interest[1]) as $interestWord) {
                    if (strtolower($word) === strtolower($interestWord)) {
                        $this->interestsWordsCountsIndexedByWords[$key][$word]++;
                    }
                }
            }
        }

        return $this->interestsWordsCountsIndexedByWords[$key];
    }
}