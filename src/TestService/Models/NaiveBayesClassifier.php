<?php

namespace App\TestService\Models;

use App\Exception\ServiceException;
use App\TestService\AbstractTestService;

class NaiveBayesClassifier extends AbstractTestService
{
    private $k = 0.5;
    private $wordsProbabilities = [];

    /**
     * Learn the classifier
     *
     * @param array $trainingSet
     * @param float $k
     * @return $this
     */
    public function train(array $trainingSet, float $k = null)
    {
        if ($k !== null) {
            $this->k = $k;
        }
        // Calculate spam and non-spam messages
        list($spamCount, $nonSpamCount) = [0, 0];
        foreach ($trainingSet as $isSpam) {
            if (is_array($isSpam)) {
                $isSpam = $isSpam[1];
            }
            if ($isSpam) {
                $spamCount++;
            } else {
                $nonSpamCount++;
            }
        }
        $wordsCounts = $this->countWords($trainingSet);
        $this->wordsProbabilities = $this->wordProbabilities($wordsCounts, $spamCount, $nonSpamCount);
        return $this;
    }

    /**
     * Calculate "is spam" probability for message
     *
     * @param string $massage
     * @return float
     * @throws ServiceException
     */
    public function classify(string $massage)
    {
        if (empty($this->wordsProbabilities)) {
            throw new ServiceException('This classifier is not learned yet', ServiceException::CODE_INVALID_CONFIG);
        }
        return $this->spamProbability($massage);
    }


    /**
     * Get the list of unique words from message
     *
     * @param string $message
     * @return string[]
     */
    private function tokenize(string $message): array
    {
        preg_match_all("/[a-z0-9']+/", strtolower($message), $matches);
        return !is_array($matches) || empty($matches[0]) ? [] : array_unique($matches[0]);
    }

    /**
     * Calculate how many times a particular word occurs in spam and non-spam messages in the training sample $trainingSet.
     *    Return the array like this: [$word => [$spamCount, $nonSpamCount]]
     *
     * @param array $trainingSet
     * @return array
     */
    private function countWords(array $trainingSet): array
    {
        $counts = [];
        foreach ($trainingSet as $index => $isSpamMessage) {
            if (is_array($isSpamMessage)) {
                list($message, $isSpam) = $isSpamMessage;
            } else {
                list($message, $isSpam) = [$index, $isSpamMessage];
            }
            foreach ($this->tokenize($message) as $word) {
                if (!isset($counts[$word])) {
                    $counts[$word] = [0, 0];
                }
                $counts[$word][$isSpam ? 0 : 1]++;
            }
        }
        return $counts;
    }

    /**
     * Calculate probabilities (is spam on is not spam) for each word from training sample
     *    Return the array like this: [$word, $spamProbability, $nonSpamProbability]
     *
     * @param array $wordsCounts
     * @param int $totalSpams
     * @param int $totalNotSpams
     * @return array
     */
    private function wordProbabilities(array $wordsCounts, int $totalSpams, int $totalNotSpams): array
    {
        $k = $this->k;
        $probabilities = [];
        foreach ($wordsCounts as $word => $counts) {
            list($spam, $nonSpam) = $counts;
            $probabilities[] = [
                $word,
                ($spam + $k) / ($totalSpams + 2 * $k),
                ($nonSpam + $k) / ($totalNotSpams + 2 * $k),
            ];
        }
        return $probabilities;
    }

    /**
     * Calculate "is spam" probability for message
     *
     * @param string $message
     * @return float
     */
    private function spamProbability(string $message): float
    {
        list($messageWords, $logProbabilityIfSpam, $logProbabilityIfNotSpam) = [$this->tokenize($message), 0, 0];
        foreach ($this->wordsProbabilities as $wordProbabilities) {
            list($word, $spamProbability, $nonSpamProbability) = $wordProbabilities;
            if (in_array($word, $messageWords)) {
                $logProbabilityIfSpam += log($spamProbability);
                $logProbabilityIfNotSpam += log($nonSpamProbability);
            } else {
                $logProbabilityIfSpam += log(1.0 - $spamProbability);
                $logProbabilityIfNotSpam += log(1.0 - $nonSpamProbability);
            }
        }
        list($probabilityIfSpam, $probabilityIfNotSpam) = [exp($logProbabilityIfSpam), exp($logProbabilityIfNotSpam)];
        return $probabilityIfSpam / ($probabilityIfSpam + $probabilityIfNotSpam);
    }
}