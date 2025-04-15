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
        $this->plays = $plays;
        $statementData = new class{
            public string $customer;
            public array $performances; 
        };
        $statementData->customer = $invoice->customer;
        $statementData->performances = $this->enrichPerformances(...$invoice->performances);

        return $this->renderPlainText($statementData, $plays);
    }

    private function enrichPerformances(Performance ...$performances): array
    {
        return array_map(function(Performance $performance) {
            $enrichedPerformance = new class(
                $performance->playId,
                $performance->audience
            ) extends Performance {
                public Play $play;
            };
            $enrichedPerformance->play = $this->playFor($enrichedPerformance);
            
            return $enrichedPerformance; 
        }, $performances);
    }

    /**
     * @param array<string, Play> $plays
     */
    private function renderPlainText(object $data, array $plays): string
    {
        $result = "Statement for {$data->customer}\n";

        foreach ($data->performances as $performance) {
            $result .= "  {$this->playFor($performance)->name}: {$this->usd($this->amountFor($performance))} ";
            $result .= "({$performance->audience} seats)\n";
        }

        $result .= "Amount owed is {$this->usd($this->totalAmount($data))}\n";
        $result .= "You earned {$this->totalVolumeCredits($data)} credits";
        return $result;
    }

    private function playFor(Performance $aPerformance): Play
    {
        return $this->plays[$aPerformance->playId];
    }

    private function totalAmount(object $data): int
    {
        $result = 0;
        foreach($data->performances as $performance) {
            $result += $this->amountFor($performance);
        }

        return $result;
    }

    private function totalVolumeCredits(object $data): float
    {
        $result = 0;
        foreach ($data->performances as $performance) {
            $result += $this->volumeCreditsFor($performance);   
        }

        return $result;
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
