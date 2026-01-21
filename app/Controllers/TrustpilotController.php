<?php

namespace Plugins\TrustpilotReview\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Plugins\TrustpilotReview\Services\TrustpilotService;

class TrustpilotController extends Controller
{
    protected $trustpilotService;

    public function __construct(TrustpilotService $trustpilotService)
    {
        $this->trustpilotService = $trustpilotService;
    }

    /**
     * Check if popup should be shown for a server
     *
     * @param Request $request
     * @param int $serverId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPopup(Request $request, $serverId)
    {
        $userId = $request->user()->id;
        $result = $this->trustpilotService->shouldShowPopup($userId, $serverId);

        return response()->json($result);
    }

    /**
     * Dismiss the popup for a server
     *
     * @param Request $request
     * @param int $serverId
     * @return \Illuminate\Http\JsonResponse
     */
    public function dismissPopup(Request $request, $serverId)
    {
        $userId = $request->user()->id;
        $this->trustpilotService->dismissPopup($userId, $serverId);

        return response()->json([
            'success' => true,
            'message' => 'Popup dismissed successfully',
        ]);
    }
}
