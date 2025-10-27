<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SMSSettingsController extends Controller
{
    /**
     * Display SMS Settings Panel
     */
    public function index()
    {
        return view('admin.sms-settings.index');
    }

    /**
     * Test SMS sending
     */
    public function testSMS(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|max:15',
            'message' => 'required|string|max:500',
        ]);

        try {
            $result = send_sms($request->mobile, $request->message);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'پیامک با موفقیت ارسال شد (یا لاگ شد)',
                    'data' => $result,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'خطا در ارسال پیامک',
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطای سیستمی: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get SMS credit
     */
    public function getCredit()
    {
        try {
            $result = sms()->getCredit();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا: ' . $e->getMessage(),
            ], 500);
        }
    }
}
