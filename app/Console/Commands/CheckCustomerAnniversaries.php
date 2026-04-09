<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\User;
use App\Notifications\CustomerAnniversaryNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckCustomerAnniversaries extends Command
{
    protected $signature   = 'notify:customer-anniversaries';
    protected $description = 'Envía notificaciones de aniversario de clientes que cumplen hoy';

    public function handle(): void
    {
        $today = now();

        $customers = Customer::query()
            ->whereNotNull('anniversary_date')
            ->whereMonth('anniversary_date', $today->month)
            ->whereDay('anniversary_date', $today->day)
            ->get();

        foreach ($customers as $customer) {
            $years = $today->year - $customer->anniversary_date->year;

            // Evitar duplicados: no reenviar si ya se notificó hoy
            $alreadySent = DB::table('notifications')
                ->where('type', CustomerAnniversaryNotification::class)
                ->whereDate('created_at', $today->toDateString())
                ->whereRaw("JSON_EXTRACT(data, '$.customer_id') = ?", [$customer->id])
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $yearLabel = $years > 0 ? " ({$years} " . ($years === 1 ? 'año' : 'años') . ")" : '';

            $notification = new CustomerAnniversaryNotification(
                title:        "Aniversario: {$customer->name}",
                message:      "Hoy es el aniversario de {$customer->name}{$yearLabel}. ¡Buen momento para contactarle!",
                customerId:   $customer->id,
                customerName: $customer->name,
            );

            // Notificar al usuario asignado si es gerente o admin; si no, notificar a todos los gerentes y admin de la empresa
            if ($customer->assigned_to) {
                $user = User::find($customer->assigned_to);
                if ($user && $user->hasAnyRole(['gerente', 'admin', 'super-admin'])) {
                    $user->notify($notification);
                    continue;
                }
            }

            User::where('company_id', $customer->company_id)
                ->whereNull('deleted_at')
                ->role(['gerente', 'admin', 'super-admin'])
                ->each(fn(User $user) => $user->notify(clone $notification));
        }

        $this->info('Revisión de aniversarios completada.');
    }
}
