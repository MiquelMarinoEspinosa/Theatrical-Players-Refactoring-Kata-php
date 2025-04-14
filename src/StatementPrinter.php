<?php

declare(strict_types=1);

namespace Theatrical;

use Error;
use NumberFormatter;

class StatementPrinter
{
    /** 
     * @var array<string, Play>
     */
    private array $plays;

    /**
     * @param array<string, Play> $plays
     */
    public function print(Invoice $invoice, array $plays): string
    {
        $totalAmount = 0;
        $this->plays = $plays;

        $result = "Statement for {$invoice->customer}\n";

        foreach ($invoice->performances as $performance) {
            $result .= "  {$this->playFor($performance)->name}: {$this->usd($this->amountFor($performance))} ";
            $result .= "({$performance->audience} seats)\n";

            $totalAmount += $this->amountFor($performance);
        }

        $result .= "Amount owed is {$this->usd($totalAmount)}\n";
        $result .= "You earned {$this->totalVolumeCredits($invoice)} credits";
        return $result;
    }

    private function playFor(Performance $aPerformance): Play
    {
        return $this->plays[$aPerformance->playId];
    }

    private function totalVolumeCredits(Invoice $invoice): float
    {
        $volumeCredits = 0;
        foreach ($invoice->performances as $performance) {
            $volumeCredits += $this->volumeCreditsFor($performance);   
        }

        return $volumeCredits;
    }

    private function volumeCreditsFor(Performance $aPerformance): float
    {
        $result = 0;
        $result += max($aPerformance->audience - 30, 0);

        if ($this->playFor($aPerformance)->type === 'comedy') {
            $result += floor($aPerformance->audience / 5);
        }

        return $result;
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

    private function usd(float $aNumber): string
    {
        return new NumberFormatter('en_US', NumberFormatter::CURRENCY)
            ->formatCurrency($aNumber / 100, 'USD');
    }
}
