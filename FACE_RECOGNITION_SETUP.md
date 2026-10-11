# Face Recognition Attendance System - Setup Guide

## Project Status

The face recognition attendance kiosk system has been successfully implemented and is ready to use.

## Installation & Setup

### 1. Download face-api.js Model Files

The system requires face-api.js model files to be placed in `public/models/face-api/`.

Download these files from: https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/

Required files:
- `face-api.min.js` - Main library file
- `tiny_face_detector_model-weights_manifest.json`
- `tiny_face_detector_model-weights_shard1.bin`
- `face_landmark_68_model-weights_manifest.json`
- `face_landmark_68_model-weights_shard1.bin`
- `face_recognition_model-weights_manifest.json`
- `face_recognition_model-weights_shard1.bin`
- `face_expression_model-weights_manifest.json`
- `face_expression_model-weights_shard1.bin`

Place all files in: `public/models/face-api/`

### 2. Run Migrations

```bash
php artisan migrate
```

This will create:
- `face_enrollments` table - stores face biometric data
- Add `photo_path` column to `attendance_logs` table

### 3. Start Development Server

```bash
php artisan serve
```

The application will be available at: `http://localhost:8000`

### 4. Access the Kiosk

- **Kiosk URL**: `http://localhost:8000/kiosk`
- **No login required** - it's a shared terminal for all employees

### 5. Setup Face Enrollment (Admin/HR Only)

1. Login to the application as an HR/Admin user
2. Go to: `/face-enrollment`
3. Select an employee to enroll
4. Click "Enroll/Update" 
5. Allow camera access
6. Stand in front of the camera for face capture
7. System captures 5 stable frames
8. Save the enrollment

### 6. Configure Face Matching Threshold

Edit `config/face-attendance.php` or set environment variable:

```bash
FACE_MATCHING_THRESHOLD=0.55
```

- Lower values (0.40): Stricter matching, may reject employees
- Default (0.55): Balanced
- Higher values (0.70): More lenient, may accept wrong persons

## Files Created

### Models
- `app/Models/FaceEnrollment.php` - Face enrollment model with relationships

### Services
- `app/Modules/Attendance/Services/FaceEnrollmentService.php` - Enrollment management
- `app/Modules/Attendance/Services/FaceRecognitionService.php` - Face matching and validation

### Controllers
- `app/Modules/Attendance/Http/Controllers/FaceKioskController.php` - Kiosk display
- `app/Modules/Attendance/Http/Controllers/FaceEnrollmentController.php` - Enrollment management
- `app/Modules/Attendance/Http/Controllers/Api/FaceAttendanceApiController.php` - API endpoints

### Views
- `resources/views/hr/attendance/kiosk-view.blade.php` - Kiosk interface
- `resources/views/hr/attendance/kiosk-disabled.blade.php` - Disabled message
- `resources/views/hr/attendance/face-enrollment/index.blade.php` - Enrollment management
- `resources/views/hr/attendance/face-enrollment/enroll.blade.php` - Enrollment form

### Configuration
- `config/face-attendance.php` - Face attendance configuration

### Migrations
- `database/migrations/2026_04_25_000100_create_face_enrollments_table.php`
- `database/migrations/2026_04_25_000200_add_photo_path_to_attendance_logs.php`

### Routes Added
```
GET  /kiosk                              - Face attendance kiosk (public)
GET  /face-enrollment                    - Enrollment management (auth, admin)
GET  /face-enrollment/enroll/{employee}  - Enrollment form (auth, admin)
POST /face-enrollment/store              - Save enrollment (auth, admin)
POST /face-enrollment/deactivate/{id}    - Deactivate enrollment (auth, admin)
POST /face-enrollment/delete/{id}        - Delete enrollment (auth, admin)

POST /api/v1/face-attendance/recognize   - Recognize face (public, throttled)
POST /api/v1/face-attendance/record      - Record attendance (public, throttled)
```

## How It Works

### Kiosk Flow

1. **Employee approaches the kiosk**
2. **Camera initializes** and displays live preview
3. **Face detection** - System looks for a face in the frame
4. **Face recognition** - If exactly one face found, system compares against enrolled employees
5. **Match found** - Employee information displays
6. **Action determined** - System determines CHECK-IN or CHECK-OUT based on today's records
7. **Confirmation** - Employee confirms the action
8. **Recorded** - Attendance is recorded with face photo and timestamp
9. **Success message** - Result displays for 5 seconds, then resets for next employee

### Security Features

✓ **Server-side verification** - Browser can't fake employee IDs
✓ **Face descriptor matching** - Uses Euclidean distance with configurable threshold
✓ **Ambiguous match detection** - Rejects if multiple employees match too closely
✓ **Eligibility validation** - Checks employee status, user account, and enrollment
✓ **Sensitive data protection** - Face descriptors not exposed in responses
✓ **Biometric privacy** - Photos stored securely, descriptors never logged
✓ **Rate limiting** - Throttles API to prevent abuse (30 requests/minute)

## Authorization & Permissions

- **Kiosk access**: No authentication needed (shared terminal)
- **Enrollment management**: Requires `attendance.manage` permission
- **Face enrollment**: HR/Admin users only
- **Existing manual attendance**: Still fully functional and unchanged

## Configuration Options

### `config/face-attendance.php`

```php
// Matching threshold (0-1 scale)
'matching_threshold' => 0.55,

// Enrollment settings
'enrollment' => [
    'min_frames' => 5,           // Frames to capture
    'enrollment_timeout' => 120, // Max time in seconds
    'photo_quality_threshold' => 0.3,
],

// Detection settings
'detection' => [
    'max_faces' => 1,           // Only allow one face
    'detection_timeout' => 30,
],

// Storage configuration
'storage' => [
    'disk' => 'local',
    'enrollment_path' => 'face-enrollments',
    'attendance_path' => 'face-attendance',
],

// Enable/disable feature
'enabled' => true,
```

## Database Schema

### `face_enrollments` table

```
- id (primary key)
- employee_id (foreign key to employees)
- face_descriptor (JSON - 128 float values)
- photo_path (path to enrollment reference photo)
- is_active (boolean - only one active per employee)
- enrolled_by (foreign key to users)
- enrolled_at (timestamp)
- timestamps (created_at, updated_at)
```

### `attendance_logs` modifications

- Added: `photo_path` (nullable string - path to attendance snapshot)

## Testing Checklist

- [ ] Navigate to `/kiosk` without login (should work)
- [ ] Camera permission dialog appears
- [ ] Live video preview displays
- [ ] Navigate to `/face-enrollment` as admin
- [ ] Enroll an employee's face (5 frames captured)
- [ ] Return to kiosk and test face recognition
- [ ] Employee details display correctly
- [ ] Attendance recorded successfully
- [ ] Check attendance report shows the face-recorded entry
- [ ] Verify existing manual attendance still works
- [ ] Test with unknown face (shows "not recognized")
- [ ] Test with multiple faces (shows error)
- [ ] Test permission checks (non-admin can't access enrollment)

## Performance & Optimization

- **Face detection**: Uses TinyFaceDetector for fast, lightweight detection
- **Descriptor size**: 128 float values per face (compact)
- **Matching**: Euclidean distance calculation (fast O(n) for comparisons)
- **Model loading**: Happens once on page load
- **Detection loop**: Runs every 500ms to allow other operations
- **API throttling**: 30 requests/minute per IP

## Troubleshooting

### Camera not working
- Check browser permissions
- Ensure HTTPS or localhost (required for camera access)
- Try a different browser
- Verify camera is not in use by another application

### Face not detected
- Ensure adequate lighting
- Face should be clearly visible and centered
- Try moving closer to the camera
- Keep head straight, not tilted

### Face recognized but wrong employee
- Adjust `FACE_MATCHING_THRESHOLD` in config/face-attendance.php
- Re-enroll the employee with better lighting
- Ensure face is well-centered during enrollment

### Enrollment not saving
- Check storage permissions on server
- Verify `storage/app/face-enrollments/` directory exists and is writable
- Check server logs for errors

## Limitations & Notes

1. **Browser-based only** - Requires modern browser with getUserMedia support
2. **Lighting sensitive** - Works best with good lighting
3. **Single face only** - Rejects multiple faces to prevent identity confusion
4. **Enrollment required** - Employee must be enrolled before using kiosk
5. **Not production-grade security** - For HR/payroll use, not high-security applications
6. **Descriptor-based** - Uses face descriptors, not facial recognition accuracy guarantees
7. **Threshold tuning** - May need adjustment for specific environment

## Next Steps

1. Download and place face-api.js model files in `public/models/face-api/`
2. Run `php artisan migrate` to create database tables
3. Start the application with `php artisan serve`
4. Test the kiosk at `/kiosk`
5. Enroll employees via `/face-enrollment` (admin only)
6. Monitor attendance records in the normal attendance report

## Support

For issues or questions, refer to:
- Configuration: `config/face-attendance.php`
- Services: `app/Modules/Attendance/Services/`
- Controllers: `app/Modules/Attendance/Http/Controllers/`
- Views: `resources/views/hr/attendance/`

The implementation follows the existing Laravel patterns and integrates seamlessly with your current attendance system.
