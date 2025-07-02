<?php

use App\Models\Member;
use App\Models\Contribution;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\ContributionReceiptController;



Route::get('/', function () {
    return view('welcome');
});


Route::get('/admin/contributions/receipt', [ContributionReceiptController::class, 'show'])
    ->name('contribution.receipt');



Route::get('/admin/receipt/contribution/bulk', [ContributionReceiptController::class, 'bulkReceipt'])
    ->name('contribution.receipt.bulk');

Route::get('/admin/contribution-report/pdf', function () {
    $from = request('from');
    $to = request('to');

    $contributions = Contribution::with('payer.name')
        ->where('status', 1)
        ->when($from, fn($q) => $q->where('payment_date', '>=', $from))
        ->when($to, fn($q) => $q->where('payment_date', '<=', $to))
        ->orderBy('payment_date')
        ->get();

    $total = $contributions->sum('amount');

    $pdf = Pdf::loadView('admin.reports.contributions-pdf', [
        'contributions' => $contributions,
        'from' => $from,
        'to' => $to,
        'total' => $total,
    ]);

    return $pdf->download('contribution-report.pdf');
})->name('contribution.report.pdf');
