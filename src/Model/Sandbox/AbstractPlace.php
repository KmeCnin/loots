<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

use Doctrine\Common\Collections\ArrayCollection;

abstract class AbstractPlace extends AbstractCard
{
    /** @var ArrayCollection|AbstractHandCard[] */
    protected $cards;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    public function addCards(iterable $cards): void
    {
        foreach ($cards as $card) {
            $this->cards->add($card);
        }
    }

    abstract public function slotsForTest(Gm $gm): int;

    abstract public function settle(Gm $gm, array $aliveAdventurers): void;

    abstract public function reward(Gm $gm, array $aliveAdventurers): void;

    public function __clone()
    {
        $this->cards = new ArrayCollection();
    }
}
