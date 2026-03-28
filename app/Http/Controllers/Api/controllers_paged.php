<?php
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// ════════════════════════════════════════════════════════════════
//  HƯỚNG DẪN: Thêm method getPaged() vào từng Controller
//  File này chứa code mẫu cho cả 3 controller
//  Chỉ cần copy đúng phần vào đúng controller là xong
// ════════════════════════════════════════════════════════════════


// ──────────────────────────────────────────────────────────────
//  1. UserController.php
//  Thêm method này vào class UserController
// ──────────────────────────────────────────────────────────────
class UserController extends Controller
{
    // ... các method cũ giữ nguyên ...

    /**
     * GET /api/users/paged?page=1&limit=10
     */
    public function getPaged(Request $request)
    {
        $page  = max(1, (int) $request->query('page',  1));
        $limit = min(100, max(1, (int) $request->query('limit', 10))); // giới hạn tối đa 100

        $query = \App\Models\User::with('role'); // giữ nguyên eager load nếu có

        // ── Tìm kiếm (tuỳ chọn) ──
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('FullName',    'like', "%{$search}%")
                  ->orWhere('Email',     'like', "%{$search}%")
                  ->orWhere('PhoneNumber','like', "%{$search}%");
            });
        }

        // ── Lọc theo trạng thái (tuỳ chọn) ──
        if ($status = $request->query('status')) {
            $query->where('Status', $status);
        }

        $total     = $query->count();
        $totalPages = (int) ceil($total / $limit);
        $data      = $query->orderBy('created_at', 'desc')
                           ->skip(($page - 1) * $limit)
                           ->take($limit)
                           ->get();

        return response()->json([
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'totalPages'  => $totalPages,
        ]);
    }
}


// ──────────────────────────────────────────────────────────────
//  2. PromotionController.php
//  Thêm method này vào class PromotionController
// ──────────────────────────────────────────────────────────────
class PromotionController extends Controller
{
    // ... các method cũ giữ nguyên ...

    /**
     * GET /api/promotions/paged?page=1&limit=10
     */
    public function getPaged(Request $request)
    {
        $page  = max(1, (int) $request->query('page',  1));
        $limit = min(100, max(1, (int) $request->query('limit', 10)));

        $query = \App\Models\Promotion::query();

        // ── Tìm kiếm ──
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('Title', 'like', "%{$search}%")
                  ->orWhere('Code', 'like', "%{$search}%");
            });
        }

        // ── Lọc trạng thái ──
        if ($status = $request->query('status')) {
            $query->where('Status', $status);
        }

        $total      = $query->count();
        $totalPages = (int) ceil($total / $limit);
        $data       = $query->orderBy('created_at', 'desc')
                            ->skip(($page - 1) * $limit)
                            ->take($limit)
                            ->get();

        return response()->json([
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'totalPages'  => $totalPages,
        ]);
    }
}


// ──────────────────────────────────────────────────────────────
//  3. ShowtimeSeatController.php
//  Thêm method này vào class ShowtimeSeatController
// ──────────────────────────────────────────────────────────────
class ShowtimeSeatController extends Controller
{
    // ... các method cũ giữ nguyên ...

    /**
     * GET /api/showtime-seats/paged?page=1&limit=20
     */
    public function getPaged(Request $request)
    {
        $page  = max(1, (int) $request->query('page',  1));
        $limit = min(100, max(1, (int) $request->query('limit', 20)));

        $query = \App\Models\ShowtimeSeat::with(['Seat', 'Showtime.Movie', 'Showtime.Room']);

        // ── Lọc theo suất chiếu ──
        if ($showtimeId = $request->query('showtime_id')) {
            $query->where('ShowtimeId', $showtimeId);
        }

        // ── Lọc trạng thái ghế ──
        if ($status = $request->query('status')) {
            $query->where('Status', $status);
        }

        $total      = $query->count();
        $totalPages = (int) ceil($total / $limit);
        $data       = $query->orderBy('ShowtimeSeatId', 'asc')
                            ->skip(($page - 1) * $limit)
                            ->take($limit)
                            ->get();

        return response()->json([
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'totalPages'  => $totalPages,
        ]);
    }
}