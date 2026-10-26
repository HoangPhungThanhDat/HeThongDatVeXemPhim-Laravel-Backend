<?php

namespace App\Services;

use App\Repositories\DashboardRepository;
use Carbon\Carbon;

class DashboardService
{
    protected $repository;

    public function __construct(DashboardRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getStatisticsData($period = 'week')
    {
        $today = Carbon::today();
        
        // Xác định khoảng thời gian
        switch ($period) {
            case 'month':
                $startDate = Carbon::today()->startOfMonth();
                $endDate = Carbon::today()->endOfMonth();
                $groupByFormat = 'DATE(OrderDate)'; 
                $chartTitle = 'Doanh thu tháng này';
                break;
            case 'year':
                $startDate = Carbon::today()->startOfYear();
                $endDate = Carbon::today()->endOfYear();
                $groupByFormat = 'MONTH(OrderDate)';
                $chartTitle = 'Doanh thu năm nay';
                break;
            case 'week':
            default:
                $startDate = Carbon::today()->subDays(6);
                $endDate = Carbon::today();
                $groupByFormat = 'DATE(OrderDate)';
                $chartTitle = 'Doanh thu 7 ngày qua';
                break;
        }

        // 1. Lấy KPI
        $kpi = [
            'revenue' => number_format($this->repository->getRevenue($startDate, $endDate)) . 'đ',
            'tickets' => $this->repository->getTicketsCount($startDate, $endDate),
            'newUsers' => $this->repository->getNewUsers($startDate, $endDate),
            'fillRate' => '76%',
        ];

        // 2. Biểu đồ doanh thu
        $ordersByDay = $this->repository->getOrdersByTime($startDate, $endDate, $groupByFormat);
        $ticketsByDay = $this->repository->getTicketsByTime($startDate, $endDate, $groupByFormat);

        $revenueChart = $ordersByDay->map(function($item) use ($ticketsByDay) {
            $dateStr = $item->date;
            $ticketCount = 0;
            foreach ($ticketsByDay as $ticketItem) {
                if ($ticketItem->date == $dateStr) {
                    $ticketCount = $ticketItem->total_tickets;
                    break;
                }
            }
            return [
                'day' => $dateStr, 
                'revenue' => $item->revenue / 1000000,
                'tickets' => $ticketCount
            ];
        });

        // 3. Biểu đồ thể loại
        $genreStats = $this->repository->getGenreStats($startDate, $endDate);
        $totalTickets = $genreStats->sum('value') ?: 1;
        $colors = ["#E53E3E", "#ED8936", "#ECC94B", "#9F7AEA", "#4A5568"];
        
        $genreChart = $genreStats->map(function($item, $index) use ($colors, $totalTickets) {
            return [
                'name' => $item->name,
                'value' => round(($item->value / $totalTickets) * 100, 1),
                'color' => $colors[$index] ?? '#A0AEC0'
            ];
        });

        // 4. Top phim
        $topMovies = $this->repository->getTopMovies($startDate, $endDate);

        // 5. Đơn hàng gần đây
        $recentBookings = $this->repository->getRecentBookings()->map(function($order) {
            $firstDetail = $order->orderdetails->first();
            $movieTitle = $firstDetail && $firstDetail->ticket && $firstDetail->ticket->showtime && $firstDetail->ticket->showtime->movie 
                ? $firstDetail->ticket->showtime->movie->Title 
                : 'N/A';
            
            $status = 'pending';
            if ($order->Status === 'Paid') $status = 'confirmed';
            if ($order->Status === 'Cancelled') $status = 'cancelled';

            return [
                'name' => $order->user->FullName ?? 'Khách',
                'movie' => $movieTitle,
                'amount' => number_format($order->TotalAmount) . 'đ',
                'status' => $status
            ];
        });

        return [
            'chartTitle' => $chartTitle,
            'kpi' => $kpi,
            'revenueChart' => $revenueChart,
            'genreChart' => $genreChart,
            'topMovies' => $topMovies,
            'recentBookings' => $recentBookings
        ];
    }
}