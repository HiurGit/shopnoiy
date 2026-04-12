<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SePayWebhookLogController extends Controller
{
    public function index(): View
    {
        $logs = DB::table('sepay_webhook_logs')
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        return view('backend.sepay-webhook-logs.index', compact('logs'));
    }
}
