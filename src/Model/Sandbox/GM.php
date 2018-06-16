<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class GM extends AbstractPlayer
{
    public $level;

    public function __construct(int $cardsToDrawAtStart, int $cardsToDraw)
    {
        parent::__construct($cardsToDrawAtStart, $cardsToDraw);

        $this->level = 1;
    }

    public function levelUp()
    {
        $this->level++;
    }

    public function slots(): int
    {
        switch (true) {
            case $this->level > 6:
                return 3;
            case $this->level > 3:
                return 2;
            default:
                return 1;
        }
    }

    protected function drawOne()
    {
        $set = [
            [1, 2, 2],
            [2, 1, 2],
            [2, 2, 1],
        ];

        $card = $set[\mt_rand(0, 2)];

        $this->hand[] = new Test($card[0], $card[1], $card[2]);
    }

    protected function hardUseOne(): AbstractHandCard
    {
        return \array_pop($this->hand);
    }

    protected function softUseOne(): AbstractHandCard
    {
        return \array_pop($this->hand);
    }
}
