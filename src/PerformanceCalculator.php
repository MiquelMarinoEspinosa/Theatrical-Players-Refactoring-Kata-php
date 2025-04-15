<?php

declare(strict_types=1);

namespace Theatrical;

class PerformanceCalculator
{
    public function __construct(
        public Performance $performance,
        public Play $play
    ) {
    }
}
