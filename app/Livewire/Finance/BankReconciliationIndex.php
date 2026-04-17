<?php

namespace App\Livewire\Finance;

use App\Models\BankReconciliation;
use App\Models\BankStatementLine;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class BankReconciliationIndex extends Component
{
    use WithPagination, WithFileUploads;

    // Vista principal
    public ?int    $viewingId    = null;
    public string  $activeTab    = 'lines';

    // Nueva conciliación
    public bool    $showNewModal      = false;
    public ?int    $newAccountId      = null;
    public string  $newPeriodFrom     = '';
    public string  $newPeriodTo       = '';
    public string  $newBankOpening    = '0';
    public string  $newBankClosing    = '0';

    // Importar CSV
    public bool    $showImportModal   = false;
    public $csvFile                   = null;
    public string  $csvDateColumn     = '0';
    public string  $csvDescColumn     = '1';
    public string  $csvAmountColumn   = '2';
    public string  $csvBalanceColumn  = '3';
    public string  $csvDelimiter      = ',';
    public bool    $csvHasHeader      = true;
    public array   $csvPreview        = [];
    public string  $importError       = '';

    // Match manual
    public ?int    $matchLineId       = null;
    public ?int    $matchTxId         = null;
    public bool    $showMatchModal    = false;

    public array   $accounts          = [];

    public function mount(): void
    {
        $this->newPeriodFrom = now()->startOfMonth()->toDateString();
        $this->newPeriodTo   = now()->toDateString();

        $this->accounts = FinanceAccount::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)->orderBy('name')
            ->get(['id', 'name', 'currency', 'current_balance'])->toArray();
    }

    // ── Nueva conciliación ────────────────────────────────────────────────
    public function createReconciliation(): void
    {
        $this->validate([
            'newAccountId'   => 'required|exists:finance_accounts,id',
            'newPeriodFrom'  => 'required|date',
            'newPeriodTo'    => 'required|date|after_or_equal:newPeriodFrom',
        ]);

        $companyId = auth()->user()->company_id;

        $folio = 'CONC-' . str_pad(
            BankReconciliation::where('company_id', $companyId)->count() + 1,
            5, '0', STR_PAD_LEFT
        );

        // Calcular saldo en libros (sistema)
        $account = FinanceAccount::find($this->newAccountId);
        $bookTxs = FinanceTransaction::where('account_id', $this->newAccountId)
            ->where('status', 'confirmado')
            ->whereBetween('transaction_date', [$this->newPeriodFrom, $this->newPeriodTo])
            ->get(['type', 'amount']);

        $bookIncome  = $bookTxs->where('type', 'ingreso')->sum('amount');
        $bookExpense = $bookTxs->where('type', 'egreso')->sum('amount');

        $rec = BankReconciliation::create([
            'company_id'           => $companyId,
            'finance_account_id'   => $this->newAccountId,
            'created_by'           => auth()->id(),
            'folio'                => $folio,
            'period_from'          => $this->newPeriodFrom,
            'period_to'            => $this->newPeriodTo,
            'status'               => 'draft',
            'bank_opening_balance' => $this->newBankOpening,
            'bank_closing_balance' => $this->newBankClosing,
            'book_opening_balance' => (float)$account->opening_balance,
            'book_closing_balance' => (float)$account->current_balance,
            'difference'           => (float)$this->newBankClosing - (float)$account->current_balance,
        ]);

        $this->showNewModal = false;
        $this->viewingId    = $rec->id;
        $this->activeTab    = 'lines';
        session()->flash('success', "Conciliación {$folio} creada.");
    }

    // ── Importar CSV ──────────────────────────────────────────────────────
    public function updatedCsvFile(): void
    {
        $this->importError = '';
        $this->csvPreview  = [];

        if (! $this->csvFile) return;

        try {
            $path = $this->csvFile->getRealPath();
            $fp   = fopen($path, 'r');
            $rows = [];
            $i    = 0;
            while (($row = fgetcsv($fp, 1000, $this->csvDelimiter ?: ',')) !== false && $i < 5) {
                $rows[] = $row;
                $i++;
            }
            fclose($fp);
            $this->csvPreview = $rows;
        } catch (\Throwable $e) {
            $this->importError = 'Error al leer el archivo: ' . $e->getMessage();
        }
    }

    public function importCsv(): void
    {
        $this->importError = '';

        if (! $this->csvFile || ! $this->viewingId) {
            $this->importError = 'Selecciona un archivo CSV.';
            return;
        }

        $rec = BankReconciliation::findOrFail($this->viewingId);

        try {
            $path      = $this->csvFile->getRealPath();
            $fp        = fopen($path, 'r');
            $lineCount = 0;
            $firstRow  = true;

            DB::transaction(function () use ($fp, $rec, &$lineCount, &$firstRow) {
                while (($row = fgetcsv($fp, 2000, $this->csvDelimiter ?: ',')) !== false) {
                    if ($firstRow && $this->csvHasHeader) { $firstRow = false; continue; }
                    $firstRow = false;

                    $dateRaw   = trim($row[(int)$this->csvDateColumn]   ?? '');
                    $desc      = trim($row[(int)$this->csvDescColumn]   ?? '');
                    $amtRaw    = trim($row[(int)$this->csvAmountColumn] ?? '0');
                    $balRaw    = trim($row[(int)$this->csvBalanceColumn] ?? '0');

                    if (empty($dateRaw) && empty($amtRaw)) continue;

                    // Limpiar monto: quitar $, comas, espacios
                    $amount  = (float) preg_replace('/[^0-9.\-]/', '', $amtRaw);
                    $balance = (float) preg_replace('/[^0-9.\-]/', '', $balRaw);

                    // Parsear fecha flexible
                    try {
                        $date = \Carbon\Carbon::parse($dateRaw)->toDateString();
                    } catch (\Throwable) {
                        continue;
                    }

                    $flow = $amount >= 0 ? 'credit' : 'debit';

                    BankStatementLine::create([
                        'bank_reconciliation_id' => $rec->id,
                        'transaction_date'       => $date,
                        'description'            => $desc,
                        'amount'                 => abs($amount),
                        'balance'                => $balance,
                        'flow'                   => $flow,
                        'match_status'           => 'unmatched',
                    ]);
                    $lineCount++;
                }
            });

            fclose($fp);

            // Auto-match inmediato
            $this->runAutoMatch($rec);

            $this->showImportModal = false;
            $this->csvFile         = null;
            $this->csvPreview      = [];
            session()->flash('success', "{$lineCount} líneas importadas. Match automático ejecutado.");

        } catch (\Throwable $e) {
            Log::error('BankReconciliationIndex importCsv', ['error' => $e->getMessage()]);
            $this->importError = 'Error al importar: ' . $e->getMessage();
        }
    }

    // ── Auto-matching: monto + fecha ±1 día ──────────────────────────────
    public function runAutoMatch(BankReconciliation $rec): void
    {
        $lines = BankStatementLine::where('bank_reconciliation_id', $rec->id)
            ->where('match_status', 'unmatched')->get();

        foreach ($lines as $line) {
            $txType = $line->flow === 'credit' ? 'ingreso' : 'egreso';

            $tx = FinanceTransaction::where('account_id', $rec->finance_account_id)
                ->where('type', $txType)
                ->where('status', 'confirmado')
                ->whereRaw('ABS(amount - ?) < 0.02', [$line->amount])
                ->whereBetween('transaction_date', [
                    $line->transaction_date->subDay()->toDateString(),
                    $line->transaction_date->addDay()->toDateString(),
                ])
                ->whereDoesntHave('bankStatementLines', fn($q) =>
                    $q->where('match_status', '!=', 'unmatched'))
                ->first();

            if ($tx) {
                $line->update([
                    'finance_transaction_id' => $tx->id,
                    'match_status'           => 'matched',
                ]);
            }
        }

        // Recalcular diferencia
        $matched   = BankStatementLine::where('bank_reconciliation_id', $rec->id)->where('match_status', '!=', 'unmatched')->count();
        $total     = BankStatementLine::where('bank_reconciliation_id', $rec->id)->count();
        $unmatched = $total - $matched;

        $rec->update([
            'difference' => (float)$rec->bank_closing_balance - (float)$rec->book_closing_balance,
            'status'     => $unmatched === 0 ? 'reviewed' : 'draft',
        ]);
    }

    // ── Match manual ──────────────────────────────────────────────────────
    public function openMatchModal(int $lineId): void
    {
        $this->matchLineId    = $lineId;
        $this->matchTxId      = null;
        $this->showMatchModal = true;
    }

    public function confirmManualMatch(): void
    {
        if (! $this->matchTxId) return;

        BankStatementLine::where('id', $this->matchLineId)->update([
            'finance_transaction_id' => $this->matchTxId,
            'match_status'           => 'manual',
        ]);

        $this->showMatchModal = false;
        session()->flash('success', 'Línea conciliada manualmente.');
    }

    public function unmatch(int $lineId): void
    {
        BankStatementLine::where('id', $lineId)->update([
            'finance_transaction_id' => null,
            'match_status'           => 'unmatched',
        ]);
    }

    // ── Cerrar conciliación ───────────────────────────────────────────────
    public function closeReconciliation(int $id): void
    {
        $rec = BankReconciliation::findOrFail($id);
        $rec->update([
            'status'    => 'closed',
            'closed_at' => now(),
        ]);
        session()->flash('success', "Conciliación {$rec->folio} cerrada.");
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $reconciliations = BankReconciliation::with(['account', 'createdBy'])
            ->where('company_id', $companyId)
            ->latest()
            ->paginate(15);

        $viewing = $this->viewingId ? BankReconciliation::with(['account', 'createdBy'])->find($this->viewingId) : null;

        $lines        = collect();
        $unmatchedTxs = collect();
        $stats        = ['total' => 0, 'matched' => 0, 'unmatched' => 0];

        if ($viewing) {
            $lines = BankStatementLine::with('transaction')
                ->where('bank_reconciliation_id', $viewing->id)
                ->orderBy('transaction_date')
                ->get();

            $stats['total']     = $lines->count();
            $stats['matched']   = $lines->whereIn('match_status', ['matched', 'manual'])->count();
            $stats['unmatched'] = $stats['total'] - $stats['matched'];

            // Transacciones sin conciliar del período para match manual
            if ($this->showMatchModal && $this->matchLineId) {
                $matchLine = $lines->firstWhere('id', $this->matchLineId);
                if ($matchLine) {
                    $txType = $matchLine->flow === 'credit' ? 'ingreso' : 'egreso';
                    $unmatchedTxs = FinanceTransaction::where('account_id', $viewing->finance_account_id)
                        ->where('type', $txType)
                        ->where('status', 'confirmado')
                        ->whereBetween('transaction_date', [$viewing->period_from, $viewing->period_to])
                        ->get(['id', 'folio', 'concept', 'amount', 'transaction_date']);
                }
            }
        }

        return view('livewire.finance.bank-reconciliation-index', compact(
            'reconciliations', 'viewing', 'lines', 'stats', 'unmatchedTxs'
        ));
    }
}
