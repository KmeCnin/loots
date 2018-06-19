<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class GameStats
{
    private $games;

    private $gamesWin;

    private $rounds;

    private $roundsWin;

    private $stuffs;

    private $stuffsWin;

    private $adventurersHands;

    private $gmHands;

    public function __construct()
    {
        $this->games = 0;
        $this->gamesWin = 0;
        $this->rounds = [];
        $this->roundsWin = [];
        $this->stuffs = [];
        $this->stuffsWin = [];
        $this->adventurersHands = [];
        $this->gmHands = [];
    }

    public function update(Game $game)
    {
        $this->games++;
        $this->rounds[] = \count($game->places);
        foreach ($game->adventurers as $adventurer) {
            $this->stuffs[] = $adventurer->stuff;
        }

        $this->adventurersHands[] = $game->adventurersTotalHand
            / \count($game->adventurers)
            / \count($game->places);
        $this->gmHands[] = $game->gmTotalHand / \count($game->places);

        // Win stats.
        if ($game->adventurersWin) {
            $this->gamesWin++;
            $this->roundsWin[] = \end($this->rounds);
            $this->stuffsWin[] = \end($this->stuffs);
        }
    }

    public function winRate()
    {
        return $this->rate($this->gamesWin);
    }

    public function averageRounds()
    {
        return $this->average($this->rounds);
    }

    public function minRounds()
    {
        return \min($this->rounds);
    }

    public function maxRounds()
    {
        return \max($this->rounds);
    }

    public function roundsFreq()
    {
        $countMap = \array_count_values($this->rounds);
        \ksort($countMap);
        $winMap = \array_count_values($this->roundsWin);

        $freqMap = [];
        foreach ($countMap as $rounds => $count) {
            $allRate = $this->rate($count);
            $winRate = $this->rate($winMap[$rounds] ?? 0);
            $freqMap[$rounds] = [
                'all' => $allRate,
                'win' => $winRate,
                'lose' => $allRate - $winRate,
            ];
        }

        return $freqMap;
    }

    public function roundsStackedFreq()
    {
        $countMap = \array_count_values($this->rounds);
        \ksort($countMap);
        $winMap = \array_count_values($this->roundsWin);

        $freqMap = [];
        $success = 100;
        foreach ($countMap as $rounds => $count) {
            $allRate = $this->rate($count);
            $loseRate = $allRate - $this->rate($winMap[$rounds] ?? 0);
            $freqMap[$rounds] = [
                'survived' => $success -= $loseRate,
                'died' => $loseRate,
            ];
        }

        return $freqMap;
    }

    public function averageStuff()
    {
        return $this->average($this->stuffs);
    }

    public function minStuff()
    {
        return \min($this->stuffs);
    }

    public function maxStuff()
    {
        return \max($this->stuffs);
    }

    public function stuffFreq()
    {
        $countMap = \array_count_values($this->stuffs);
        \ksort($countMap);
        $winMap = \array_count_values($this->stuffsWin);

        $freqMap = [];
        foreach ($countMap as $stuffs => $count) {
            $allRate = $this->rate($count, \count($this->stuffs));
            $winRate = $this->rate($winMap[$stuffs] ?? 0, \count($this->stuffs));
            $freqMap[$stuffs] = [
                'all' => $allRate,
                'win' => $winRate,
                'lose' => $allRate - $winRate,
            ];
        }

        return $freqMap;
    }

    public function averageAdvHands()
    {
        return $this->average($this->adventurersHands);
    }

    public function averageGmHands()
    {
        return $this->average($this->gmHands);
    }

    private function rate(int $number, int $total = null): int
    {
        $total = null !== $total ? $total : $this->games;
        return (int) \round($number / $total * 100, 0);
    }

    private function average(array $data): int
    {
        return (int) \round(\array_sum($data) / \count($data), 0);
    }
}
