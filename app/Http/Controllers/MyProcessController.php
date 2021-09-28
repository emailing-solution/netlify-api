<?php

namespace App\Http\Controllers;

use App\Libraries\BackgroundProcess;
use App\Models\Account;
use App\Models\Process;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MyProcessController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $processes = Process::with('user:id,username')
                ->when(Gate::allows('is_mailer'), fn($q) => $q->where('user_id', auth()->id()))
                ->select([
                    'id', 'status', 'pid', 'split_by', 'delay_by',
                    'total_sent', 'total_emails', 'account_id',
                    'user_id', 'created_at', 'updated_at'
                ]);
            return datatables()->of($processes)->toJson();
        }
        return view('my_process');
    }

    public function get(Account $account, string $site, string $identity)
    {
        Gate::authorize('account_allowed', $account);
        return view('create_process');
    }

    public function create(Account $account, string $site, string $identity, Request $request): JsonResponse
    {
        Gate::authorize('account_allowed', $account);

        $request->validate([
            'emails' => ['required', 'array'],
            'split' => ['required', 'numeric'],
            'delay' => ['required', 'numeric'],
        ]);

        $emails = array_filter($request->emails, fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL));
        $emails = array_values(array_unique($emails));
        if (empty($emails)) {
            return response()->json([
                'error' => 'Please Load Valid Emails'
            ], 400);
        }
        $split = $request->split < 30 ?: 30;
        $delay = $request->delay;

        $process = Process::create([
            'site_id' => $site,
            'identity_id' => $identity,
            'emails' => $emails,
            'status' => 'running',
            'pid' => 0,
            'split_by' => $split,
            'delay_by' => $delay,
            'total_sent' => 0,
            'total_emails' => count($emails),
            'account_id' => $account->id,
            'user_id' => auth()->id(),
        ]);

        $cmd = sprintf("nohup php %s process:run %s ", base_path('artisan'), $process->id);
        $pro = new BackgroundProcess($cmd);
        $pro->run();
        if ($pro->isRunning()) {
            $process->update(['pid' => $pro->getPid()]);
            return response()->json([
                'data' => 'Process Created Successfully'
            ]);
        }
        $process->delete();
        return response()->json([
            'error' => 'Error While Creating Process'
        ]);
    }

    public function kill(Process $process): JsonResponse
    {
        Gate::authorize('process_allowed', $process);
        $pro = BackgroundProcess::createFromPID($process->pid);
        if ($pro->isRunning()) {
            $pro->stop();
        }
        $result = $process->update(['pid' => 0, 'status' => 'killed']);
        return response()->json([
            'status' => $result
        ]);
    }
}
