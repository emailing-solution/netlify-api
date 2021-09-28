<?php

namespace App\Console\Commands;

use App\Libraries\Netlify;
use App\Models\Process;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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
            $process->update(['status' => 'processing']);
            $total = $process->total_sent;
            $groupEmails = collect($process->emails)->chunk($process->split_by);
            $netlify = new Netlify($process->account);
            foreach ($groupEmails as $emails) {
                $toSend = $emails->map(fn($e) => ['email' => $e]);
                $result = $netlify->inviteIdentity($process->site_id, $process->identity_id, $toSend->toArray());
                if($result['status']) {
                    $total = $total + $toSend->count();
                    $process->update(['total_sent' => $total]);
                    sleep($process->delay_by);
                } else {
                    if($result['code'] == 429) {
                        $retryAfter = now()->diffInSeconds(Carbon::createFromTimestamp($result['reset_at']), false);
                        if ($retryAfter > 0) {
                            sleep($retryAfter);
                        }
                    } else {
                        $process->update([
                            'status' => 'error',
                            'pid' => 0
                        ]);
                        return 0;
                    }
                }
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
