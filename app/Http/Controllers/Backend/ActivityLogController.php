<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::query()->orderByDesc('created_at')->orderByDesc('id');

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($keyword) {
                $builder->where('user_name', 'like', '%' . $keyword . '%')
                    ->orWhere('user_email', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%')
                    ->orWhere('route_name', 'like', '%' . $keyword . '%')
                    ->orWhere('subject_type', 'like', '%' . $keyword . '%')
                    ->orWhere('subject_id', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('action')) {
            $query->where('action', (string) $request->input('action'));
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('backend.activity-logs.index', compact('logs'));
    }
}
