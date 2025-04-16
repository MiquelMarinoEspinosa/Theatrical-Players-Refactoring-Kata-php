<?php

declare(strict_types=1);

namespace Theatrical;

abstract class PerformanceCalculator
{
    public function __construct(
        public Performance $performance,
        public Play $play
    ) {
    }

    abstract public function amount(): int;

    public function volumeCredits(): float
    {
        return max($this->performance->audience - 30, 0);
    }
}
