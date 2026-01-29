<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SmartImportService;
use Illuminate\Http\JsonResponse;

class SmartImportController extends Controller
{
    private SmartImportService $importService;
    
    public function __construct(SmartImportService $importService)
    {
        $this->importService = $importService;
    }
    
    /**
     * Show smart import form
     */
    public function index()
    {
        $serviceHealthy = $this->importService->healthCheck();
        
        return view('import.smart', [
            'serviceHealthy' => $serviceHealthy
        ]);
    }
    
    /**
     * Upload file for analysis
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:51200' // 50MB
        ]);
        
        $result = $this->importService->uploadFile($request->file('file'));
        
        if (isset($result['error'])) {
            return response()->json($result, 500);
        }
        
        return response()->json($result);
    }
    
    /**
     * Update column mapping
     */
    public function updateMapping(Request $request): JsonResponse
    {
        $request->validate([
            'job_id' => 'required|string',
            'mapping' => 'required|array'
        ]);
        
        $result = $this->importService->updateMapping(
            $request->input('job_id'),
            $request->input('mapping')
        );
        
        return response()->json($result);
    }
    
    /**
     * Validate data
     */
    public function validate(string $jobId): JsonResponse
    {
        $result = $this->importService->validateData($jobId);
        
        return response()->json($result);
    }
    
    /**
     * Process import
     */
    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'job_id' => 'required|string',
            'skip_errors' => 'boolean'
        ]);
        
        $result = $this->importService->processImport(
            $request->input('job_id'),
            $request->boolean('skip_errors')
        );
        
        return response()->json($result);
    }
    
    /**
     * Rollback import
     */
    public function rollback(string $jobId): JsonResponse
    {
        $result = $this->importService->rollback($jobId);
        
        return response()->json($result);
    }
    
    /**
     * Get job status
     */
    public function status(string $jobId): JsonResponse
    {
        $result = $this->importService->getStatus($jobId);
        
        return response()->json($result);
    }
}
