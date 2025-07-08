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

Route::get('/coordinator/print-receipt', function () {
    $ids = explode(',', request()->get('members'));
    $coordinatorId = request()->get('coordinator');
    $receiptRef = request()->get('ref'); // ✅ GET from URL

    $coordinator = Member::find($coordinatorId);

    $payments = [];
    foreach ($ids as $id) {
        $member = Member::with('deceaseds')->find($id);

        $contributions = Contribution::where('payer_memberID', $id)
            ->where('status', 1)
            ->whereDate('payment_date', today())
            ->get();

        $payments[] = [
            'member' => $member,
            'total' => $contributions->sum('amount'),
            'deceased' => $member->deceaseds ?? [],
        ];
    }

    return view('receipts.payment-receipt', [
        'payments' => $payments,
        'coordinator' => $coordinator,
        'receiptRef' => $receiptRef, // ✅ PASS HERE
    ]);
})->name('coordinator.print-receipt');
