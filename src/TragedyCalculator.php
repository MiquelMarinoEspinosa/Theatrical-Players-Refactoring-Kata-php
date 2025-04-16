<?php

declare(strict_types=1);

namespace Theatrical;

final class TragedyCalculator extends PerformanceCalculator
{
    public function amount(): int
    {
        $result = 40000;
        if ($this->performance->audience > 30) {
            $result += 1000 * ($this->performance->audience - 30);
        }

        return $result;
    }
}
