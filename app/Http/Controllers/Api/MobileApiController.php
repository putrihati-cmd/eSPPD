<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Spd;
use App\Http\Resources\SppdResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileApiController extends Controller
{
    /**
     * Dashboard summary for mobile
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $employeeId = $user->employee_id;

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'pending' => Spd::where('employee_id', $employeeId)->where('status', 'submitted')->count(),
                    'approved' => Spd::where('employee_id', $employeeId)->where('status', 'approved')->count(),
                    'this_month' => Spd::where('employee_id', $employeeId)->whereMonth('created_at', now()->month)->count(),
                ],
                'recent_sppd' => SppdResource::collection(
                    Spd::where('employee_id', $employeeId)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get()
                ),
                'pending_approvals' => $this->getPendingApprovals($user),
            ],
        ]);
    }

    /**
     * List SPPD with simplified response
     */
    public function listSppd(Request $request): JsonResponse
    {
        $user = $request->user();
        $employeeId = $user->employee_id;

        $spds = Spd::where('employee_id', $employeeId)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->limit($request->get('limit', 20))
            ->get(['id', 'spd_number', 'destination', 'departure_date', 'return_date', 'status']);

        return response()->json([
            'success' => true,
            'data' => $spds->map(fn($spd) => [
                'id' => $spd->id,
                'spd_number' => $spd->spd_number,
                'destination' => $spd->destination,
                'dates' => $spd->departure_date->format('d/m') . ' - ' . $spd->return_date->format('d/m/Y'),
                'status' => $spd->status,
                'status_color' => $this->getStatusColor($spd->status),
            ]),
        ]);
    }

    /**
     * SPPD detail for mobile
     */
    public function showSppd(Request $request, Spd $spd): JsonResponse
    {
        $spd->load(['employee', 'unit', 'budget', 'costs']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $spd->id,
                'spd_number' => $spd->spd_number,
                'spt_number' => $spd->spt_number,
                'employee' => $spd->employee?->name,
                'destination' => $spd->destination,
                'purpose' => $spd->purpose,
                'departure_date' => $spd->departure_date->format('d M Y'),
                'return_date' => $spd->return_date->format('d M Y'),
                'duration' => $spd->duration . ' hari',
                'transport' => $spd->transport_type,
                'estimated_cost' => 'Rp ' . number_format($spd->estimated_cost, 0, ',', '.'),
                'status' => $spd->status,
                'status_label' => $spd->status_label,
                'can_submit' => $spd->status === 'draft',
                'can_edit' => $spd->status === 'draft',
            ],
        ]);
    }

    /**
     * Quick actions
     */
    public function quickSubmit(Request $request, Spd $spd): JsonResponse
    {
        if ($spd->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'SPPD tidak dapat disubmit',
            ], 400);
        }

        $spd->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SPPD berhasil disubmit',
        ]);
    }

    public function quickApprove(Request $request, Spd $spd): JsonResponse
    {
        $spd->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->employee_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SPPD berhasil disetujui',
        ]);
    }

    /**
     * Notifications list
     */
    public function notifications(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->limit(20)
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'type' => class_basename($n->type),
                'data' => $n->data,
                'read' => $n->read_at !== null,
                'ago' => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markNotificationRead(Request $request, string $id): JsonResponse
    {
        $request->user()->notifications()->where('id', $id)->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    protected function getPendingApprovals($user): array
    {
        // Count pending approvals for this user
        return [
            'count' => \App\Models\Approval::where('approver_id', $user->employee_id)
                ->where('status', 'pending')
                ->count(),
        ];
    }

    protected function getStatusColor(string $status): string
    {
        return match($status) {
            'draft' => '#64748B',
            'submitted' => '#F59E0B',
            'approved' => '#10B981',
            'rejected' => '#EF4444',
            'completed' => '#3B82F6',
            default => '#64748B',
        };
    }
}
