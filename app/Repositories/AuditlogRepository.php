<?php

namespace App\Repositories;

use App\Models\Auditlog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuditLogRepository
{
    public function getLogsQuery($search, $action, $startDate)
    {
        $query = Auditlog::with('user')
            ->where('CreatedAt', '>=', $startDate)
            ->orderBy('CreatedAt', 'desc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('Action', 'like', "%{$search}%")
                  ->where('Description', 'like', "%{$search}%")
                  ->orWhere('IpAddress', 'like', "%{$search}%");
            });
        }

        if ($action) {
            $query->where('Action', $action);
        }

        return $query;
    }

    public function paginateLogs($query, $limit = 20)
    {
        return $query->paginate($limit);
    }

    public function countTotalLogsToday()
    {
        return Auditlog::whereDate('CreatedAt', Carbon::today())->count();
    }

    public function countAbnormalLogs($startDate)
    {
        return Auditlog::where('CreatedAt', '>=', $startDate)
            ->where(function($q) {
                $q->where('Description', 'like', '%thất bại%')
                  ->orWhere('Description', 'like', '%lỗi%');
            })->count();
    }

    public function countActiveUsers($startDate)
    {
        return Auditlog::where('CreatedAt', '>=', $startDate)
            ->whereNotNull('UserId')
            ->distinct('UserId')
            ->count('UserId');
    }

    public function getActionsByType($startDate)
    {
        return DB::table('auditlogs')
            ->select('Action', DB::raw('count(*) as count'))
            ->where('CreatedAt', '>=', $startDate)
            ->groupBy('Action')
            ->get();
    }
}