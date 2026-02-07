<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        // Filter by User
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by Action Type
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->paginate(20)->withQueryString();

        // Get data for filters
        $users = User::orderBy('name')->get();
        $actionTypes = AuditLog::select('action_type')->distinct()->orderBy('action_type')->pluck('action_type');

        return view('admin.audit.index', compact('logs', 'users', 'actionTypes'));
    }

    /**
     * Display the specified resource.
     */
    public function show(AuditLog $auditLog)
    {
        return view('admin.audit.show', compact('auditLog'));
    }
}
