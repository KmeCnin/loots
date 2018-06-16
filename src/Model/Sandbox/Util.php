<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class Util
{
    public static function wrand(array $weightedValues)
    {
        $rand = mt_rand(1, (int) array_sum($weightedValues));

        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }

        return null;
    }
}