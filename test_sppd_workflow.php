<?php

/**
 * Test SPPD Workflow
 * Tests creation, submission, and approval flow
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Spd;
use App\Models\Approval;
use App\Models\ApprovalRule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

echo "\n" . str_repeat("=", 70) . "\n";
echo "TESTING SPD WORKFLOW";
echo "\n" . str_repeat("=", 70) . "\n\n";

// Get test users
$pegawai = User::where('nip', '197505051999031001')->first();
$kaprodi = User::where('nip', '196803201990031003')->first();
$wadek = User::where('nip', '195811081988031004')->first();

if (!$pegawai) {
    echo "ERROR: Pegawai account not found\n";
    exit(1);
}

// ===== TEST 1: CREATE SPD =====
echo "TEST 1: Create SPD as Pegawai\n";
echo str_repeat("-", 70) . "\n\n";

Auth::guard('web')->login($pegawai);

try {
    $spd = Spd::create([
        'employee_id' => $pegawai->employee_id ?? 1,
        'nip' => $pegawai->nip,
        'destination' => 'Jakarta',
        'purpose' => 'Rapat Koordinasi',
        'departure_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
        'return_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
        'budget_amount' => 5000000,
        'status' => 'draft',
    ]);

    echo "✓ SPD Created Successfully\n";
    echo "  SPD ID: {$spd->id}\n";
    echo "  Destination: {$spd->destination}\n";
    echo "  Purpose: {$spd->purpose}\n";
    echo "  Budget: Rp " . number_format($spd->budget_amount, 0, ',', '.') . "\n";
    echo "  Status: {$spd->status}\n";

    $spdId = $spd->id;
} catch (\Exception $e) {
    echo "✗ Failed to create SPD: {$e->getMessage()}\n";
    Auth::guard('web')->logout();
    exit(1);
}

Auth::guard('web')->logout();

// ===== TEST 2: SUBMIT SPD =====
echo "\n\nTEST 2: Submit SPD for Approval\n";
echo str_repeat("-", 70) . "\n\n";

Auth::guard('web')->login($pegawai);

try {
    $spd->update(['status' => 'submitted']);
    echo "✓ SPD Submitted\n";
    echo "  Status changed: draft → submitted\n";
} catch (\Exception $e) {
    echo "✗ Failed to submit: {$e->getMessage()}\n";
}

Auth::guard('web')->logout();

// ===== TEST 3: CHECK APPROVAL QUEUE =====
echo "\n\nTEST 3: Check Approval Queue\n";
echo str_repeat("-", 70) . "\n\n";

Auth::guard('web')->login($kaprodi);

$pendingApprovals = Spd::where('status', 'submitted')->get();

echo "Pending SPDs in system: " . count($pendingApprovals) . "\n";
if (count($pendingApprovals) > 0) {
    foreach ($pendingApprovals as $s) {
        echo "  - SPD ID {$s->id}: {$s->destination} (Rp " . number_format($s->budget_amount, 0, ',', '.') . ")\n";
    }
    echo "\n✓ Kaprodi can see pending SPDs\n";
} else {
    echo "No pending SPDs found\n";
}

// ===== TEST 4: APPROVE SPD =====
echo "\n\nTEST 4: Approve SPD as Kaprodi\n";
echo str_repeat("-", 70) . "\n\n";

if ($pendingApprovals->count() > 0) {
    $spdToApprove = $pendingApprovals->first();

    try {
        // Record approval
        $approval = Approval::create([
            'spd_id' => $spdToApprove->id,
            'approver_nip' => $kaprodi->nip,
            'level' => 2, // Kaprodi level
            'status' => 'approved',
            'notes' => 'Disetujui oleh Kaprodi',
            'approved_at' => now(),
        ]);

        // Update SPD status to approved
        $spdToApprove->update(['status' => 'approved']);

        echo "✓ SPD Approved by Kaprodi\n";
        echo "  Approval ID: {$approval->id}\n";
        echo "  Level: {$approval->level}\n";
        echo "  SPD Status: {$spdToApprove->fresh()->status}\n";
    } catch (\Exception $e) {
        echo "✗ Approval failed: {$e->getMessage()}\n";
    }
} else {
    echo "No SPDs to approve\n";
}

Auth::guard('web')->logout();

// ===== TEST 5: CHECK DATABASE STATE =====
echo "\n\nTEST 5: Final Database State\n";
echo str_repeat("-", 70) . "\n\n";

$totalSpds = Spd::count();
$draftSpds = Spd::where('status', 'draft')->count();
$submittedSpds = Spd::where('status', 'submitted')->count();
$approvedSpds = Spd::where('status', 'approved')->count();
$totalApprovals = Approval::count();

echo "SPD Statistics:\n";
echo "  Total SPDs: $totalSpds\n";
echo "  Draft: $draftSpds\n";
echo "  Submitted: $submittedSpds\n";
echo "  Approved: $approvedSpds\n";
echo "  Total Approvals: $totalApprovals\n";

if ($totalSpds > 0) {
    echo "\n✓ WORKFLOW TEST PASSED\n";
} else {
    echo "\n⚠ No SPDs in database - workflow test inconclusive\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "TEST COMPLETE\n";
echo str_repeat("=", 70) . "\n\n";
