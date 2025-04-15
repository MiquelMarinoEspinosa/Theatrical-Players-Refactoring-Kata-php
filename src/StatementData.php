<?php

declare(strict_types=1);

namespace Theatrical;

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
            $calculator = self::createPerformanceCalculator(
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
            $enrichedPerformance->amount = $calculator->amount();
            $enrichedPerformance->volumeCredits = $calculator->volumeCredits();

            return $enrichedPerformance; 
        }, $performances);
    }

    private static function createPerformanceCalculator(Performance $aPerformance, Play $aPlay): PerformanceCalculator
    {
        return new PerformanceCalculator(
            $aPerformance,
            $aPlay
        );
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
}
