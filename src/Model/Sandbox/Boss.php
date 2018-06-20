<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class Boss extends Region
{
    public function slots(Gm $gm): int
    {
        return 3;
    }
}
