<?php

namespace App\Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class FaceKioskController extends Controller
{
    /**
     * Display the face attendance kiosk page.
     * This is a shared terminal - no authentication required.
     */
    public function show(): View
    {
        $config = config('face-attendance');

        if (!$config['enabled']) {
            return view('hr.attendance.kiosk-disabled');
        }

        return view('hr.attendance.kiosk-view', [
            'modelsPath' => $config['models_path'],
            'matchingThreshold' => $config['matching_threshold'],
        ]);
    }
}
