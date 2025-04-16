<?php

declare(strict_types=1);

namespace Theatrical;

final class ComedyCalculator extends PerformanceCalculator
{
    public function amount(): int
    {
        $result = 30000;
        if ($this->performance->audience > 20) {
            $result += 10000 + 500 * ($this->performance->audience - 20);
        }
        $result += 300 * $this->performance->audience;

        return $result;
    }

    public function volumeCredits(): float
    {
        $result = parent::volumeCredits();
        $result += floor($this->performance->audience / 5);

        return $result;
    }
}
