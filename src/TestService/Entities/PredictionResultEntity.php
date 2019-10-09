<?php

namespace App\TestService\Entities;

class PredictionResultEntity
{
    private $name;
    private $correct = 0;
    private $incorrect = 0;
    private $total = 0;

    public function __construct(int $correct, int $incorrect, string $name = 'Prediction result')
    {
        $this->correct = $correct;
        $this->incorrect = $incorrect;
        $this->total = $correct + $incorrect;
        $this->name = $name;
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

    public function toArray(int $decimals = 0): array
    {
        return [
            'name' => $this->name,
            'total' => $this->getTotal(),
            'correctCount' => $this->correct,
            'correctPercents' => $this->getCorrectPercent($decimals),
            'correctLabel' => $this->getCorrectString($decimals),
            'incorrectCount' => $this->incorrect,
            'incorrectPercents' => $this->getIncorrectPercent($decimals),
            'incorrectLabel' => $this->getIncorrectString($decimals),
        ];
    }
}