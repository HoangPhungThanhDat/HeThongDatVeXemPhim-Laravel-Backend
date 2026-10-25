<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStatistics()
    {
        $today = Carbon::today();
        $sevenDaysAgo = Carbon::today()->subDays(6);

        // 1. TÍNH KPI
        $todayRevenue = Order::whereDate('OrderDate', $today)->where('Status', 'Paid')->sum('TotalAmount');
        $todayTickets = Ticket::whereDate('CreatedAt', $today)->count();
        $newUsers = User::where('CreatedAt', '>=', $sevenDaysAgo)->count();

        // 2. BIỂU ĐỒ DOANH THU 7 NGÀY QUA (Sửa lỗi GROUP BY)
        $ordersByDay = Order::where('Status', 'Paid')
            ->where('OrderDate', '>=', $sevenDaysAgo)
            ->select(
                DB::raw('DATE(OrderDate) as date'),
                DB::raw('SUM(TotalAmount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Đếm số vé mỗi ngày riêng biệt để tránh lỗi subquery
        $ticketsByDay = Ticket::where('CreatedAt', '>=', $sevenDaysAgo)
            ->select(
                DB::raw('DATE(CreatedAt) as date'),
                DB::raw('COUNT(TicketId) as total_tickets')
            )
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $revenueChart = $ordersByDay->map(function($item) use ($ticketsByDay) {
            $dateStr = $item->date; // Định dạng Y-m-d
            $ticketCount = isset($ticketsByDay[$dateStr]) ? $ticketsByDay[$dateStr]->total_tickets : 0;
            
            return [
                'day' => Carbon::parse($dateStr)->format('d/m'), 
                'revenue' => $item->revenue / 1000000,
                'tickets' => $ticketCount
            ];
        });

        // 3. TỶ LỆ THỂ LOẠI PHIM
        $genreStats = DB::table('tickets')
            ->join('showtimes', 'tickets.ShowtimeId', '=', 'showtimes.ShowtimeId')
            ->join('movies', 'showtimes.MovieId', '=', 'movies.MovieId')
            ->join('moviegenres', 'movies.MovieId', '=', 'moviegenres.MovieId')
            ->join('genres', 'moviegenres.GenreId', '=', 'genres.GenreId')
            ->select('genres.Name as name', DB::raw('count(tickets.TicketId) as value'))
            ->groupBy('genres.GenreId', 'genres.Name')
            ->orderByDesc('value')
            ->limit(5)
            ->get();
        
        $totalTickets = $genreStats->sum('value') ?: 1;
        $colors = ["#E53E3E", "#ED8936", "#ECC94B", "#9F7AEA", "#4A5568"];
        
        $genreChart = $genreStats->map(function($item, $index) use ($colors, $totalTickets) {
            return [
                'name' => $item->name,
                'value' => round(($item->value / $totalTickets) * 100, 1),
                'color' => $colors[$index] ?? '#A0AEC0'
            ];
        });

        // 4. TOP PHIM BÁN CHẠY
        $topMovies = DB::table('tickets')
            ->join('showtimes', 'tickets.ShowtimeId', '=', 'showtimes.ShowtimeId')
            ->join('movies', 'showtimes.MovieId', '=', 'movies.MovieId')
            ->select('movies.Title as title', DB::raw('count(tickets.TicketId) as tickets'))
            ->groupBy('movies.MovieId', 'movies.Title')
            ->orderByDesc('tickets')
            ->limit(4)
            ->get();

        // 5. ĐƠN HÀNG GẦN ĐÂY
        $recentBookings = Order::with(['user', 'orderdetails.ticket.showtime.movie'])
            ->orderBy('OrderDate', 'desc')
            ->limit(5)
            ->get()
            ->map(function($order) {
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

        return response()->json([
            'kpi' => [
                'revenue' => number_format($todayRevenue) . 'đ',
                'tickets' => $todayTickets,
                'newUsers' => $newUsers,
                'fillRate' => '76%', 
            ],
            'revenueChart' => $revenueChart,
            'genreChart' => $genreChart,
            'topMovies' => $topMovies,
            'recentBookings' => $recentBookings
        ]);
    }
}