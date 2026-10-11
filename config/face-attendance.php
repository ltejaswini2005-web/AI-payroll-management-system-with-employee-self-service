<?php

/**
 * Face Recognition Attendance Configuration
 *
 * This configuration manages face recognition settings for the kiosk attendance system.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Face Matching Threshold
    |--------------------------------------------------------------------------
    |
    | The minimum distance score (0-1) required for a successful face match.
    | Lower values are more strict, higher values are more lenient.
    |
    | Recommended range: 0.4 - 0.6
    | Default: 0.55 (good balance between security and usability)
    |
    | 0.40 = Very strict (may reject legitimate employees)
    | 0.55 = Balanced (recommended)
    | 0.70 = Very lenient (may accept wrong persons)
    |
    */
    'matching_threshold' => env('FACE_MATCHING_THRESHOLD', 0.55),

    /*
    |--------------------------------------------------------------------------
    | Face Enrollment Settings
    |--------------------------------------------------------------------------
    |
    | min_frames: Minimum number of stable face frames required for enrollment
    | enrollment_timeout: Maximum time (seconds) allowed for enrollment process
    | photo_quality_threshold: Minimum face size required in frame (0-1 scale)
    |
    */
    'enrollment' => [
        'min_frames' => 5,
        'enrollment_timeout' => 120,
        'photo_quality_threshold' => 0.3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Face Detection Settings
    |--------------------------------------------------------------------------
    |
    | max_faces: Maximum number of faces allowed in frame for recognition
    | detection_timeout: Timeout for face detection (seconds)
    |
    */
    'detection' => [
        'max_faces' => 1,
        'detection_timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | disk: Filesystem disk for storing enrollment photos and attendance snapshots
    | enrollment_path: Directory path for enrollment photos
    | attendance_path: Directory path for attendance snapshots
    |
    */
    'storage' => [
        'disk' => 'local',
        'enrollment_path' => 'face-enrollments',
        'attendance_path' => 'face-attendance',
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Files Path
    |--------------------------------------------------------------------------
    |
    | Path to face-api.js model files (relative to public directory)
    | These files must be downloaded and stored in the public directory
    |
    */
    'models_path' => '/models/face-api',

    /*
    |--------------------------------------------------------------------------
    | Feature Enabled
    |--------------------------------------------------------------------------
    |
    | Set to false to disable face attendance entirely
    |
    */
    'enabled' => env('FACE_ATTENDANCE_ENABLED', true),
];
