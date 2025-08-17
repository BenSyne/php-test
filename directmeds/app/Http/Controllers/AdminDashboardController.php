<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Prescription;
use App\Models\Payment;
use App\Models\AuditLog;
use App\Models\ComplianceReport;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display the main admin dashboard.
     */
    public function index()
    {
        $metrics = $this->getKeyMetrics();
        $recentActivity = $this->getRecentActivity();
        $alerts = $this->getSystemAlerts();
        
        return view('admin.dashboard.index', compact('metrics', 'recentActivity', 'alerts'));
    }

    /**
     * Get key performance metrics for the dashboard.
     */
    public function getKeyMetrics()
    {
        return Cache::remember('admin_dashboard_metrics', 300, function () {
            $today = Carbon::today();
            $thisMonth = Carbon::now()->startOfMonth();
            $lastMonth = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

            // Revenue metrics
            $todayRevenue = Payment::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->sum('amount');

            $monthRevenue = Payment::where('created_at', '>=', $thisMonth)
                ->where('status', 'completed')
                ->sum('amount');

            $lastMonthRevenue = Payment::whereBetween('created_at', [$lastMonth, $lastMonthEnd])
                ->where('status', 'completed')
                ->sum('amount');

            $revenueGrowth = $lastMonthRevenue > 0 
                ? (($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 
                : 0;

            // Order metrics
            $todayOrders = Order::whereDate('created_at', $today)->count();
            $monthOrders = Order::where('created_at', '>=', $thisMonth)->count();
            $pendingOrders = Order::where('status', 'pending')->count();
            $processingOrders = Order::where('status', 'processing')->count();

            // User metrics
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();
            $newUsersToday = User::whereDate('created_at', $today)->count();
            $newUsersThisMonth = User::where('created_at', '>=', $thisMonth)->count();

            // Prescription metrics
            $pendingPrescriptions = Prescription::where('status', 'pending_verification')->count();
            $verifiedPrescriptions = Prescription::where('status', 'verified')->count();
            $totalPrescriptions = Prescription::count();

            // Low stock alerts
            $lowStockItems = Product::where('stock_quantity', '<=', DB::raw('reorder_point'))
                ->where('is_active', true)
                ->count();

            return [
                'revenue' => [
                    'today' => $todayRevenue,
                    'month' => $monthRevenue,
                    'growth' => round($revenueGrowth, 2),
                ],
                'orders' => [
                    'today' => $todayOrders,
                    'month' => $monthOrders,
                    'pending' => $pendingOrders,
                    'processing' => $processingOrders,
                ],
                'users' => [
                    'total' => $totalUsers,
                    'active' => $activeUsers,
                    'new_today' => $newUsersToday,
                    'new_month' => $newUsersThisMonth,
                ],
                'prescriptions' => [
                    'pending' => $pendingPrescriptions,
                    'verified' => $verifiedPrescriptions,
                    'total' => $totalPrescriptions,
                ],
                'inventory' => [
                    'low_stock' => $lowStockItems,
                ],
            ];
        });
    }

    /**
     * Get recent system activity.
     */
    public function getRecentActivity()
    {
        return Cache::remember('admin_recent_activity', 60, function () {
            return AuditLog::with(['user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'user' => $log->user ? $log->user->name : 'System',
                        'action' => $log->action,
                        'resource' => $log->auditable_type,
                        'resource_id' => $log->auditable_id,
                        'timestamp' => $log->created_at,
                        'ip_address' => $log->ip_address,
                    ];
                });
        });
    }

    /**
     * Get system alerts that need attention.
     */
    public function getSystemAlerts()
    {
        $alerts = [];

        // Low stock alerts
        $lowStockCount = Product::where('stock_quantity', '<=', DB::raw('reorder_point'))
            ->where('is_active', true)
            ->count();

        if ($lowStockCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Low Stock Alert',
                'message' => "{$lowStockCount} products are running low on stock",
                'action_url' => route('admin.inventory.low-stock'),
                'action_text' => 'View Inventory',
            ];
        }

        // Pending prescriptions
        $pendingPrescriptions = Prescription::where('status', 'pending_verification')->count();
        if ($pendingPrescriptions > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Pending Prescriptions',
                'message' => "{$pendingPrescriptions} prescriptions require verification",
                'action_url' => route('admin.prescriptions.pending'),
                'action_text' => 'Review Prescriptions',
            ];
        }

        // Failed payments in last 24 hours
        $failedPayments = Payment::where('status', 'failed')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->count();

        if ($failedPayments > 0) {
            $alerts[] = [
                'type' => 'error',
                'title' => 'Failed Payments',
                'message' => "{$failedPayments} payment failures in the last 24 hours",
                'action_url' => route('admin.payments.failed'),
                'action_text' => 'Review Payments',
            ];
        }

        // System compliance alerts
        $overdueReports = ComplianceReport::where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->count();

        if ($overdueReports > 0) {
            $alerts[] = [
                'type' => 'error',
                'title' => 'Overdue Compliance Reports',
                'message' => "{$overdueReports} compliance reports are overdue",
                'action_url' => route('compliance.reports'),
                'action_text' => 'View Reports',
            ];
        }

        return $alerts;
    }

    /**
     * Get analytics data for charts.
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', '30'); // Default to 30 days
        $startDate = Carbon::now()->subDays($period);

        $analytics = [
            'revenue_trend' => $this->getRevenueTrend($startDate),
            'order_trend' => $this->getOrderTrend($startDate),
            'user_growth' => $this->getUserGrowth($startDate),
            'top_products' => $this->getTopProducts(),
            'order_status_distribution' => $this->getOrderStatusDistribution(),
        ];

        return response()->json($analytics);
    }

    /**
     * Get revenue trend data.
     */
    private function getRevenueTrend($startDate)
    {
        return Payment::selectRaw('DATE(created_at) as date, SUM(amount) as revenue')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'revenue' => (float) $item->revenue,
                ];
            });
    }

    /**
     * Get order trend data.
     */
    private function getOrderTrend($startDate)
    {
        return Order::selectRaw('DATE(created_at) as date, COUNT(*) as orders')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'orders' => $item->orders,
                ];
            });
    }

    /**
     * Get user growth data.
     */
    private function getUserGrowth($startDate)
    {
        return User::selectRaw('DATE(created_at) as date, COUNT(*) as new_users')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'new_users' => $item->new_users,
                ];
            });
    }

    /**
     * Get top selling products.
     */
    private function getTopProducts()
    {
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('products.name, SUM(order_items.quantity) as total_sold, SUM(order_items.price * order_items.quantity) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'total_sold' => $item->total_sold,
                    'revenue' => (float) $item->revenue,
                ];
            });
    }

    /**
     * Get order status distribution.
     */
    private function getOrderStatusDistribution()
    {
        return Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => ucfirst($item->status),
                    'count' => $item->count,
                ];
            });
    }

    /**
     * User management dashboard.
     */
    public function users(Request $request)
    {
        $query = User::with(['profile', 'roles'])
            ->withCount(['orders', 'prescriptions']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->get('user_type'));
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'locked') {
                $query->where('locked_until', '>', now());
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        $userStats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'patients' => User::where('user_type', 'patient')->count(),
            'pharmacists' => User::where('user_type', 'pharmacist')->count(),
            'prescribers' => User::where('user_type', 'prescriber')->count(),
            'admins' => User::where('user_type', 'admin')->count(),
        ];

        return view('admin.users.index', compact('users', 'userStats'));
    }

    /**
     * Inventory management dashboard.
     */
    public function inventory(Request $request)
    {
        $query = Product::with(['manufacturer', 'category']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ndc_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->get('category'));
        }

        if ($request->filled('stock_status')) {
            $status = $request->get('stock_status');
            if ($status === 'low') {
                $query->where('stock_quantity', '<=', DB::raw('reorder_point'));
            } elseif ($status === 'out') {
                $query->where('stock_quantity', 0);
            }
        }

        $products = $query->orderBy('name')->paginate(20);

        $inventoryStats = [
            'total_products' => Product::where('is_active', true)->count(),
            'low_stock' => Product::where('stock_quantity', '<=', DB::raw('reorder_point'))
                ->where('is_active', true)->count(),
            'out_of_stock' => Product::where('stock_quantity', 0)
                ->where('is_active', true)->count(),
            'total_value' => Product::where('is_active', true)
                ->selectRaw('SUM(stock_quantity * cost_price) as total')
                ->value('total') ?? 0,
        ];

        return view('admin.inventory.index', compact('products', 'inventoryStats'));
    }

    /**
     * Order fulfillment dashboard.
     */
    public function orders(Request $request)
    {
        $query = Order::with(['user', 'items.product', 'payments']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        $orderStats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'orderStats'));
    }

    /**
     * Reporting and analytics dashboard.
     */
    public function reports()
    {
        $reportData = [
            'sales_summary' => $this->getSalesSummary(),
            'user_summary' => $this->getUserSummary(),
            'prescription_summary' => $this->getPrescriptionSummary(),
            'compliance_summary' => $this->getComplianceSummary(),
        ];

        return view('admin.reports.index', compact('reportData'));
    }

    /**
     * Get sales summary for reports.
     */
    private function getSalesSummary()
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        return [
            'this_month_revenue' => Payment::where('created_at', '>=', $thisMonth)
                ->where('status', 'completed')->sum('amount'),
            'last_month_revenue' => Payment::whereBetween('created_at', [$lastMonth, $lastMonthEnd])
                ->where('status', 'completed')->sum('amount'),
            'this_month_orders' => Order::where('created_at', '>=', $thisMonth)->count(),
            'average_order_value' => Order::where('created_at', '>=', $thisMonth)
                ->avg('total_amount'),
        ];
    }

    /**
     * Get user summary for reports.
     */
    private function getUserSummary()
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'users_by_type' => User::selectRaw('user_type, COUNT(*) as count')
                ->groupBy('user_type')->get()->pluck('count', 'user_type'),
        ];
    }

    /**
     * Get prescription summary for reports.
     */
    private function getPrescriptionSummary()
    {
        return [
            'total_prescriptions' => Prescription::count(),
            'pending_verification' => Prescription::where('status', 'pending_verification')->count(),
            'verified_prescriptions' => Prescription::where('status', 'verified')->count(),
            'rejected_prescriptions' => Prescription::where('status', 'rejected')->count(),
        ];
    }

    /**
     * Get compliance summary for reports.
     */
    private function getComplianceSummary()
    {
        return [
            'total_reports' => ComplianceReport::count(),
            'pending_reports' => ComplianceReport::where('status', 'pending')->count(),
            'overdue_reports' => ComplianceReport::where('status', 'pending')
                ->where('due_date', '<', Carbon::now())->count(),
        ];
    }
}