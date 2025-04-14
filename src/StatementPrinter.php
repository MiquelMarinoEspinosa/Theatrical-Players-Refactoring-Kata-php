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
            $play = $this->plays[$performance->playId];
            $thisAmount = $this->amountFor($performance, $play);

            // add volume credits
            $volumeCredits += max($performance->audience - 30, 0);
            // add extra credit for every ten comedy attendees
            if ($play->type === 'comedy') {
                $volumeCredits += floor($performance->audience / 5);
            }

            // print line for this order
            $result .= "  {$play->name}: {$format->formatCurrency($thisAmount / 100, 'USD')} ";
            $result .= "({$performance->audience} seats)\n";

            $totalAmount += $thisAmount;
        }

        $result .= "Amount owed is {$format ->formatCurrency($totalAmount / 100, 'USD')}\n";
        $result .= "You earned {$volumeCredits} credits";
        return $result;
    }

    /**
     * @param array<string, Play> $plays
     */
    private function amountFor(Performance $aPerformance, Play $play): int
    {
        $result = 0;

        switch ($play->type) {
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
                throw new Error("Unknown type: {$play->type}");
        }
        
        return $result;
    }
}
