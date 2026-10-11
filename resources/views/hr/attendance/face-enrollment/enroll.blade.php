@extends('layouts.backend')
@section('title', 'Face Enrollment - ' . ($employee->first_name ?? 'Employee'))

@section('content')
<div class="wrapper-page">
    <div class="page-title">
        <h1><i class="icon-face"></i> Face Enrollment for {{ trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) }}</h1>
    </div>

    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:30px;">
                    <h5 class="mb-4">Capture Face for Recognition</h5>

                    <div style="max-width:500px;">
                        <!-- Camera Stream -->
                        <div id="camera-stream" style="background:#000; border-radius:8px; overflow:hidden; margin-bottom:20px; position:relative;">
                            <video id="enrollment-video" style="width:100%; height:300px; display:block; object-fit:cover;" autoplay playsinline></video>
                        </div>

                        <!-- Status Messages -->
                        <div id="status-message" style="padding:12px; border-radius:6px; margin-bottom:20px; display:none;"></div>

                        <!-- Controls -->
                        <div style="display:flex; gap:10px; margin-bottom:20px;">
                            <button id="btn-start" class="btn btn-custom" onclick="startEnrollment()">
                                <i class="icon-play"></i> Start Camera
                            </button>
                            <button id="btn-stop" class="btn btn-outline-secondary" onclick="stopEnrollment()" disabled>
                                <i class="icon-stop"></i> Stop
                            </button>
                        </div>

                        <!-- Progress -->
                        <div id="progress-section" style="display:none;">
                            <div style="margin-bottom:10px;">
                                <strong>Frames Captured: <span id="frame-count">0</span> / 5</strong>
                            </div>
                            <div class="progress" style="height:20px;">
                                <div id="progress-bar" class="progress-bar bg-success" style="width:0%;"></div>
                            </div>
                            <p style="color:#999; font-size:12px; margin-top:10px;">Align your face with the camera and remain still</p>
                        </div>

                        <!-- Hidden Canvas -->
                        <canvas id="enrollment-canvas" style="display:none;"></canvas>

                        <!-- Submit Section -->
                        <div id="submit-section" style="display:none; margin-top:20px; padding:20px; background:#f0f0f0; border-radius:8px;">
                            <p style="margin-bottom:15px; color:#333;"><strong>✓ Face captured successfully!</strong></p>
                            <button id="btn-save" class="btn btn-custom" onclick="saveEnrollment()" style="width:100%;">
                                <i class="icon-save"></i> Save Enrollment
                            </button>
                            <button class="btn btn-outline-secondary" onclick="resetEnrollment()" style="width:100%; margin-top:10px;">
                                Capture Again
                            </button>
                        </div>

                        <!-- Employee Info -->
                        <div style="background:#f9f9f9; padding:15px; border-radius:6px; margin-top:20px; border-left:4px solid #667eea;">
                            <p><strong>Employee ID:</strong> {{ $employee->id }}</p>
                            <p><strong>Employee Code:</strong> {{ $employee->employee_code }}</p>
                            <p><strong>Name:</strong> {{ trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) }}</p>
                            @if($hasExistingEnrollment)
                                <p style="color:#ff6b6b; margin-top:10px;"><strong>⚠ An active enrollment exists. Saving will replace it.</strong></p>
                            @endif
                        </div>

                        <a href="{{ route('face-enrollment.index') }}" class="btn btn-outline-secondary" style="margin-top:20px; width:100%;">Back to Enrollments</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{--
    Load face-api.js using a root-relative synchronous <script> tag.
    /models/face-api/face-api.min.js is a root-relative path: the browser
    automatically prepends the current host+port (e.g. http://127.0.0.1:8000)
    so it always resolves correctly regardless of APP_URL.
--}}
<script src="/models/face-api/face-api.min.js"></script>
<script>
    const employeeId = {{ $employee->id }};
    // Root-relative model path: browser resolves against its current host+port.
    const modelsPath = '/models/face-api';
    let stream = null;
    let capturedFrames = [];
    let currentDescriptor = null;
    let enrollmentActive = false;
    
    /**
     * Wait for face-api library to be available in the global scope.
     * The face-api.min.js script may take time to fully initialize and register
     * the global faceapi object, especially on slower connections or systems.
     * This function polls with exponential backoff to ensure the library is ready.
     */
    async function waitForFaceApi(maxWaitMs = 10000) {
        const startTime = Date.now();
        const pollIntervals = [50, 100, 200, 500, 500, 500]; // ms delays between checks
        let pollIndex = 0;
        
        while (Date.now() - startTime < maxWaitMs) {
            // Check if faceapi global is available and has the core methods we need
            if (typeof window.faceapi !== 'undefined' && 
                typeof window.faceapi.detectSingleFace === 'function' &&
                typeof window.faceapi.nets !== 'undefined') {
                return true; // Library is ready
            }
            
            // Wait with increasing interval
            const delay = pollIntervals[pollIndex] || 500;
            pollIndex = Math.min(pollIndex + 1, pollIntervals.length - 1);
            await new Promise(resolve => setTimeout(resolve, delay));
        }
        
        return false; // Timeout reached
    }

    async function startEnrollment() {
        try {
            // Wait for face-api to be available, with proper error reporting
            const faceApiReady = await waitForFaceApi();
            
            if (!faceApiReady || typeof window.faceapi === 'undefined') {
                throw new Error(
                    'face-api.js library failed to load or initialize from /models/face-api/face-api.min.js. ' +
                    'The library took longer than expected to load. ' +
                    'Check your browser console (Network tab) for any errors on: ' +
                    window.location.origin + '/models/face-api/face-api.min.js'
                );
            }

            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
            document.getElementById('enrollment-video').srcObject = stream;
            document.getElementById('btn-start').disabled = true;
            document.getElementById('btn-stop').disabled = false;
            document.getElementById('progress-section').style.display = 'block';
            document.getElementById('status-message').style.display = 'none';

            capturedFrames = [];
            updateFrameCount();

            // Load models
            await Promise.all([
                window.faceapi.nets.tinyFaceDetector.loadFromUri(modelsPath),
                window.faceapi.nets.faceLandmark68Net.loadFromUri(modelsPath),
                window.faceapi.nets.faceRecognitionNet.loadFromUri(modelsPath),
            ]);

            enrollmentActive = true;
            captureFrames();
        } catch (err) {
            showStatus('Camera error: ' + err.message, 'error');
        }
    }

    function stopEnrollment() {
        enrollmentActive = false;
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        document.getElementById('btn-start').disabled = false;
        document.getElementById('btn-stop').disabled = true;
    }

    async function captureFrames() {
        const video = document.getElementById('enrollment-video');
        
        // Wait for video to have valid dimensions before starting detection
        const maxWaitMs = 5000;
        const startTime = Date.now();
        while (enrollmentActive && (video.videoWidth === 0 || video.videoHeight === 0)) {
            if (Date.now() - startTime > maxWaitMs) {
                showStatus('Video failed to initialize. Please check camera permissions.', 'error');
                stopEnrollment();
                return;
            }
            await new Promise(r => setTimeout(r, 50));
        }

        while (enrollmentActive && capturedFrames.length < 5) {
            try {
                // Create detector options with default settings suitable for typical camera distance
                const detectorOptions = new window.faceapi.TinyFaceDetectorOptions({
                    inputSize: 416,
                    scoreThreshold: 0.5
                });
                
                const detections = await window.faceapi.detectSingleFace(video, detectorOptions).withFaceLandmarks().withFaceDescriptor();

                if (detections && detections.descriptor) {
                    capturedFrames.push(detections.descriptor);
                    updateFrameCount();

                    if (capturedFrames.length >= 5) {
                        enrollmentActive = false;
                        currentDescriptor = averageDescriptors(capturedFrames);
                        showStatus('Face captured! Ready to save.', 'success');
                        document.getElementById('progress-section').style.display = 'none';
                        document.getElementById('submit-section').style.display = 'block';
                        stopEnrollment();
                        break;
                    }
                }
            } catch (err) {
                console.error('Frame capture error:', err);
            }

            await new Promise(r => setTimeout(r, 100));
        }
    }

    function updateFrameCount() {
        document.getElementById('frame-count').textContent = capturedFrames.length;
        const percent = (capturedFrames.length / 5) * 100;
        document.getElementById('progress-bar').style.width = percent + '%';
    }

    function averageDescriptors(descriptors) {
        if (descriptors.length === 0) return null;
        const avg = new Array(descriptors[0].length).fill(0);
        descriptors.forEach(desc => {
            for (let i = 0; i < desc.length; i++) {
                avg[i] += desc[i];
            }
        });
        return avg.map(v => v / descriptors.length);
    }

    async function saveEnrollment() {
        if (!currentDescriptor) {
            showStatus('No face data captured', 'error');
            return;
        }

        document.getElementById('btn-save').disabled = true;
        showStatus('Saving enrollment...', 'info');

        try {
            const response = await fetch('{{ route("face-enrollment.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    employee_id: employeeId,
                    face_descriptor: currentDescriptor
                })
            });

            const data = await response.json();

            if (data.success) {
                showStatus('✓ Enrollment saved successfully!', 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("face-enrollment.index") }}';
                }, 2000);
            } else {
                showStatus(data.message || 'Failed to save enrollment', 'error');
                document.getElementById('btn-save').disabled = false;
            }
        } catch (err) {
            showStatus('Error: ' + err.message, 'error');
            document.getElementById('btn-save').disabled = false;
        }
    }

    function resetEnrollment() {
        capturedFrames = [];
        currentDescriptor = null;
        document.getElementById('submit-section').style.display = 'none';
        document.getElementById('progress-section').style.display = 'block';
        document.getElementById('btn-start').disabled = false;
        document.getElementById('btn-stop').disabled = true;
        document.getElementById('status-message').style.display = 'none';
        updateFrameCount();
    }

    function showStatus(message, type) {
        const el = document.getElementById('status-message');
        el.textContent = message;
        el.style.display = 'block';
        el.className = 'alert alert-' + (type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info');
    }
</script>
@endsection
