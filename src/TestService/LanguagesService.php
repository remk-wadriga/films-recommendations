<?php

namespace App\TestService;

use App\TestService\Entities\LabeledPointEntity;
use App\TestService\Entities\ListEntity;
use App\TestService\Entities\PredictionResultEntity;
use App\TestService\Entities\VectorEntity;
use App\TestService\Models\NearestNeighbors;
use App\TestService\Stats\FileReaderTrait;
use App\TestService\Stats\DataIndexerTrait;
use App\TestService\Stats\FinderTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LanguagesService extends AbstractTestService
{
    use FileReaderTrait;
    use DataIndexerTrait;
    use FinderTrait;

    private $nearestNeighbors;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container, Calculator $calc, NearestNeighbors $nearestNeighbors)
    {
        parent::__construct($em, $container, $calc);
        $this->nearestNeighbors = $nearestNeighbors;
    }

    public function findLanguageByCoordinates(array $coordinates): LabeledPointEntity
    {
        return $this->findLanguageByPoint(new VectorEntity($coordinates));
    }

    public function predictPointLanguageByKnn(LabeledPointEntity $lang, int $k): string
    {
        return $this->nearestNeighbors->knnClassify($k, new ListEntity($this->getLanguagesGeography()), $lang->point);
    }

    /**
     * @param array $range
     * @return PredictionResultEntity[]
     */
    public function getLanguagesKnnPredictions(array $range = [1, 1]): array
    {
        $results = [];
        for ($i = $range[0]; $i <= $range[1]; $i++) {
            $correctPredictions = 0;
            $incorrectPredictions = 0;

            foreach ($this->getLanguagesGeography() as $lang) {
                $predicted = $this->predictPointLanguageByKnn($lang, $i);
                if ($predicted == $lang->label) {
                    $correctPredictions++;
                } else {
                    $incorrectPredictions++;
                }
            }
            $results[] = new PredictionResultEntity($correctPredictions, $incorrectPredictions, $i);
        }
        return $results;
    }
}