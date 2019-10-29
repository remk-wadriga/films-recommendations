<?php

namespace App\TestService\Entities;

class PredictionResultEntity
{
    private $data;
    public $name;
    public $correct = 0;
    public $incorrect = 0;
    public $total = 0;
    public $tp = 0;
    public $fp = 0;
    public $tn = 0;
    public $fn = 0;
    public $accuracy = 0;
    public $completeness = 0;

    public function __construct(array $data = [], \Closure $dataModifier = null, string $name = 'Prediction result')
    {
        $this->name = $name;
        $this->data = $data;
        if (!empty($this->data)) {
            foreach ($this->data as $result) {
                list($real, $predicted) = $dataModifier !== null ? call_user_func($dataModifier, $result) : $result;
                if ($real === true && $predicted === true) {
                    $this->tp++;
                } elseif ($real === false && $predicted === true) {
                    $this->fp++;
                } elseif ($real === false && $predicted === false) {
                    $this->tn++;
                } elseif ($real === true && $predicted === false) {
                    $this->fn++;
                }
            }
            list($this->correct, $this->incorrect) = [$this->tp + $this->tn, $this->fp + $this->fn];
            $this->total = $this->correct + $this->incorrect;
            $this->accuracy = $this->tp * 100 / ($this->tp + $this->fp);
            $this->completeness = $this->tp * 100 / ($this->tp + $this->fn);
        }
    }

    public function getCorrectString(int $decimals = 0): string
    {
        if ($this->total === 0) {
            return 0;
        }
        return $this->correct . ' (' . $this->getCorrectPercent($decimals) . '%)';
    }

    public function getIncorrectString(int $decimals = 0): string
    {
        if ($this->total === 0) {
            return 0;
        }
        return $this->incorrect . ' (' . $this->getIncorrectPercent($decimals) . '%)';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCorrect(): int
    {
        return $this->correct;
    }

    public function getIncorrect(): int
    {
        return $this->incorrect;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getCorrectPercent(int $decimals = 0): float
    {
        if ($this->total === 0) {
            return 0;
        }
        $percent = $this->correct / $this->total * 100;
        return $decimals > 0 ? number_format($percent, $decimals, '.', '') : $percent;
    }

    public function getIncorrectPercent(int $decimals = 0): float
    {
        if ($this->total === 0) {
            return 0;
        }
        $percent = $this->incorrect / $this->total * 100;
        return $decimals > 0 ? number_format($percent, $decimals, '.', '') : $percent;
    }

    public function getAccuracy(int $decimals = 0): float
    {
        return $decimals > 0 ? number_format($this->accuracy, $decimals, '.', '') : $this->accuracy;
    }

    public function getCompleteness(int $decimals = 0): float
    {
        return $decimals > 0 ? number_format($this->completeness, $decimals, '.', '') : $this->completeness;
    }

    public function getAccuracyString(int $decimals = 0): string
    {
        if ($this->accuracy === 0) {
            return 0;
        }
        return $this->getAccuracy($decimals) . '%';
    }

    public function getCompletenessString(int $decimals = 0): string
    {
        if ($this->accuracy === 0) {
            return 0;
        }
        return $this->getCompleteness($decimals) . '%';
    }

    public function toArray(int $decimals = 0): array
    {
        return [
            'name' => $this->name,
            'total' => $this->getTotal(),
            'correct' => $this->getCorrectString($decimals),
            'incorrect' => $this->getIncorrectString($decimals),
            'accuracy' => $this->getAccuracyString($decimals),
            'completeness' => $this->getCompletenessString($decimals),
        ];
    }
}