<?php

namespace App\Console\Commands;

use App\Libraries\Netlify;
use App\Models\Process;
use App\Models\ProcessLog;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
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
                $this->comment("Try Sending  : ". $toSend->count());

                try {
                    $result = $netlify->inviteIdentity($process->site_id, $process->identity_id, $toSend->toArray());
                } catch (ConnectionException $cnx) {
                    $this->error("TIMEOUT PROCESS");
                    $process->update([
                        'status' => 'finish connection timeout',
                        'pid' => 0
                    ]);
                    return 0;
                } catch (RequestException $cnx) {
                    $this->error("Request Exception");
                    $process->update([
                        'status' => 'finish request exception',
                        'pid' => 0
                    ]);
                    return 0;
                }
                $retryAfter = now()->diffInSeconds($result['reset_at'], false);
                $datetime = now()->addSeconds($retryAfter);

                ProcessLog::add(
                    json_encode($result['headers']),
                    $result['body'],
                    (int)$result['limit'],
                    (int)$result['left'],
                    $datetime,
                    $process->id,
                    $result['code']
                );

                if($result['status']) {
                    $this->comment("Sent With Success ". $toSend->count());
                    $total = $total + $toSend->count();
                    $process->update(['total_sent' => $total]);
                    sleep($process->delay_by);
                } else {
                    $this->error("FAILED");
                    if($result['code'] == 429) {
                        $this->comment("API RATE LIMITED");
                        if ($retryAfter > 0) {
                            $process->update(['status' => 'rate limit, sleeping until ' . $datetime->toDateTimeString()]);
                            $this->error("sleeping for $retryAfter seconds");
                            sleep($retryAfter);
                        }
                    } else {
                        $this->error("KILLING PROCESS");
                        $process->update([
                            'status' => 'finish error api',
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
