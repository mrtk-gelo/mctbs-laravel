<?php

namespace App\Support;

use Carbon\Carbon;

class UnitTimeFormatter
{
    public static function format(string $time): string
    {
        $formattedTime = Carbon::parse($time)->format('g:i A');

        return $formattedTime === '12:00 PM' ? '12:00 NN' : $formattedTime;
    }
}
