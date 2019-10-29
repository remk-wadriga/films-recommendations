<?php


namespace App\TestService\Examples;


use App\TestService\AbstractTestService;
use App\TestService\Calculator;
use App\TestService\Entities\ListEntity;
use App\TestService\Entities\PredictionResultEntity;
use App\TestService\Models\NaiveBayesClassifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SpamFilterExample extends AbstractTestService
{
    private $classifier;
    private $dataCacheFile = 'naive_bayes_data.cache';
    private $cache;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container, Calculator $calc, NaiveBayesClassifier $classifier)
    {
        parent::__construct($em, $container, $calc);
        $this->classifier = $classifier;
        $this->cache = new FilesystemAdapter();
    }

    public function run(float $k = 0.75): array
    {
        $cacheKey = 'predictions_of_spam_filter_for_coefficient_' . $k;

        // Create test data cache from letters backup
        //$this->cache->delete($cacheKey);
        //$this->createDataCache();

        return $this->cache->get($cacheKey, function (CacheItem $item) use ($k) {
            // Get test data from cache
            $data = new ListEntity($this->getDataFromCache());

            // Split the data to "training" and "check" pieces
            list($trainingSet, $testData) = $data->split($k);

            // Train the classifier
            $this->classifier->train($trainingSet);

            $results = [];
            for ($i = 1; $i < 10; $i++) {
                $result = new PredictionResultEntity($testData, function ($data) use ($i) {
                    list($message, $isSpam) = $data;
                    return[$isSpam, $this->classifier->classify($message) > $i / 10];
                });
                $results[] = [
                    'limit' => $i / 10,
                    'correct' => $result->getCorrectPercent(2),
                    'incorrect' => $result->getIncorrectPercent(2),
                    'accuracy' => $result->getAccuracy(2),
                    'completeness' => $result->getCompleteness(2),
                ];
            }
            return $results;
        });
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
        foreach (['spam.cache', 'easy_ham.cache', 'hard_ham.cache'] as $fileName) {
            $data = $readDir($path . '/' . str_replace('.cache', '', $fileName));
            $this->getFileReader('mail/' . $fileName)->writeData($data);
            $totalData = array_merge($totalData, $data);
        }

        $data = new ListEntity($totalData);
        $this->getFileReader($this->dataCacheFile)->writeData($data->randomize());
    }
}