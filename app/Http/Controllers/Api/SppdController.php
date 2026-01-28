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
            'employee_id' => 'required|exists:employees,id',
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string',
            'departure_date' => 'required|date|after:today',
            'return_date' => 'required|date|after_or_equal:departure_date',
            'transport_type' => 'required|in:pesawat,kereta,bus,mobil_dinas,kapal',
            'budget_id' => 'required|exists:budgets,id',
            'invitation_number' => 'nullable|string',
        ]);

        // Auto-generate nomor SPPD
        $year = now()->format('Y');
        $month = now()->format('m');
        $count = Spd::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
        $spdNumber = sprintf("SPD/%s/%s/%03d", $year, $month, $count);
        $sptNumber = sprintf("SPT/%s/%s/%03d", $year, $month, $count);

        // Calculate duration
        $duration = Carbon::parse($validated['departure_date'])
            ->diffInDays(Carbon::parse($validated['return_date'])) + 1;

        $spd = Spd::create([
            ...$validated,
            'spd_number' => $spdNumber,
            'spt_number' => $sptNumber,
            'duration' => $duration,
            'status' => 'draft',
            'created_by' => auth()->id(),
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

    /**
     * DELETE /api/sppd/{id} - Delete SPPD (hanya jika status draft)
     */
    public function destroy(Spd $spd): JsonResponse
    {
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
    public function submit(Spd $spd): JsonResponse
    {
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

        // Create approval record
        Approval::create([
            'spd_id' => $spd->id,
            'approver_id' => $spd->employee->supervisor_id ?? null,
            'level' => 1,
            'status' => 'pending',
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
        $user = auth()->user();

        // Check pending approval
        $approval = $spd->approvals()->where('status', 'pending')->first();
        if (!$approval) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada approval pending untuk SPPD ini',
            ], 404);
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
        if ($approval) {
            $approval->update([
                'status' => 'rejected',
                'approved_at' => now(),
                'notes' => $validated['alasan_penolakan'],
            ]);
        }

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
    public function complete(Spd $spd): JsonResponse
    {
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
}
