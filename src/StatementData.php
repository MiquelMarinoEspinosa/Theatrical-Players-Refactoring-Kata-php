<?php

declare(strict_types=1);

namespace Theatrical;

use Error;

final class StatementData
{
    public string $customer;
    public array $performances;
    public int $totalAmount;
    public float $totalVolumeCredits;
    /** 
     * @var array<string, Play>
     */
    private static array $plays;
    
    private function __construct()
    {
    }

    public static function createStatementData(array $plays, Invoice $invoice): self
    {   
        $statementData = new self();
        self::$plays = $plays;
        $statementData->customer = $invoice->customer;
        $statementData->performances = self::enrichPerformances(...$invoice->performances);
        $statementData->totalAmount = self::totalAmount($statementData);
        $statementData->totalVolumeCredits = self::totalVolumeCredits($statementData);
        return $statementData;
    }

    private static function enrichPerformances(Performance ...$performances): array
    {
        return array_map(function(Performance $performance) {
            $calculator = new PerformanceCalculator(
                $performance,
                self::playFor($performance)
            );
            
            $enrichedPerformance = new class(
                $performance->playId,
                $performance->audience
            ) extends Performance {
                public Play $play;
                public int $amount;
                public float $volumeCredits;
            };
            
            $enrichedPerformance->play = $calculator->play;
            $enrichedPerformance->amount = self::amountFor($enrichedPerformance);
            $enrichedPerformance->volumeCredits = self::volumeCreditsFor($enrichedPerformance);

            return $enrichedPerformance; 
        }, $performances);
    }

    private static function playFor(Performance $aPerformance): Play
    {
        return self::$plays[$aPerformance->playId];
    }

    private static function totalAmount(self $data): int
    {
        return array_reduce($data->performances, static function(int $totalAmount, Performance $aPerformance) {
            return $totalAmount + $aPerformance->amount;
        }, 0);
    }

    private static function totalVolumeCredits(self $data): float
    {
        return array_reduce($data->performances, static function(int $totalVolumeCredits, Performance $aPerformance) {
            return $totalVolumeCredits + $aPerformance->volumeCredits;
        }, 0);
    }

    private static function volumeCreditsFor(Performance $aPerformance): float
    {
        $result = 0;
        $result += max($aPerformance->audience - 30, 0);

        if ($aPerformance->play->type === 'comedy') {
            $result += floor($aPerformance->audience / 5);
        }

        return $result;
    }

    private static function amountFor(Performance $aPerformance): int
    {
        $result = 0;

        switch ($aPerformance->play->type) {
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
                throw new Error("Unknown type: {$aPerformance->play->type}");
        }
        
        return $result;
    }
}
