<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComboStatisticRepository
{
    public function getOrderDetails($startDate, $endDate)
    {
        return DB::table('orderdetails')
            ->join('orders', 'orderdetails.OrderId', '=', 'orders.OrderId')
            ->join('foodanddrinks', 'orderdetails.ItemId', '=', 'foodanddrinks.ItemId')
            ->whereNotNull('orderdetails.ItemId')
            ->where('orders.Status', 'Paid')
            ->whereBetween('orders.OrderDate', [$startDate, $endDate])
            ->select(
                'foodanddrinks.ItemId',
                'foodanddrinks.Name',
                'foodanddrinks.Price as UnitPrice',
                'orderdetails.Quantity',
                'orderdetails.Price as TotalPrice',
                'orders.OrderDate'
            )
            ->get();
    }

    public function countOrdersWithCombo($startDate, $endDate)
    {
        return DB::table('orders')
            ->join('orderdetails', 'orders.OrderId', '=', 'orderdetails.OrderId')
            ->whereNotNull('orderdetails.ItemId')
            ->where('orders.Status', 'Paid')
            ->whereBetween('orders.OrderDate', [$startDate, $endDate])
            ->distinct('orders.OrderId')
            ->count('orders.OrderId');
    }

    public function getDailyRevenue($startDate, $endDate)
    {
        return DB::table('orders')
            ->join('orderdetails', 'orders.OrderId', '=', 'orderdetails.OrderId')
            ->whereNotNull('orderdetails.ItemId')
            ->where('orders.Status', 'Paid')
            ->whereBetween('orders.OrderDate', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(orders.OrderDate) as date'),
                DB::raw('SUM(orderdetails.Price) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }
}