<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

abstract class AbstractHandCard extends AbstractCard
{
    public $fight;

    public $trick;

    public $magic;

    public $bonusSkill;

    public function __construct(int $fight, int $trick, int $magic, array $bonusSkill)
    {
        $this->fight = $fight;
        $this->trick = $trick;
        $this->magic = $magic;
        $this->bonusSkill = $bonusSkill;
    }

    public function bonusSkill(string $skill): int
    {
        return $this->bonusSkill[$skill] ?? 0;
    }
}
