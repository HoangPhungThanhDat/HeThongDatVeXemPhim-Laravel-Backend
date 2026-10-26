<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    public function getRevenue($startDate, $endDate)
    {
        return Order::whereBetween('OrderDate', [$startDate, $endDate])->where('Status', 'Paid')->sum('TotalAmount');
    }

    public function getTicketsCount($startDate, $endDate)
    {
        return Ticket::whereBetween('CreatedAt', [$startDate, $endDate])->count();
    }

    public function getNewUsers($startDate, $endDate)
    {
        return User::whereBetween('CreatedAt', [$startDate, $endDate])->count();
    }

    public function getOrdersByTime($startDate, $endDate, $groupByFormat)
    {
        return Order::where('Status', 'Paid')
            ->whereBetween('OrderDate', [$startDate, $endDate])
            ->select(DB::raw("{$groupByFormat} as date"), DB::raw('SUM(TotalAmount) as revenue'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }

       public function getTicketsByTime($startDate, $endDate, $groupByFormat)
    {
        // Thay thế OrderDate bằng CreatedAt trong $groupByFormat
        $format = str_replace('OrderDate', 'CreatedAt', $groupByFormat);

        return Ticket::whereBetween('CreatedAt', [$startDate, $endDate])
            ->select(DB::raw("{$format} as date"), DB::raw('COUNT(TicketId) as total_tickets'))
            ->groupBy('date')
            ->get();
    }

    public function getGenreStats($startDate, $endDate)
    {
        return DB::table('tickets')
            ->join('showtimes', 'tickets.ShowtimeId', '=', 'showtimes.ShowtimeId')
            ->join('movies', 'showtimes.MovieId', '=', 'movies.MovieId')
            ->join('moviegenres', 'movies.MovieId', '=', 'moviegenres.MovieId')
            ->join('genres', 'moviegenres.GenreId', '=', 'genres.GenreId')
            ->whereBetween('tickets.CreatedAt', [$startDate, $endDate])
            ->select('genres.Name as name', DB::raw('count(tickets.TicketId) as value'))
            ->groupBy('genres.GenreId', 'genres.Name')
            ->orderByDesc('value')
            ->limit(5)
            ->get();
    }

    public function getTopMovies($startDate, $endDate)
    {
        return DB::table('tickets')
            ->join('showtimes', 'tickets.ShowtimeId', '=', 'showtimes.ShowtimeId')
            ->join('movies', 'showtimes.MovieId', '=', 'movies.MovieId')
            ->whereBetween('tickets.CreatedAt', [$startDate, $endDate])
            ->select('movies.Title as title', DB::raw('count(tickets.TicketId) as tickets'))
            ->groupBy('movies.MovieId', 'movies.Title')
            ->orderByDesc('tickets')
            ->limit(4)
            ->get();
    }

    public function getRecentBookings()
    {
        return Order::with(['user', 'orderdetails.ticket.showtime.movie'])
            ->orderBy('OrderDate', 'desc')
            ->limit(5)
            ->get();
    }
}