<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class CRMController extends Controller
{
    public function requestToCRM(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'phone' => 'required|string',
                'project' => 'required|string',
            ]);

            $phone = $this->formatPhone($request->phone);

            $this->sendToCRM($request, $phone);

            return response()->json([
                'message' => 'Request sent to CRM successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    private function sendToCRM($request, $phone)
    {
        if (Cache::has("phone-{$phone}")) {
            session()->flash('success');
            return redirect()->back();
        }

        $project = $request->input('project');
        $project = str_replace(' ', '-', $project);

        Cache::put("phone-{$phone}", 300, 300);

        $title = "Заявка с $project";

        $name = $request->input('name');

        $utm_campaign = $request->input('utm_campaign') ?? "";
        $utm_medium = $request->input('utm_medium') ?? "";
        $utm_source = $request->input('utm_source') ?? "";

        try {
            Http::post('http://connector-amo.xonsaroy.uz/bitrix-site', [
                'first_name' => $name,
                'phone' => $phone,
                'title' => $title,
                'utm_campaign' => $utm_campaign,
                'utm_medium' => $utm_medium,
                'utm_source' => $utm_source,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to send request to CRM');
        }
    }

    private function formatPhone($number)
    {
        // get only numbers
        $number = (int) filter_var($number, FILTER_SANITIZE_NUMBER_INT);

        if (strlen($number) == 13 && strpos($number, '+') === 0) {
            $number = substr($number, 0, 4) . ' ' . substr($number, 4, 2) . ' ' . substr($number, 6, 3) . ' ' . substr($number, 9, 2) . ' ' . substr($number, 11);
        } elseif (strlen($number) == 12 && strpos($number, '+') === false) {
            $number = substr($number, 0, 3) . ' ' . substr($number, 3, 2) . ' ' . substr($number, 5, 3) . ' ' . substr($number, 8, 2) . ' ' . substr($number, 10);
        } elseif (strlen($number) == 9) {
            $number = substr($number, 0, 2) . ' ' . substr($number, 2, 3) . ' ' . substr($number, 5, 2) . ' ' . substr($number, 7);
        } elseif (strlen($number) == 7) {
            $number = substr($number, 0, 3) . ' ' . substr($number, 3, 2) . ' ' . substr($number, 5);
        } else {
            $number = $number;
        }

        return $number;
    }
}
