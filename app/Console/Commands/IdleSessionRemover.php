<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class IdleSessionRemover extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'idle-session:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Idle Session';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $limitTime = config('session.lifetime', 0) * 60;
        if (config('session.driver') == 'database'){
            DB::table('sessions')
                ->whereRaw("(UNIX_TIMESTAMP(NOW()) - sessions.last_activity) >= $limitTime")
                ->delete();
        }
        return 1;
    }
}
