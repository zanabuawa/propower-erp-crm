<?php

namespace App\Livewire\Finance;

use App\Models\Customer;
use App\Models\SaleInvoice;
use App\Notifications\PaymentReminderNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PaymentReminders extends Component
{
    use WithPagination;

    // Filtros
    public string $filterUrgency  = '';      // overdue|due-today|due-soon
    public ?int   $filterCustomer = null;
    public string $search         = '';

    // Envío individual
    public ?int  $sendingInvoiceId = null;
    public bool  $sendSuccess      = false;
    public string $sendError       = '';

    // Envío masivo
    public bool  $showBatchModal   = false;
    public array $selectedIds      = [];
    public bool  $batchSending     = false;
    public string $batchResult     = '';

    public function updatingSearch(): void         { $this->resetPage(); }
    public function updatingFilterUrgency(): void  { $this->resetPage(); }
    public function updatingFilterCustomer(): void { $this->resetPage(); }

    // ── Query base ───────────────────────────────────────────────────────
    private function baseQuery()
    {
        $today = now()->startOfDay();

        return SaleInvoice::with(['customer.emails'])
            ->where('company_id', auth()->user()->company_id)
            ->whereIn('status', ['stamped', 'draft'])
            ->whereRaw('total - paid_amount > 0.005')
            ->whereNotNull('due_at')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', fn($c) =>
                        $c->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->filterCustomer, fn($q) =>
                $q->where('customer_id', $this->filterCustomer))
            ->when($this->filterUrgency === 'overdue',
                fn($q) => $q->where('due_at', '<', $today))
            ->when($this->filterUrgency === 'due-today',
                fn($q) => $q->whereBetween('due_at', [$today, $today->copy()->endOfDay()]))
            ->when($this->filterUrgency === 'due-soon',
                fn($q) => $q->whereBetween('due_at', [
                    $today->copy()->addDay(),
                    $today->copy()->addDays(7)->endOfDay(),
                ]))
            ->when(! $this->filterUrgency,
                fn($q) => $q->where('due_at', '<=', $today->copy()->addDays(7)->endOfDay()))
            ->orderByRaw('due_at ASC');
    }

    // ── Counts por urgencia (para badges) ───────────────────────────────
    private function urgencyCounts(): array
    {
        $today = now()->startOfDay();
        $base  = SaleInvoice::where('company_id', auth()->user()->company_id)
            ->whereIn('status', ['stamped', 'draft'])
            ->whereRaw('total - paid_amount > 0.005')
            ->whereNotNull('due_at');

        return [
            'overdue'   => (clone $base)->where('due_at', '<', $today)->count(),
            'due-today' => (clone $base)->whereBetween('due_at', [$today, $today->copy()->endOfDay()])->count(),
            'due-soon'  => (clone $base)->whereBetween('due_at', [
                $today->copy()->addDay(),
                $today->copy()->addDays(7)->endOfDay(),
            ])->count(),
        ];
    }

    // ── Enviar recordatorio individual ───────────────────────────────────
    public function sendReminder(int $invoiceId): void
    {
        $this->sendSuccess      = false;
        $this->sendError        = '';
        $this->sendingInvoiceId = $invoiceId;

        $invoice  = SaleInvoice::with(['customer.emails', 'createdBy'])->findOrFail($invoiceId);
        $customer = $invoice->customer;

        $emails = $customer->emails
            ->sortByDesc('is_primary')
            ->pluck('email')
            ->filter()
            ->values()
            ->all();

        if (empty($emails)) {
            $this->sendError        = "El cliente {$customer->name} no tiene correo registrado.";
            $this->sendingInvoiceId = null;
            return;
        }

        try {
            // Notificación interna al usuario que registró la factura (o al actual)
            $notifiable = $invoice->createdBy ?? auth()->user();
            $notifiable->notify(new PaymentReminderNotification($invoice));

            // Email al cliente
            Notification::route('mail', array_shift($emails))
                ->notify(new PaymentReminderNotification($invoice, $customer->name));

            $invoice->increment('reminder_count');
            $invoice->update(['reminder_sent_at' => now()]);

            $this->sendSuccess      = true;
            $this->sendingInvoiceId = null;
            session()->flash('success', "Recordatorio enviado a {$customer->name}.");

        } catch (\Throwable $e) {
            Log::error('PaymentReminders sendReminder error', [
                'invoice_id' => $invoiceId,
                'error'      => $e->getMessage(),
            ]);
            $this->sendError        = 'Error al enviar: ' . $e->getMessage();
            $this->sendingInvoiceId = null;
        }
    }

    // ── Envío masivo ─────────────────────────────────────────────────────
    public function openBatchModal(): void
    {
        $this->selectedIds  = $this->baseQuery()->pluck('id')->all();
        $this->batchResult  = '';
        $this->showBatchModal = true;
    }

    public function sendBatch(): void
    {
        $this->batchSending = true;
        $this->batchResult  = '';

        $invoices = SaleInvoice::with(['customer.emails', 'createdBy'])
            ->whereIn('id', $this->selectedIds)
            ->where('company_id', auth()->user()->company_id)
            ->get();

        $sent    = 0;
        $skipped = 0;

        foreach ($invoices as $invoice) {
            $emails = $invoice->customer?->emails
                ->sortByDesc('is_primary')
                ->pluck('email')
                ->filter()
                ->values()
                ->all() ?? [];

            if (empty($emails)) {
                $skipped++;
                continue;
            }

            try {
                $notifiable = $invoice->createdBy ?? auth()->user();
                $notifiable->notify(new PaymentReminderNotification($invoice));

                Notification::route('mail', array_shift($emails))
                    ->notify(new PaymentReminderNotification($invoice, $invoice->customer->name));

                $invoice->increment('reminder_count');
                $invoice->update(['reminder_sent_at' => now()]);
                $sent++;

            } catch (\Throwable $e) {
                Log::error('PaymentReminders batch error', [
                    'invoice_id' => $invoice->id,
                    'error'      => $e->getMessage(),
                ]);
                $skipped++;
            }
        }

        $this->batchSending   = false;
        $this->showBatchModal = false;
        $this->selectedIds    = [];

        session()->flash('success', "Recordatorios enviados: {$sent} exitosos, {$skipped} omitidos (sin email).");
    }

    // ── Render ───────────────────────────────────────────────────────────
    public function render()
    {
        $invoices  = $this->baseQuery()->paginate(20);
        $counts    = $this->urgencyCounts();
        $customers = Customer::where('company_id', auth()->user()->company_id)
            ->orderBy('name')->get(['id', 'name']);

        return view('livewire.finance.payment-reminders', compact(
            'invoices', 'counts', 'customers'
        ));
    }
}
