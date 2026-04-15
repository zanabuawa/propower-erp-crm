<?php

namespace App\Observers;

use App\Models\FinanceTransaction;
use App\Models\SalePayment;

class SalePaymentObserver
{
    /**
     * Cuando un pago se cancela, anular la transacción financiera vinculada.
     * El FinanceTransactionObserver::deleted() recalcula el saldo de la cuenta automáticamente.
     */
    public function saved(SalePayment $payment): void
    {
        if (
            $payment->wasChanged('status') &&
            $payment->status === 'cancelled'
        ) {
            FinanceTransaction::where('reference', $payment->folio)
                ->whereNull('deleted_at')
                ->delete();
        }
    }
}
