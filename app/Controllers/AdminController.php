<?php

namespace Plugins\TrustpilotReview\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Display admin settings page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $settings = $this->getSettings();
        return view('trustpilot::admin.settings', compact('settings'));
    }

    /**
     * Get current settings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettings()
    {
        $settingsArray = DB::table('trustpilot_settings')->pluck('value', 'key')->toArray();

        return [
            'days_before_expiry' => $settingsArray['days_before_expiry'] ?? 7,
            'review_url' => $settingsArray['review_url'] ?? '',
            'api_key' => $settingsArray['api_key'] ?? '',
            'enabled' => (bool) ($settingsArray['enabled'] ?? true),
        ];
    }

    /**
     * Update settings
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'days_before_expiry' => 'required|integer|min:1|max:365',
            'review_url' => 'required|url',
            'api_key' => 'nullable|string',
            'enabled' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings = [
            'days_before_expiry' => $request->input('days_before_expiry'),
            'review_url' => $request->input('review_url'),
            'api_key' => $request->input('api_key'),
            'enabled' => $request->input('enabled') ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            DB::table('trustpilot_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'updated_at' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'settings' => $this->getSettings(),
        ]);
    }

    /**
     * Get dismissal statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        $totalDismissals = DB::table('trustpilot_dismissals')->count();
        $uniqueUsers = DB::table('trustpilot_dismissals')->distinct('user_id')->count();

        return response()->json([
            'total_dismissals' => $totalDismissals,
            'unique_users' => $uniqueUsers,
        ]);
    }
}
