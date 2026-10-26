<?php

namespace App\Services;

use App\Repositories\ComboStatisticRepository;
use Carbon\Carbon;

class ComboStatisticService
{
    protected $repository;

    public function __construct(ComboStatisticRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getComboStatistics($period = '7days')
    {
        // 1. Xác định mốc thời gian
        $startDate = Carbon::now();
        $endDate = Carbon::now();
        
        switch ($period) {
            case '30days': $startDate->subDays(30); break;
            case '90days': $startDate->subDays(90); break;
            case 'thisMonth': $startDate->startOfMonth(); break;
            case 'lastMonth': 
                $startDate->subMonth()->startOfMonth(); 
                $endDate = Carbon::now()->subMonth()->endOfMonth(); 
                break;
            case '7days': default: $startDate->subDays(7); break;
        }

        // 2. Lấy dữ liệu thô
        $orderDetails = $this->repository->getOrderDetails($startDate, $endDate);
        $totalOrders = $this->repository->countOrdersWithCombo($startDate, $endDate);
        $dailyRevenueRaw = $this->repository->getDailyRevenue($startDate, $endDate);

        // 3. Tính KPI
        $totalRevenue = $orderDetails->sum('TotalPrice');
        $totalItems = $orderDetails->sum('Quantity');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // 4. Phân loại sản phẩm
        $categories = [
            'Bắp' => ['count' => 0, 'totalItems' => 0, 'revenue' => 0, 'percentage' => 0],
            'Nước ngọt' => ['count' => 0, 'totalItems' => 0, 'revenue' => 0, 'percentage' => 0],
            'Combo' => ['count' => 0, 'totalItems' => 0, 'revenue' => 0, 'percentage' => 0],
            'Snack' => ['count' => 0, 'totalItems' => 0, 'revenue' => 0, 'percentage' => 0],
        ];
        
        $productStats = [];

        foreach ($orderDetails as $item) {
            $cat = $this->determineCategory($item->Name);
            
            $categories[$cat]['totalItems'] += $item->Quantity;
            $categories[$cat]['revenue'] += $item->TotalPrice;

            if (!isset($productStats[$item->ItemId])) {
                $productStats[$item->ItemId] = [
                    'name' => $item->Name,
                    'category' => $cat,
                    'sold' => 0,
                    'revenue' => 0,
                    'percentage' => 0,
                ];
            }
            $productStats[$item->ItemId]['sold'] += $item->Quantity;
            $productStats[$item->ItemId]['revenue'] += $item->TotalPrice;
        }

        // Cập nhật % và số lượng cho danh mục
        foreach ($categories as $key => &$cat) {
            $cat['percentage'] = $totalRevenue > 0 ? round(($cat['revenue'] / $totalRevenue) * 100, 1) : 0;
            $cat['count'] = count(array_filter($productStats, fn($p) => $p['category'] === $key));
        }

        // 5. Top sản phẩm bán chạy
        usort($productStats, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
        $topProducts = array_slice($productStats, 0, 5);
        
        foreach ($topProducts as &$prod) {
            $prod['percentage'] = $totalRevenue > 0 ? round(($prod['revenue'] / $totalRevenue) * 100, 1) : 0;
        }

        // 6. Format biểu đồ doanh thu
        $dailyRevenue = $dailyRevenueRaw->map(function($item) {
            return [
                'label' => Carbon::parse($item->date)->format('d/m'),
                'value' => $item->revenue
            ];
        });

        return [
            'stats' => [
                'totalRevenue' => $totalRevenue,
                'totalOrders' => $totalOrders,
                'totalItems' => $totalItems,
                'avgOrderValue' => round($avgOrderValue),
                'growth' => 12.5, // Tạm fix cứng
            ],
            'categories' => $categories,
            'topProducts' => $topProducts,
            'dailyRevenue' => $dailyRevenue,
        ];
    }

    // Hàm phụ trợ phân loại sản phẩm
    private function determineCategory($name)
    {
        if (stripos($name, 'bắp') !== false) return 'Bắp';
        if (stripos($name, 'coca') !== false || stripos($name, 'pepsi') !== false || stripos($name, 'nước') !== false) return 'Nước ngọt';
        if (stripos($name, 'snack') !== false || stripos($name, 'bánh') !== false) return 'Snack';
        return 'Combo';
    }
}