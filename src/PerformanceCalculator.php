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
            case 'tragedy':
                $result = 40000;
                if ($this->performance->audience > 30) {
                    $result += 1000 * ($this->performance->audience - 30);
                }
                break;

            case 'comedy':
                $result = 30000;
                if ($this->performance->audience > 20) {
                    $result += 10000 + 500 * ($this->performance->audience - 20);
                }
                $result += 300 * $this->performance->audience;
                break;

            default:
                throw new Error("Unknown type: {$this->play->type}");
        }
        
        return $result;
    }
}
