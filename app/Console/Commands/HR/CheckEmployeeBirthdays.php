<?php

namespace App\Console\Commands\HR;

use App\Models\HrEmployee;
use App\Models\User;
use App\Notifications\HR\EmployeeBirthdayNotification;
use Illuminate\Console\Command;

class CheckEmployeeBirthdays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hr:check-birthdays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for employee birthdays and notify relevant users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $intervals = [0, 1, 3];
        $admins = User::role(['admin', 'super-admin', 'rrhh'])->get();

        foreach ($intervals as $days) {
            $targetDate = now()->addDays($days);
            $month = $targetDate->month;
            $day   = $targetDate->day;

            $employees = HrEmployee::where('notify_birthday', true)
                ->where('status', 'active')
                ->whereMonth('birth_date', $month)
                ->whereDay('birth_date', $day)
                ->get();

            if ($employees->isNotEmpty()) {
                $this->info("Processing birthdays for interval: {$days} days before.");
                
                foreach ($employees as $employee) {
                    $this->info("- Sending notification for: {$employee->full_name}");
                    
                    foreach ($admins as $admin) {
                        $admin->notify(new EmployeeBirthdayNotification($employee, $days));
                    }
                }
            }
        }

        $this->info('Birthday notifications processed.');
    }
}
