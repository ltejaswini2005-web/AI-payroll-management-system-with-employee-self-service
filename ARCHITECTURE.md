# 🏗️ Face Recognition Attendance System - Architecture

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         SHARED OFFICE KIOSK                             │
│                    (HTML5 + face-api.js + Fetch API)                   │
└─────────────────┬───────────────────────────────────────────────────────┘
                  │
                  │ Browser (JavaScript)
                  │
        ┌─────────▼────────────┐
        │   face-api.js        │
        │ ├─ Face Detection    │
        │ ├─ Descriptor Extract│
        │ └─ Landmarks         │
        └─────────┬────────────┘
                  │
                  │ HTTP POST
                  │
        ┌─────────▼────────────────────────────────────────┐
        │     Laravel Backend (PHP)                        │
        │                                                  │
        │  Routes:                                         │
        │  POST /api/v1/face-attendance/recognize         │
        │  POST /api/v1/face-attendance/record            │
        │                                                  │
        │  Controllers:                                    │
        │  └─ FaceAttendanceApiController                 │
        │     ├─ recognize()                              │
        │     └─ recordAttendance()                        │
        └─────────┬────────────────────────────────────────┘
                  │
                  ▼
        ┌──────────────────────────────────────┐
        │      Services (Business Logic)      │
        │                                      │
        │  FaceRecognitionService:            │
        │  ├─ findBestMatch()                 │
        │  ├─ validateEmployeeEligibility()   │
        │  ├─ checkForAmbiguousMatches()      │
        │  └─ euclideanDistance()             │
        │                                      │
        │  FaceEnrollmentService:             │
        │  ├─ enrollFace()                    │
        │  ├─ getAllActiveEnrollments()       │
        │  └─ storeEnrollmentPhoto()          │
        │                                      │
        │  AttendanceService:                 │
        │  └─ addManualLog()                  │
        └──────────┬───────────────────────────┘
                   │
        ┌──────────▼──────────────────────────┐
        │      Database Models (ORM)          │
        │                                      │
        │  ├─ Employee                        │
        │  ├─ FaceEnrollment                  │
        │  ├─ AttendanceLog                   │
        │  └─ User                            │
        └──────────┬──────────────────────────┘
                   │
        ┌──────────▼──────────────────────────┐
        │      SQLite/MySQL Database          │
        │                                      │
        │  Tables:                             │
        │  ├─ face_enrollments                │
        │  ├─ attendance_logs                 │
        │  ├─ employees                       │
        │  └─ users                           │
        └─────────────────────────────────────┘
```

---

## Component Structure

### Frontend Layer
```
┌─ kiosk-view.blade.php
│  ├─ HTML5 Video Element (camera stream)
│  ├─ Canvas (frame capture)
│  ├─ JavaScript State Machine
│  │  ├─ State: init
│  │  ├─ State: ready
│  │  ├─ State: no-face
│  │  ├─ State: multiple
│  │  ├─ State: processing
│  │  ├─ State: found
│  │  ├─ State: notfound
│  │  ├─ State: recorded
│  │  └─ State: error
│  │
│  ├─ face-api.js Integration
│  │  ├─ TinyFaceDetector
│  │  ├─ FaceLandmark68Net
│  │  ├─ FaceRecognitionNet
│  │  └─ FaceExpressionNet
│  │
│  └─ API Calls
│     ├─ POST /api/v1/face-attendance/recognize
│     └─ POST /api/v1/face-attendance/record
│
├─ face-enrollment/index.blade.php
│  ├─ Employee List Table
│  ├─ Statistics Dashboard
│  └─ Enrollment Actions
│
└─ face-enrollment/enroll.blade.php
   ├─ Camera Preview
   ├─ Frame Counter
   ├─ Progress Bar
   └─ Save Button
```

### Backend Layer
```
┌─ Http/Controllers
│  ├─ FaceKioskController
│  │  └─ show() → Display kiosk UI
│  │
│  ├─ FaceEnrollmentController
│  │  ├─ index() → List enrollments
│  │  ├─ showEnrollmentForm()
│  │  ├─ store() → Save enrollment
│  │  ├─ deactivate()
│  │  └─ destroy()
│  │
│  └─ Api/FaceAttendanceApiController
│     ├─ recognize() → Identify employee
│     ├─ recordAttendance() → Create attendance
│     ├─ determineAttendanceState()
│     └─ saveAttendancePhoto()
│
├─ Services
│  ├─ FaceRecognitionService
│  │  ├─ findBestMatch()
│  │  ├─ validateEmployeeEligibility()
│  │  ├─ checkForAmbiguousMatches()
│  │  ├─ euclideanDistance()
│  │  └─ hasActiveEnrollment()
│  │
│  ├─ FaceEnrollmentService
│  │  ├─ enrollFace()
│  │  ├─ getActiveEnrollment()
│  │  ├─ getAllActiveEnrollments()
│  │  ├─ deactivateEnrollment()
│  │  ├─ hasActiveEnrollment()
│  │  ├─ storeEnrollmentPhoto()
│  │  ├─ deleteEnrollment()
│  │  └─ getStatistics()
│  │
│  └─ AttendanceService
│     ├─ addManualLog()
│     ├─ isEntryDateTimeValid()
│     └─ parseEntryDateTime()
│
├─ Models (ORM)
│  ├─ FaceEnrollment
│  │  ├─ employee() → BelongsTo
│  │  ├─ enrolledByUser() → BelongsTo
│  │  └─ getDescriptorAsArray()
│  │
│  ├─ Employee (existing)
│  │  ├─ user()
│  │  ├─ faceEnrollment()
│  │  └─ attendanceLogs()
│  │
│  ├─ AttendanceLog (existing)
│  │  └─ employee()
│  │
│  └─ User (existing)
│     └─ employee()
│
└─ Repositories
   └─ AttendanceRepository (existing)
      └─ Used for querying attendance
```

### Data Layer
```
┌─ Database (SQLite/MySQL)
│
├─ face_enrollments TABLE
│  ├─ id (PK)
│  ├─ employee_id (FK)
│  ├─ face_descriptor (JSON - 128 floats)
│  ├─ photo_path (path to storage)
│  ├─ is_active (boolean)
│  ├─ enrolled_by (FK to users)
│  ├─ enrolled_at (timestamp)
│  ├─ created_at
│  └─ updated_at
│  └─ UNIQUE(employee_id, is_active)
│
├─ attendance_logs TABLE (existing + addition)
│  ├─ id (PK)
│  ├─ employee_id (FK)
│  ├─ attendance_date
│  ├─ check_in_at
│  ├─ check_out_at
│  ├─ status
│  ├─ source ("manual", "face-checkin", "face-checkout", etc.)
│  ├─ photo_path (NEW - optional path to attendance photo)
│  ├─ remarks
│  ├─ approved_by
│  ├─ approved_at
│  ├─ created_at
│  └─ updated_at
│
├─ employees TABLE (existing)
│  ├─ id
│  ├─ employee_code
│  ├─ first_name
│  ├─ last_name
│  ├─ user_id (FK)
│  ├─ employment_status
│  └─ ... other fields
│
└─ users TABLE (existing)
   ├─ id
   ├─ email
   ├─ name
   ├─ account_status
   ├─ roles (pivot table)
   └─ permissions (through roles)
```

---

## Data Flow Diagrams

### Kiosk Recognition Flow

```
[Employee] → [Kiosk Camera]
              ↓
        [face-api.js]
        - Detect faces
        - Extract descriptor (128 floats)
              ↓
        [POST /recognize]
              ↓
        [FaceAttendanceApiController::recognize()]
              ↓
        [FaceRecognitionService::findBestMatch()]
        - Load all active enrollments
        - For each enrollment:
          - Calculate Euclidean distance
          - Track minimum
        - If match within threshold:
          - Check for ambiguous matches
          - Validate employee eligibility
          - Return employee info
        - Else:
          - Return error
              ↓
        [Response JSON]
        {
          success: true/false,
          employee: {id, code, name},
          attendance_action: "checkin" or "checkout",
          match_distance: 0.45
        }
              ↓
        [Frontend]
        - Display employee info
        - Show action
        - Wait for confirmation
```

### Attendance Recording Flow

```
[Employee Confirms] → [POST /record]
                       ↓
        [FaceAttendanceApiController::recordAttendance()]
        - Re-verify face match (security!)
        - Re-validate employee eligibility
        - Verify action is valid for current state
        - Capture photo (if available)
        - Create attendance record
              ↓
        [AttendanceService::addManualLog()]
        - Set attendance_date (today)
        - Set check_in_at or check_out_at
        - Set source = "face-checkin" or "face-checkout"
        - Set photo_path
        - Create database record
              ↓
        [Database INSERT]
        INSERT INTO attendance_logs (...) VALUES (...)
              ↓
        [Response JSON]
        {
          success: true,
          message: "Attendance recorded successfully",
          attendance: {
            employee_name,
            employee_code,
            action,
            timestamp,
            date
          }
        }
              ↓
        [Frontend]
        - Show success message (5 seconds)
        - Reset to ready state
```

### Enrollment Flow

```
[HR/Admin] → [Navigate to /face-enrollment/enroll/{employee}]
             ↓
        [Load Enrollment Form]
        - Camera preview starts
        - Frame counter = 0
             ↓
        [Employee Face in Camera]
             ↓
        [face-api.js]
        - Detect face every 100ms
        - Extract descriptor
        - Store frame (need 5)
             ↓
        [5 Frames Captured]
             ↓
        [Average Descriptors]
        - Calculate mean of all 128 values
        - Result: stable descriptor
             ↓
        [HR Clicks "Save"]
             ↓
        [POST /face-enrollment/store]
             ↓
        [FaceEnrollmentController::store()]
        - Validate employee_id
        - Validate descriptor
        - Deactivate existing active enrollment
        - Create new enrollment record
        - Store reference photo
             ↓
        [Database INSERT]
        INSERT INTO face_enrollments (...) VALUES (...)
             ↓
        [Response JSON]
        {
          success: true,
          message: "Face enrollment successful",
          enrollment_id: 123
        }
             ↓
        [Redirect to /face-enrollment]
        - Status updated to "✓ Enrolled"
```

---

## API Endpoints Detail

### POST /api/v1/face-attendance/recognize

**Request:**
```json
{
  "face_descriptor": [0.23, 0.45, -0.12, ..., 0.34]  // 128 float array
}
```

**Success Response (200):**
```json
{
  "success": true,
  "employee": {
    "id": 5,
    "code": "EMP0001",
    "name": "Tejaswini L"
  },
  "match_distance": 0.35,
  "attendance_action": "checkin",
  "has_open_attendance": false
}
```

**Error Responses:**
```json
// Not recognized
{ "success": false, "error": "not_recognized", "message": "Face not recognized" }

// Ambiguous
{ "success": false, "error": "ambiguous", "message": "Unable to identify the employee. Please try again." }

// Not eligible
{ "success": false, "error": "not_eligible", "message": "Employee is inactive" }
```

### POST /api/v1/face-attendance/record

**Request:**
```json
{
  "employee_id": 5,
  "face_descriptor": [0.23, 0.45, -0.12, ..., 0.34],
  "action": "checkin",
  "photo_data": "data:image/jpeg;base64,..."  // optional
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Attendance recorded successfully",
  "attendance": {
    "employee_name": "Tejaswini L",
    "employee_code": "EMP0001",
    "action": "checkin",
    "timestamp": "09:02 AM",
    "date": "2026-09-30"
  }
}
```

**Error Responses:**
```json
// Verification failed
{ "success": false, "error": "verification_failed", "message": "Face verification failed" }

// Invalid action
{ "success": false, "error": "invalid_action", "message": "Invalid attendance action for current state" }

// Recording failed
{ "success": false, "error": "recording_failed", "message": "Failed to record attendance" }
```

---

## State Machine (Frontend)

```
                 ┌────────────────────────────┐
                 │    STATE: INITIALIZING     │
                 │  "Initializing camera..." │
                 └────────────┬───────────────┘
                              │ Camera loaded
                 ┌────────────▼──────────────┐
                 │    STATE: READY           │
                 │ "Stand in front of camera"│
                 └───┬─────────────┬──────────┘
           No face   │             │  Face detected
                ┌────▼────┐        │
                │ NO_FACE │        │
                │ "No face"│       │
                └────┬────┘        │ Single face?
                     │             │
                     └─────┬───────┤ Multiple faces
                           │       │
                      [WAIT 500ms] │  ┌──────────┐
                           │       │  │ MULTIPLE │
                           │       │  │ "Only 1" │
                           │       │  └────┬─────┘
                           │       │       │
                           └──┬────┼───────┤
                              │    │       │
                              ▼    ▼       │
                        ┌────────────┐     │
                        │ PROCESSING │     │
                        │ "Processing"    │
                        └────────────┘     │
                              │            │
                    ┌──────────┴────────────┘
                    │ (Recognize face)
                    ▼
         ┌──────────────────────┐
         │ FOUND or NOT FOUND    │
         │ if found:             │
         │ ├─ Show employee name│
         │ ├─ Show action       │
         │ └─ Wait for confirm │
         │ if not found:         │
         │ └─ Show "Not recognized"
         └──┬───────────────┬────┘
        YES │               │ CANCEL/TRY AGAIN
            ▼               │
      ┌───────────┐      ┌──▼───┐
      │ RECORDED  │      │READY │
      │ "Success!"│      │(reset)
      │ (5 sec)  │      └──────┘
      └──────┬────┘
             │
             └─→ [RESET TO READY AFTER 5 SEC]
```

---

## Security Layers

```
┌─ Layer 1: Access Control
│  ├─ Kiosk: Public (no auth)
│  ├─ Enrollment: Admin only (middleware check)
│  └─ Permissions: Role-based (permissions middleware)
│
├─ Layer 2: Request Validation
│  ├─ Input validation (face_descriptor array, employee_id int)
│  ├─ Request size limits
│  └─ Content-type verification
│
├─ Layer 3: Face Verification
│  ├─ Server re-verifies face match (don't trust browser)
│  ├─ Euclidean distance calculation (server-side)
│  ├─ Threshold matching (configurable)
│  └─ Ambiguous match detection
│
├─ Layer 4: Employee Validation
│  ├─ Employee exists check
│  ├─ User account active check
│  ├─ Employment status check
│  ├─ Portal access eligibility
│  └─ Active enrollment check
│
├─ Layer 5: Data Protection
│  ├─ Face descriptors never returned (client)
│  ├─ Photos stored in secure location
│  ├─ Descriptors stored encrypted
│  └─ Sensitive data not logged
│
├─ Layer 6: Rate Limiting
│  ├─ 30 requests/minute per IP
│  └─ Prevents brute force attacks
│
└─ Layer 7: Database Security
   ├─ Foreign key constraints
   ├─ Unique constraints
   ├─ Transaction management
   └─ Prepared statements (Eloquent ORM)
```

---

## Performance Optimization

```
┌─ Frontend
│  ├─ face-api.js models loaded once
│  ├─ Detection loop: 500ms interval (allows other work)
│  ├─ Frame capture: 100ms interval (accumulates gradually)
│  └─ Lazy load models: Only when needed
│
├─ Backend
│  ├─ Database indexing: (employee_id, is_active)
│  ├─ N+1 query prevention: Eager loading with()
│  ├─ Caching: Could cache active enrollments
│  └─ Async: Could offload photo processing to queue
│
├─ API
│  ├─ Response size: Minimal JSON
│  ├─ Compression: gzip enabled
│  ├─ Caching headers: Appropriate TTLs
│  └─ CDN: Models served from CDN (Jsdelivr)
│
└─ Database
   ├─ Indexes on frequently queried columns
   ├─ Partitioning by employee_id (future)
   ├─ Archive old attendance (optional)
   └─ Optimize photo storage (S3, etc.)
```

---

## Scaling Considerations

```
Current:
├─ Supports: 100-1000 enrollments
├─ Storage: ~5-50MB (data only)
├─ QPS: 10-30 requests/second
└─ Latency: <500ms per request

To 10,000+ employees:
├─ Database: Migration to dedicated MySQL
├─ Storage: S3 or similar for photos
├─ Caching: Redis for active enrollments
├─ Load Balancing: Multiple Laravel instances
├─ Photos: CDN for image serving
├─ Processing: Queue for photo storage
└─ Monitoring: Metrics collection (New Relic, etc.)
```

---

This architecture ensures:
- ✅ **Security**: Multi-layer verification
- ✅ **Performance**: Optimized queries and caching
- ✅ **Scalability**: Design supports growth
- ✅ **Maintainability**: Clean separation of concerns
- ✅ **Testability**: Each service independently testable
