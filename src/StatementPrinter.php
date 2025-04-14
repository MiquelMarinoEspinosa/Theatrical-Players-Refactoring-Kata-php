<?php

declare(strict_types=1);

namespace Theatrical;

use Error;
use NumberFormatter;

class StatementPrinter
{
    /** @var array<string, Play> */
    private array $plays;

    /**
     * @param array<string, Play> $plays
     */
    public function print(Invoice $invoice, array $plays): string
    {
        $totalAmount = 0;
        $volumeCredits = 0;
        $this->plays = $plays;

        $result = "Statement for {$invoice->customer}\n";
        $format = new NumberFormatter('en_US', NumberFormatter::CURRENCY);

        foreach ($invoice->performances as $performance) {
            $volumeCredits += $this->volumeCreditsFor($performance);

            $result .= "  {$this->playFor($performance)->name}: {$format->formatCurrency($this->amountFor($performance) / 100, 'USD')} ";
            $result .= "({$performance->audience} seats)\n";

            $totalAmount += $this->amountFor($performance);
        }

        $result .= "Amount owed is {$format ->formatCurrency($totalAmount / 100, 'USD')}\n";
        $result .= "You earned {$volumeCredits} credits";
        return $result;
    }

    private function playFor(Performance $aPerformance): Play
    {
        return $this->plays[$aPerformance->playId];
    }

    private function volumeCreditsFor(Performance $aPerformance): float
    {
        $volumeCredits = 0;
        $volumeCredits += max($aPerformance->audience - 30, 0);

        if ($this->playFor($aPerformance)->type === 'comedy') {
            $volumeCredits += floor($aPerformance->audience / 5);
        }

        return $volumeCredits;
    }

    private function amountFor(Performance $aPerformance): int
    {
        $result = 0;

        switch ($this->playFor($aPerformance)->type) {
            case 'tragedy':
                $result = 40000;
                if ($aPerformance->audience > 30) {
                    $result += 1000 * ($aPerformance->audience - 30);
                }
                break;

            case 'comedy':
                $result = 30000;
                if ($aPerformance->audience > 20) {
                    $result += 10000 + 500 * ($aPerformance->audience - 20);
                }
                $result += 300 * $aPerformance->audience;
                break;

            default:
                throw new Error("Unknown type: {$this->playFor($aPerformance)->type}");
        }
        
        return $result;
    }
}
