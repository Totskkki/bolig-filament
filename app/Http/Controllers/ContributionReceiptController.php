<?php

namespace App\Http\Controllers;

use ZipArchive;
use App\Models\Member;
use iio\libmergepdf\Merger;
use Illuminate\Support\Str;
use App\Models\Contribution;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ContributionReceiptController extends Controller
{


    public function show(Request $request)
    {
        $payerId = $request->payer;
        $batchId = $request->batch;

        $payer = Member::with('name')->findOrFail($payerId);

        $query = Contribution::with(['deceased.member.name'])
            ->where('payer_memberID', $payerId)
            ->where('status', 1);

        if ($batchId) {
            $query->where('payment_batch', $batchId);
        }

        $contributions = $query->orderBy('payment_date', 'desc')->get();
        $totalPaid = $contributions->sum('amount');

        return view('admin.contributions.receipt', compact('payer', 'contributions', 'totalPaid'));
    }


    public function bulkReceipt(Request $request)
    {
        $payerIds = explode(',', $request->input('payer_ids', ''));
        $batchId = $request->input('batch');

        $receipts = [];

        foreach ($payerIds as $payerId) {
            $payer = Member::with('name')->find($payerId);

            $query = Contribution::with(['deceased.member.name'])
                ->where('payer_memberID', $payerId)
                ->where('status', 1);


            if ($batchId) {
                $query->where('payment_batch', $batchId);
            }

            $contributions = $query->orderBy('payment_date', 'desc')->get();
            $totalPaid = $contributions->sum('amount');

            $qrContent = "BOLIG Receipt\nPayer: {$payer->name->full_name}\nDate: " . now()->format('Y-m-d H:i') .
                "\nBatch ID: {$batchId}\nTotal: ₱" . number_format($totalPaid, 2);

            $receipts[] = [
                'payer' => $payer,
                'contributions' => $contributions,
                'total' => $totalPaid,
                'qr' => $qrContent,
            ];
        }

        return view('admin.contributions.print-bulk', compact('receipts'));
    }



    public function downloadZip(Request $request)
    {
        $batchId = $request->batch;
        $payerIds = explode(',', $request->payer_ids);
        $zipFileName = "receipts_batch_{$batchId}.zip";

        $tempPath = storage_path("app/public/temp-receipts/{$batchId}");
        Storage::makeDirectory("public/temp-receipts/{$batchId}");

        foreach ($payerIds as $payerId) {
            $payer = Member::with('name')->find($payerId);
            $contributions = Contribution::with('deceased.member.name')
                ->where('payer_memberID', $payerId)
                ->where('payment_batch', $batchId)
                ->get();

            if ($contributions->isEmpty()) continue;

            $totalPaid = $contributions->sum('amount');

            $pdf = Pdf::loadView('admin.contributions.receipt', compact('payer', 'contributions', 'totalPaid'));
            $filename = Str::slug($payer->name->full_name) . ".pdf";

            file_put_contents("{$tempPath}/{$filename}", $pdf->output());
        }

        // Zip it
        $zip = new ZipArchive;
        $zipPath = storage_path("app/public/{$zipFileName}");
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            foreach (glob("{$tempPath}/*.pdf") as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        // Clean up temp folder (optional)
        Storage::deleteDirectory("public/temp-receipts/{$batchId}");

        return response()->download($zipPath)->deleteFileAfterSend();
    }
}
