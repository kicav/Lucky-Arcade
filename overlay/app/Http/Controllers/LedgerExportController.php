<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LedgerExportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $user = $request->user();
        $filename = 'lucky-arcade-ledger-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($user): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['id', 'created_at', 'type', 'direction', 'amount', 'balance_after', 'reference_type', 'reference_id']);

            $user->ledgerEntries()
                ->orderBy('id')
                ->chunk(500, function ($entries) use ($output): void {
                    foreach ($entries as $entry) {
                        fputcsv($output, [
                            $entry->id,
                            $entry->created_at?->toIso8601String(),
                            $entry->type,
                            $entry->direction->value,
                            $entry->amount,
                            $entry->balance_after,
                            $entry->reference_type,
                            $entry->reference_id,
                        ]);
                    }
                });

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
