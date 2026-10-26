<?php

namespace App\Services;

use App\Repositories\AuditLogRepository;
use Carbon\Carbon;

class AuditLogService
{
    protected $repository;

    // ⭐ KHAI BÁO ACTION MAP TẠI ĐÂY ĐỂ DÙNG CHUNG
    const ACTION_MAP = [
        'Create' => 'Tạo dữ liệu',
        'Update' => 'Sửa dữ liệu',
        'Delete' => 'Xóa dữ liệu',
        'Login' => 'Đăng nhập',
        'Logout' => 'Đăng xuất',
        'Export' => 'Xuất báo cáo',
        'AssignRole' => 'Phân quyền',
        'Config' => 'Cấu hình hệ thống',
        'Refund' => 'Xử lý hoàn tiền',
    ];

    public function __construct(AuditLogRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getStatisticsData($search, $actionText, $range)
    {
        // 1. Xử lý thời gian
        $startDate = Carbon::now();
        if ($range === 'today') $startDate->startOfDay();
        elseif ($range === '30 days') $startDate->subDays(30);
        else $startDate->subDays(7);

        // 2. Xử lý Action (Đảo ngược map từ tiếng Việt sang tiếng Anh để query DB)
        $dbAction = null;
        if ($actionText && $actionText !== 'Tất cả') {
            $reverseMap = array_flip(self::ACTION_MAP);
            $dbAction = $reverseMap[$actionText] ?? $actionText;
        }

        // 3. Lấy dữ liệu từ Repository
        $query = $this->repository->getLogsQuery($search, $dbAction, $startDate);
        $paginatedLogs = $this->repository->paginateLogs($query);

        // 4. Format Logs
        $formattedLogs = collect($paginatedLogs->items())->map(function($log) {
            return $this->formatLogItem($log);
        });

        // 5. Lấy các số liệu KPI
        $kpi = [
            'totalToday' => $this->repository->countTotalLogsToday(),
            'abnormal' => $this->repository->countAbnormalLogs($startDate),
            'activeUsers' => $this->repository->countActiveUsers($startDate),
            'ipMonitoring' => $paginatedLogs->total(),
        ];

        // 6. Dữ liệu biểu đồ (Tạm thời fix cứng)
        $activityByHour = [
            ['time' => '0h', 'actions' => 4], ['time' => '3h', 'actions' => 2],
            ['time' => '6h', 'actions' => 8], ['time' => '9h', 'actions' => 42],
            ['time' => '12h', 'actions' => 58], ['time' => '15h', 'actions' => 71],
            ['time' => '18h', 'actions' => 64], ['time' => '21h', 'actions' => 33],
        ];

        // 7. Format Actions By Type
        $actionsByType = $this->repository->getActionsByType($startDate)->map(function($item) {
            return [
                'name' => self::ACTION_MAP[$item->Action] ?? $item->Action,
                'count' => $item->count,
                'color' => '#ea580c',
            ];
        });

        return [
            'logs' => $formattedLogs,
            'pagination' => [
                'current_page' => $paginatedLogs->currentPage(),
                'last_page' => $paginatedLogs->lastPage(),
                'total' => $paginatedLogs->total(),
            ],
            'kpi' => $kpi,
            'activityByHour' => $activityByHour,
            'actionsByType' => $actionsByType,
        ];
    }

    // Hàm phụ trợ format từng log
    private function formatLogItem($log)
    {
        $descLower = strtolower($log->Description ?? '');
        $isAbnormal = str_contains($descLower, 'thất bại') || str_contains($descLower, 'lỗi') || str_contains($descLower, 'cảnh báo');
        
        $userEmail = 'Khách vãng lai';
        $userRole = 'Khách';
        
        if ($log->user) {
            $userEmail = $log->user->Email;
            $userRole = ($log->user->RoleId == 1) ? 'Admin' : 'Nhân viên';
        }

        return [
            'id' => $log->LogId,
            'time' => Carbon::parse($log->CreatedAt)->format('d/m/Y H:i:s'),
            'user' => $userEmail,
            'role' => $userRole,
            'action' => self::ACTION_MAP[$log->Action] ?? $log->Action,
            'target' => $log->Description ?? 'Không có mô tả',
            'ip' => $log->IpAddress === '::1' ? '127.0.0.1' : ($log->IpAddress ?? 'N/A'),
            'device' => $log->DeviceInfo ?? 'Không xác định',
            'location' => 'TP. Hồ Chí Minh',
            'status' => $isAbnormal ? 'Bất thường' : 'Bình thường',
            'reason' => $isAbnormal ? 'Hệ thống phát hiện thao tác không thành công.' : '',
        ];
    }
}