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
    private function renderPlainText(object $data): string
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

    private function usd(float $aNumber): string
    {
        return new NumberFormatter('en_US', NumberFormatter::CURRENCY)
            ->formatCurrency($aNumber / 100, 'USD');
    }
}
