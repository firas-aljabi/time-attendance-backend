<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AttendanceDaily extends Command
{

    protected $signature = 'attendance:cron';

    protected $description = 'Command description';

    public function handle()
    {
        $today = Carbon::today();
        $users = DB::table('users')
            ->leftJoin('attendances', function ($join) use ($today) {
                $join->on('users.id', '=', 'attendances.user_id')
                    ->whereDate('attendances.date', $today);
            })
            ->whereNull('attendances.id')
            ->select('users.*')
            ->get();

        foreach ($users as $user) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'status' => 0
            ]);
        }
        return Command::SUCCESS;
    }
}
