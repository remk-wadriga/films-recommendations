<?php


namespace App\TestService\Examples;


use App\TestService\AbstractTestService;
use App\TestService\Calculator;
use App\TestService\Entities\ListEntity;
use App\TestService\Models\NaiveBayesClassifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SpamFilterExample extends AbstractTestService
{
    private $classifier;
    private $dataCacheFile = 'naive_bayes_data.cache';

    public function __construct(EntityManagerInterface $em, ContainerInterface $container, Calculator $calc, NaiveBayesClassifier $classifier)
    {
        parent::__construct($em, $container, $calc);
        $this->classifier = $classifier;
    }

    public function run()
    {
        // Create test data cache from letters backup
        //$this->createDataCache();

        // Get test data from cache
        $data = new ListEntity($this->getDataFromCache());

        // Split the data to "training" and "check" pieces
        list($trainingSet, $testData) = $data->split(0.75);

        // Train the classifier
        $this->classifier->train($trainingSet);

        // Get classified data (something like this: [$message, $realIsSpam, $calculatedIsSpamProbability])
        $classified = [];
        foreach ($testData as $messageIsSpam) {
            list($message, $isSpam) = $messageIsSpam;
            $classified[] = [
                $message,
                $isSpam,
                $this->classifier->classify($message),
            ];
        }

        // If $calculatedIsSpamProbability > 0.5 - then classification for message was correct
        $counts = new ListEntity;
        foreach ($classified as $messageIsSpam) {
            list($isSpam, $probability) = array_slice($messageIsSpam, 1, 2);
            $counts[] = [$isSpam, $probability > 0.5];
        }

        // Calculate the accuracy and completeness of predictions
        list($tp, $fp, $tf, $ff) = [0, 0, 0, 0];
        foreach ($counts as $results) {
            list($real, $predicted) = $results;
            if ($real === true && $predicted === true) {
                $tp++;
            } elseif ($real === false && $predicted === true) {
                $fp++;
            } elseif ($real === false && $predicted === false) {
                $tf++;
            } elseif ($real === true && $predicted === false) {
                $ff++;
            }
        }
        $accuracy = $tp * 100 / ($tp + $fp); // ~92.75
        $completeness = $tp * 100 / ($tp + $ff); // ~68.08

        dd(['Accuracy' => $accuracy, 'Completeness' => $completeness]);
    }

    public function getDataFromCache()
    {
        return $this->getFileReader($this->dataCacheFile)->readFile();
    }

    private function createDataCache()
    {
        $path = 'D:\OSPanel\domains\watchfilms.local\var\files\spam_lib';
        $readLetter = function($file) {
            if (!is_file($file)) {
                return null;
            }
            if (!preg_match("/Subject: (.+)\\n/", file_get_contents($file), $matches) || !is_array($matches) || count($matches) !== 2) {
                return null;
            }
            return trim($matches[1]);
        };

        $readDir = function($dir) use ($readLetter) {
            $isSpam = strpos($dir, 'ham') === false;
            $dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dir);
            $titles = [];
            foreach (scandir($dir) as $fileName) {
                if (in_array($fileName, ['.', '..'])) {
                    continue;
                }
                $title = $readLetter($dir . DIRECTORY_SEPARATOR . $fileName);
                if (empty($title)) {
                    continue;
                }
                //$title = str_replace('Re: ', '', $title);
                //$title = str_replace('Fw: ', '', $title);
                $titles[] = [$title, $isSpam];
            }
            return $titles;
        };

        $totalData = [];
        foreach (['mail/spam.cache', 'mail/easy_ham.cache', 'mail/hard_ham.cache'] as $fileName) {
            $data = $readDir($path . '/' . str_replace('.cache', '', $fileName));
            $this->getFileReader($fileName)->writeData($data);
            $totalData = array_merge($totalData, $data);
        }

        $randomData = [];
        for ($i = 0; $i < count($totalData); $i++) {
            $hasItem = false;
            $index = $i;
            while (!$hasItem) {
                $index = rand(0, count($totalData));
                $hasItem = isset($totalData[$index]);
            }
            $randomData[] = $totalData[$index];
        }

        $this->getFileReader($this->dataCacheFile)->writeData($randomData);
    }
}