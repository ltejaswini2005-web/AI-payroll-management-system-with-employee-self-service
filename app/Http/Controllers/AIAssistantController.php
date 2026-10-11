<?php

namespace App\Http\Controllers;

use App\Services\AIAssistantService;
use App\Services\HRDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIAssistantController extends Controller
{
    public function __construct(
        private readonly HRDataService $hrDataService,
        private readonly AIAssistantService $aiAssistantService
    ) {
    }

    /**
     * Process user chat message and return AI HR response.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $hrContext = $this->hrDataService->getHRContext($user);
        $reply = $this->aiAssistantService->generateReply($validated['message'], $hrContext, $user);

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'user' => [
                'name' => $user->name,
                'is_admin' => $hrContext['is_admin'],
            ],
        ]);
    }

    /**
     * Get quick summary cards for assistant popup.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $hrContext = $this->hrDataService->getHRContext($user);

        return response()->json([
            'success' => true,
            'employee' => $hrContext['employee'],
            'leave_summary' => $hrContext['leaves']['balances'],
            'attendance_summary' => $hrContext['attendance']['summary'],
            'upcoming_holidays' => array_slice($hrContext['holidays'], 0, 3),
        ]);
    }
}
