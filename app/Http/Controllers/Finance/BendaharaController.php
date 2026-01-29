<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Spd;
use App\Models\Budget;
use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BendaharaController extends Controller
{
    /**
     * Bendahara Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'pending_verification' => Spd::whereHas('approvals', function ($q) {
                $q->where('status', 'approved');
            })->where('finance_verified', false)->count(),
            
            'pending_payment' => Spd::where('finance_verified', true)
                ->where('payment_status', 'pending')->count(),
                
            'paid_this_month' => Spd::where('payment_status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->count(),
                
            'total_budget_used' => Spd::where('payment_status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->sum('total_cost') ?? 0,
        ];

        // Recent SPD awaiting verification
        $pendingSpd = Spd::with(['user', 'destination'])
            ->whereHas('approvals', function ($q) {
                $q->where('status', 'approved');
            })
            ->where('finance_verified', false)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Budget status per unit
        $budgetStatus = DB::table('budgets')
            ->select('unit_name', 'allocated', 'used', DB::raw('allocated - used as remaining'))
            ->orderBy('unit_name')
            ->get();

        return view('dashboard.bendahara.index', compact('stats', 'pendingSpd', 'budgetStatus'));
    }

    /**
     * List SPD pending financial verification
     */
    public function pendingVerification()
    {
        $pendingSpd = Spd::with(['user', 'destination', 'approvals'])
            ->whereHas('approvals', function ($q) {
                $q->where('status', 'approved');
            })
            ->where('finance_verified', false)
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('dashboard.bendahara.verification', compact('pendingSpd'));
    }

    /**
     * Verify SPD budget availability
     */
    public function verify(Request $request, Spd $spd)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($request->action === 'approve') {
            $spd->update([
                'finance_verified' => true,
                'finance_verified_at' => now(),
                'finance_verified_by' => Auth::id(),
                'finance_notes' => $request->notes
            ]);

            return redirect()->back()->with('success', 'SPD berhasil diverifikasi.');
        } else {
            $spd->update([
                'finance_rejected' => true,
                'finance_rejected_at' => now(),
                'finance_rejected_by' => Auth::id(),
                'finance_notes' => $request->notes
            ]);

            return redirect()->back()->with('info', 'SPD ditolak dengan catatan.');
        }
    }

    /**
     * List SPD pending payment
     */
    public function pendingPayment()
    {
        $pendingPayment = Spd::with(['user', 'destination'])
            ->where('finance_verified', true)
            ->where('payment_status', 'pending')
            ->orderBy('finance_verified_at', 'asc')
            ->paginate(20);

        return view('dashboard.bendahara.payment', compact('pendingPayment'));
    }

    /**
     * Process payment for SPD
     */
    public function processPayment(Request $request, Spd $spd)
    {
        $request->validate([
            'payment_method' => 'required|in:transfer,cash',
            'payment_reference' => 'nullable|string|max:100',
            'payment_amount' => 'required|numeric|min:0'
        ]);

        $spd->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'paid_by' => Auth::id(),
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'payment_amount' => $request->payment_amount
        ]);

        // Update budget used if tracking
        if ($spd->user && $spd->user->unit_id) {
            DB::table('budgets')
                ->where('unit_id', $spd->user->unit_id)
                ->increment('used', $request->payment_amount);
        }

        return redirect()->back()->with('success', 'Pembayaran berhasil diproses.');
    }

    /**
     * Financial report
     */
    public function report(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $report = Spd::with(['user', 'destination'])
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->orderBy('paid_at', 'desc')
            ->get();

        $summary = [
            'total_spd' => $report->count(),
            'total_amount' => $report->sum('payment_amount'),
            'by_unit' => $report->groupBy('user.unit_name')->map->sum('payment_amount'),
            'by_method' => $report->groupBy('payment_method')->map->sum('payment_amount')
        ];

        return view('dashboard.bendahara.report', compact('report', 'summary', 'startDate', 'endDate'));
    }
}
