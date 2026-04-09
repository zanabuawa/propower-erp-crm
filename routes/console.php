<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Stock bajo / agotado — revisa cada hora durante horario laboral
Schedule::command('notify:low-stock')->hourlyAt(0)->between('7:00', '20:00');

// Aniversarios de clientes — una vez al día a las 8 AM
Schedule::command('notify:customer-anniversaries')->dailyAt('08:00');

// Productos/servicios con datos incompletos — cada lunes a las 9 AM
Schedule::command('notify:incomplete-products')->weeklyOn(1, '09:00');
