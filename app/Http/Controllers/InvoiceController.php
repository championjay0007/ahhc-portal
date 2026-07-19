<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Participant;
use App\Models\PortalSetting;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $invoices = Invoice::query()
            ->where('participant_id', $participant->id)
            ->orderBy('invoice_date', 'desc')
            ->get();

        return view('portal.participant.invoices', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['participant', 'worker', 'approver']);

        return view('admin.invoice', compact('invoice'));
    }

    public function storeForParticipant(Request $request)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:100'],
            'invoice_date' => ['required', 'date'],
            'service_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xlsx,xls', 'max:10240'],
        ]);

        $amountCents = (int) round($validated['amount'] * 100);
        $validated['committed_amount_cents'] = $amountCents;

        unset($validated['amount']);
        $validated['amount_cents'] = $amountCents;

        $attachmentPath = null;
        $attachmentDisk = null;
        $attachmentMimeType = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('invoices', 'local');
            $attachmentDisk = 'local';
            $attachmentMimeType = $file->getMimeType();
        }

        try {
            $invoice = Invoice::create(array_merge($validated, [
            'participant_id' => $participant->id,
            'status' => 'submitted',
            'invoice_file_path' => $attachmentPath,
            'attachment_path' => $attachmentPath,
            'attachment_disk' => $attachmentDisk,
            'attachment_mime_type' => $attachmentMimeType,
        ]));
        } catch (QueryException $e) {
            // Handle unique constraint on invoice_number
            if (str_contains($e->getMessage(), 'unique') || str_contains($e->getMessage(), 'UNIQUE')) {
                // remove stored attachment if present
                if ($attachmentPath && Storage::disk($attachmentDisk)->exists($attachmentPath)) {
                    Storage::disk($attachmentDisk)->delete($attachmentPath);
                }

                return back()->withErrors(['invoice_number' => 'An invoice with that number already exists. Please choose a different invoice number.'])->withInput();
            }

            throw $e;
        }

        AuditLogService::record('Invoice Create', $invoice, [], [
            'invoice_number' => $invoice->invoice_number,
            'participant_id' => $invoice->participant_id,
            'status' => $invoice->status,
            'amount_cents' => $invoice->amount_cents,
        ]);

        User::where('role', 'admin')->get()->each(function ($admin) use ($participant, $invoice) {
            NotificationService::notify([
                'user_id' => $admin->id,
                'participant_id' => $participant->id,
                'type' => 'info',
                'data' => [
                    'title' => 'Invoice submitted',
                    'message' => "{$participant->first_name} submitted invoice {$invoice->invoice_number}.",
                    'url' => route('portal.admin.invoices.show', $invoice),
                ],
            ]);
        });

        return redirect()->route('portal.participant.invoices.index')->with('status', 'Invoice created.');
    }

    public function downloadAttachment(Invoice $invoice)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        if ($invoice->participant_id !== $participant->id) {
            abort(403);
        }

        $path = $invoice->invoice_file_path ?: $invoice->attachment_path;

        if (! $path || ! Storage::disk($invoice->attachment_disk)->exists($path)) {
            abort(404);
        }

        $filename = $invoice->invoice_number.'.'.pathinfo($path, PATHINFO_EXTENSION);

        AuditLogService::record('Invoice Download', $invoice, [], []);

        return Storage::disk($invoice->attachment_disk)->download($path, $filename);
    }
}
