<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutocompleteRequest;
use App\Http\Requests\SelectSuggestionRequest;
use App\Models\SearchUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Search Autocomplete (0 Billable Usage - PRD Section 3)
     */
    public function search_auto_complete(AutocompleteRequest $request): JsonResponse
    {
        try {
            $search = trim($request->validated()['q']);
            $results = DB::table('records')->select(['id', 'content'])->where('content', 'LIKE', '%' . $search . '%')->limit(10)->get();

            return response()->json(['success' => true, 'message' => 'Search results fetched successfully', 'data' => $results], 200);
        } catch (\Throwable $e) {
            Log::error('Autocomplete search failed', ['error' => $e->getMessage(), 'search' => $request->input('q')]);

            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }

    public function select_suggestion(SelectSuggestionRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $recordId = $validated['record_id'];
            $idempotencyKey = $validated['idempotency_key'] ?? null;
            if ($idempotencyKey) {
                $existing = SearchUsage::where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Selection already recorded for this key (Idempotent)',
                        'data' => [
                            'usage_id' => $existing->id,
                            'record_id' => $existing->record_id,
                            'total_billable_usages' => SearchUsage::count(),
                        ],
                    ], 200);
                }
            }

            $sessionId = $request->hasSession() ? $request->session()->getId() : $request->header('X-Session-ID');
            $usage = SearchUsage::create([
                'record_id' => $recordId,
                'user_id' => $request->user()?->id,
                'session_id' => $sessionId,
                'idempotency_key' => $idempotencyKey,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $totalUsages = SearchUsage::count();

            return response()->json([
                'success' => true,
                'message' => 'Suggestion selected and 1 billable API usage recorded successfully',
                'data' => [
                    'usage_id' => $usage->id,
                    'record_id' => $usage->record_id,
                    'total_billable_usages' => $totalUsages,
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Select suggestion tracking failed', [
                'error' => $e->getMessage(),
                'record_id' => $request->input('record_id'),
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to record usage.'], 500);
        }
    }

    public function usage_summary(): JsonResponse
    {
        $total = SearchUsage::count();
        $recent = SearchUsage::with('record:id,content')->latest()->limit(5)->get();

        return response()->json(['success' => true, 'total_billable_usages' => $total, 'recent_selections' => $recent], 200);
    }
}
