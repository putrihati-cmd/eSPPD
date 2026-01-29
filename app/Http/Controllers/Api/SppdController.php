<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SppdResource;
use App\Models\Spd;
use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class SppdController extends Controller
{
    /**
     * GET /api/sppd - List SPPD dengan filter
     */
    public function index(Request $request): JsonResponse
    {
        $query = Spd::with(['employee', 'unit', 'budget']);

        // Search by SPPD number
        if ($request->has('search')) {
            $query->where('spd_number', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('departure_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('departure_date', '<=', $request->to_date);
        }

        // Filter by user/employee
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Sort by departure date descending
        $query->orderBy('departure_date', 'desc');

        // Pagination
        $perPage = $request->get('per_page', 10);
        $spds = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => SppdResource::collection($spds),
            'meta' => [
                'current_page' => $spds->currentPage(),
                'last_page' => $spds->lastPage(),
                'per_page' => $spds->perPage(),
                'total' => $spds->total(),
            ],
        ]);
    }

    /**
     * POST /api/sppd - Create new SPPD
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid|exists:employees,id',
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string',
            'departure_date' => 'required|date|after:today',
            'return_date' => 'required|date|after_or_equal:departure_date',
            'transport_type' => 'required|in:pesawat,kereta,bus,mobil_dinas,kapal',
            'budget_id' => 'required|uuid|exists:budgets,id',
            'invitation_number' => 'nullable|string',
        ]);

        // Get employee to get organization and unit
        $employee = \App\Models\Employee::find($validated['employee_id']);

        // Auto-generate nomor SPPD
        $year = now()->format('Y');
        $month = now()->format('m');
        $count = Spd::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
        $spdNumber = sprintf("SPD/%s/%s/%03d", $year, $month, $count);
        $sptNumber = sprintf("SPT/%s/%s/%03d", $year, $month, $count);

        $duration = Carbon::parse($validated['departure_date'])
            ->diffInDays(Carbon::parse($validated['return_date'])) + 1;

        /** @var \App\Models\User $authUser */
        $authUser = $request->user();

        $spd = Spd::create([
            ...$validated,
            'organization_id' => $employee->organization_id,
            'unit_id' => $employee->unit_id,
            'spd_number' => $spdNumber,
            'spt_number' => $sptNumber,
            'duration' => $duration,
            'status' => 'draft',
            'created_by' => $authUser->id,
            'estimated_cost' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SPPD berhasil dibuat',
            'data' => new SppdResource($spd->load(['employee', 'unit', 'budget'])),
        ], 201);
    }

    /**
     * GET /api/sppd/{id} - Get detail SPPD
     */
    public function show(Spd $spd): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new SppdResource($spd->load(['employee', 'unit', 'budget', 'costs', 'approvals'])),
        ]);
    }

    /**
     * PUT /api/sppd/{id} - Update SPPD (hanya jika status draft)
     */
    public function update(Request $request, Spd $spd): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // SECURITY: Only owner or admin can update
        if ($spd->employee_id !== $user->employee_id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengubah SPPD ini',
            ], 403);
        }

        if ($spd->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya SPPD dengan status draft yang bisa diubah',
            ], 403);
        }

        $validated = $request->validate([
            'destination' => 'sometimes|string|max:255',
            'purpose' => 'sometimes|string',
            'departure_date' => 'sometimes|date',
            'return_date' => 'sometimes|date|after_or_equal:departure_date',
            'transport_type' => 'sometimes|in:pesawat,kereta,bus,mobil_dinas,kapal',
            'budget_id' => 'sometimes|exists:budgets,id',
        ]);

        if (isset($validated['departure_date']) || isset($validated['return_date'])) {
            $departureDate = $validated['departure_date'] ?? $spd->departure_date;
            $returnDate = $validated['return_date'] ?? $spd->return_date;
            $validated['duration'] = Carbon::parse($departureDate)->diffInDays(Carbon::parse($returnDate)) + 1;
        }

        $spd->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'SPPD berhasil diupdate',
            'data' => new SppdResource($spd->fresh(['employee', 'unit', 'budget'])),
        ]);
    }

    public function destroy(Request $request, Spd $spd): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // SECURITY: Only owner or admin can delete
        if ($spd->employee_id !== $user->employee_id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus SPPD ini',
            ], 403);
        }

        if ($spd->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya SPPD dengan status draft yang bisa dihapus',
            ], 403);
        }

        $spd->delete();

        return response()->json([
            'success' => true,
            'message' => 'SPPD berhasil dihapus',
        ]);
    }

    /**
     * POST /api/sppd/{id}/submit - Submit untuk approval
     */
    public function submit(Request $request, Spd $spd): JsonResponse
    {
        $user = $request->user();

        // SECURITY: Only owner or admin can submit
        if ($spd->employee_id !== $user->employee_id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat mengajukan SPPD milik Anda sendiri',
            ], 403);
        }

        if ($spd->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya SPPD draft yang bisa disubmit',
            ], 403);
        }

        $spd->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SPPD berhasil diajukan untuk approval',
            'data' => new SppdResource($spd->fresh()),
        ]);
    }

    /**
     * POST /api/sppd/{id}/approve - Approve SPPD
     */
    public function approve(Request $request, Spd $spd): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Check pending approval
        $approval = $spd->approvals()->where('status', 'pending')->first();
        if (!$approval) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada approval pending untuk SPPD ini',
            ], 404);
        }

        // Security Check: Ensure user is the assigned approver or Admin
        if ($approval->approver_id !== $user->employee_id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki kewenangan untuk menyetujui dokumen ini',
            ], 403);
        }

        $approval->update([
            'status' => 'approved',
            'approved_at' => now(),
            'notes' => $request->notes,
        ]);

        $spd->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'SPPD berhasil disetujui',
            'data' => new SppdResource($spd->fresh()),
        ]);
    }

    /**
     * POST /api/sppd/{id}/reject - Reject SPPD
     */
    public function reject(Request $request, Spd $spd): JsonResponse
    {
        $validated = $request->validate([
            'alasan_penolakan' => 'required|string|min:10',
        ]);

        $approval = $spd->approvals()->where('status', 'pending')->first();
        if (!$approval) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada approval pending untuk SPPD ini',
            ], 404);
        }

        // SECURITY: Check authorization
        /** @var \App\Models\User $user */
        $user = $request->user();
        if ($approval->approver_id !== $user->employee_id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki kewenangan untuk menolak dokumen ini',
            ], 403);
        }

        $approval->update([
            'status' => 'rejected',
            'approved_at' => now(),
            'notes' => $validated['alasan_penolakan'],
        ]);

        $spd->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SPPD ditolak',
            'data' => new SppdResource($spd->fresh()),
        ]);
    }

    /**
     * POST /api/sppd/{id}/complete - Mark as completed
     */
    public function complete(Request $request, Spd $spd): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // SECURITY: Only Admin or Finance can mark as completed
        if (!$user->isFinance() && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Admin atau Bagian Keuangan yang dapat menyelesaikan SPPD',
            ], 403);
        }

        if ($spd->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya SPPD yang sudah approved yang bisa diselesaikan',
            ], 403);
        }

        $spd->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SPPD berhasil diselesaikan',
            'data' => new SppdResource($spd->fresh()),
        ]);
    }

    /**
     * GET /api/sppd/{id}/approvals - List approvals for an SPPD
     */
    public function listApprovals(Spd $spd): JsonResponse
    {
        $approvals = $spd->approvals()->with('approver')->get();

        return response()->json([
            'success' => true,
            'data' => $approvals,
        ]);
    }

    /**
     * POST /api/sppd/{id}/approvals - Create approval for an SPPD
     */
    public function storeApproval(Request $request, Spd $spd): JsonResponse
    {
        $user = $request->user();

        // Only approvers or admins can approve
        if ($user->role !== 'approver' && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menyetujui SPPD',
            ], 403);
        }

        // Check if user is the SPD creator (cannot approve own SPPD)
        if ($spd->employee_id === $user->employee_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menyetujui SPPD milik Anda sendiri',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string',
            'level' => 'nullable|integer',
        ]);

        $approval = Approval::create([
            'spd_id' => $spd->id,
            'approver_id' => $user->employee_id,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'level' => $validated['level'] ?? 1,
            'approved_at' => now(),
        ]);

        // Update SPPD status based on approval status
        if ($validated['status'] === 'approved') {
            $spd->update(['status' => 'approved']);
        } elseif ($validated['status'] === 'rejected') {
            $spd->update(['status' => 'rejected']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Approval berhasil dibuat',
            'data' => $approval,
        ], 201);
    }

    /**
     * POST /api/sppd/{id}/export-pdf - Export SPPD to PDF
     */
    public function exportPdf(Request $request, Spd $spd): JsonResponse
    {
        // Queue a PDF generation job
        $user = $request->user();
        \App\Jobs\GenerateSpdPdfJob::dispatch($spd, $user);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan export PDF sedang diproses. Anda akan menerima notifikasi ketika siap diunduh.',
        ]);
    }
}
