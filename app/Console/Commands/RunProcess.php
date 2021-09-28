<?php

namespace App\Console\Commands;

use App\Libraries\Netlify;
use App\Models\Process;
use Illuminate\Console\Command;

class RunProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:run {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Process';

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
    public function handle()
    {
        if ($process = Process::find($this->argument('id'))) {
            $groupEmails = collect($process->emails)->chunk($process->split_by);
            $netlify = new Netlify($process->account);
            foreach ($groupEmails as $emails) {
                $toSend = $emails->map(fn($e) => ['email' => $e]);
                $result = $netlify->inviteIdentity($process->site_id, $process->identity_id, $toSend->toArray());
                if($result) {
                    $process->update([
                        'total_sent' => $process->total_sent + $toSend->count(),
                    ]);
                }
                sleep($process->delay_by);
            }
            $process->update([
                'status' => 'finish',
                'pid' => 0
            ]);
            return 1;
        }
        return 0;
    }
}
