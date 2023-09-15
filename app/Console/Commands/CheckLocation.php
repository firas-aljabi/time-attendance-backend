<?php

namespace App\Console\Commands;

use App\Events\LocationCheckNotification;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckLocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:location';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentDate = Carbon::now();

        $attendances = Attendance::whereDate('date', $currentDate->toDateString())->get();

        foreach ($attendances as $attendance) {
            if ($attendance->login_time !== null && $attendance->logout_time == null) {
                $notifier = User::where('id', $attendance->user_id)->first();
                $updatedAt = Carbon::parse($attendance->updated_at);
                $diffInMinutes = $updatedAt->diffInMinutes(now());
                if ($diffInMinutes > 30) {
                    $attendance->update([
                        'logout_time' => now()
                    ]);
                    event(new LocationCheckNotification($notifier));
                }
            }
        }
        return Command::SUCCESS;
    }
}