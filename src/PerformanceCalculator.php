<?php

declare(strict_types=1);

namespace Theatrical;

use Error;

class PerformanceCalculator
{
    public function __construct(
        public Performance $performance,
        public Play $play
    ) {
    }

    public function amount(): int
    {
        $result = 0;

        switch ($this->play->type) {
            default:
                throw new Error("Unknown type: {$this->play->type}");
        }
        
        return $result;
    }

    public function volumeCredits(): float
    {
        $result = 0;
        $result += max($this->performance->audience - 30, 0);

        if ($this->play->type === 'comedy') {
            $result += floor($this->performance->audience / 5);
        }

        return $result;
    }
}
