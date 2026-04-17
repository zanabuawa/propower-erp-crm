<?php

namespace App\Console\Commands\HR;

use App\Models\HrProspect;
use App\Notifications\HR\InterviewReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendInterviewReminders extends Command
{
    protected $signature = 'hr:send-interview-reminders';
    protected $description = 'Envía recordatorios automáticos de entrevistas (24h y 1h antes)';

    public function handle()
    {
        $now = Carbon::now();

        // ── 1. Recordatorios 24 HORAS ─────────────────────────────────────────
        $prospects24h = HrProspect::where('status', 'entrevista_agendada')
            ->where('reminder_24h_sent', false)
            ->whereBetween('interview_date', [
                $now->copy()->addHours(23),
                $now->copy()->addHours(25)
            ])
            ->get();

        foreach ($prospects24h as $prospect) {
            $this->sendToRelevantUsers($prospect, '24h');
            $prospect->update(['reminder_24h_sent' => true]);
            $this->info("Recordatorio 24h enviado para: {$prospect->full_name}");
        }

        // ── 2. Recordatorios 1 HORA ──────────────────────────────────────────
        $prospects1h = HrProspect::where('status', 'entrevista_agendada')
            ->where('reminder_1h_sent', false)
            ->whereBetween('interview_date', [
                $now->copy(),
                $now->copy()->addMinutes(70)
            ])
            ->get();

        foreach ($prospects1h as $prospect) {
            $this->sendToRelevantUsers($prospect, '1h');
            $prospect->update(['reminder_1h_sent' => true]);
            $this->info("Recordatorio 1h enviado para: {$prospect->full_name}");
        }
    }

    private function sendToRelevantUsers(HrProspect $prospect, string $timeframe)
    {
        $usersToNotify = collect();

        if ($prospect->interviewer) {
            $usersToNotify->push($prospect->interviewer);
        }

        if ($prospect->scheduledBy && $prospect->scheduled_by_id !== $prospect->interviewer_id) {
            $usersToNotify->push($prospect->scheduledBy);
        }

        foreach ($usersToNotify->unique('id') as $user) {
            $user->notify(new InterviewReminderNotification($prospect, $timeframe));
        }
    }
}
