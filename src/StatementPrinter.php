<?php

declare(strict_types=1);

namespace Theatrical;

use NumberFormatter;

class StatementPrinter
{
    /**
     * @param array<string, Play> $plays
     */
    public function print(Invoice $invoice, array $plays): string
    {
        return $this->renderPlainText(StatementData::createStatementData($plays, $invoice));
    }

    /**
     * @param array<string, Play> $plays
     */
    public function printHtml(Invoice $invoice, array $plays): string
    {
        return $this->renderHtml(StatementData::createStatementData($plays, $invoice));
    }

    /**
     * @param array<string, Play> $plays
     */
    private function renderPlainText(StatementData $data): string
    {
        $result = "Statement for {$data->customer}\n";

        foreach ($data->performances as $performance) {
            $result .= "  {$performance->play->name}: {$this->usd($performance->amount)} ";
            $result .= "({$performance->audience} seats)\n";
        }

        $result .= "Amount owed is {$this->usd($data->totalAmount)}\n";
        $result .= "You earned {$data->totalVolumeCredits} credits";
        return $result;
    }

    /**
     * @param array<string, Play> $plays
     */
    private function renderHtml(StatementData $data): string
    {
        $result = "<h1>Statement for {$data->customer}</h1>\n";
        $result .= "<table>\n";
        $result .= "<tr><th>play</th><th>seats</th><th>cost</th></tr>\n";
        foreach ($data->performances as $performance) {
            $result .= "  <tr><td>{$performance->play->name}</td><td>{$this->usd($performance->amount)}</td>";
            $result .= "<td>({$performance->audience} seats)</td></tr>\n";
        }
        $result .= "</table>\n";
        $result .= "<p>Amount owed is <em>{$this->usd($data->totalAmount)}</em></p>\n";
        $result .= "<p>You earned <em>{$data->totalVolumeCredits}</em> credits</p>\n";
        return $result;
    }

    private function usd(float $aNumber): string
    {
        return new NumberFormatter('en_US', NumberFormatter::CURRENCY)
            ->formatCurrency($aNumber / 100, 'USD');
    }
}
