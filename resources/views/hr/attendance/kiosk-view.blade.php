<!DOCTYPE html>
<html>
<head>
    <title>Attendance Kiosk - Face Recognition</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f0f2f5; display:flex; justify-content:center; align-items:center; min-height:100vh; }
        .kiosk { background:white; border-radius:14px; box-shadow:0 12px 45px rgba(0,0,0,0.18); max-width:520px; width:100%; padding:28px; }
        .header { text-align:center; margin-bottom:20px; color:#1a202c; }
        h1 { font-size:26px; margin-bottom:4px; font-weight:700; }
        .subtitle { font-size:12px; color:#718096; text-transform:uppercase; letter-spacing:1px; font-weight:600; }
        .camera-box { background:#000; width:100%; height:320px; border-radius:10px; margin-bottom:18px; position:relative; overflow:hidden; }
        video { width:100%; height:100%; object-fit:cover; }
        canvas { display:none; }
        .status { text-align:center; padding:14px; margin:14px 0; border-radius:8px; font-size:15px; font-weight:500; }
        .status.info { background:#e8f0fe; color:#1a73e8; border:1px solid #c7dcfb; }
        .status.error { background:#fce8e6; color:#d93025; border:1px solid #f9c7c4; }
        .status.success { background:#e6f4ea; color:#137333; border:1px solid #b7e1cd; }
        .status.warning { background:#fef7e0; color:#b06000; border:1px solid #fbdc99; }
        .emp-info { background:#f8fafc; padding:16px; border-radius:8px; margin:14px 0; text-align:center; border:1px solid #e2e8f0; }
        .emp-info .code { font-size:12px; color:#64748b; font-weight:bold; letter-spacing:0.5px; }
        .emp-info .name { font-size:22px; font-weight:bold; color:#1e293b; margin:6px 0; }
        .emp-info .badge { display:inline-block; background:#10b981; color:white; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600; margin-top:4px; }
        .buttons { display:flex; gap:10px; margin-top:18px; }
        button { flex:1; padding:12px; font-size:14px; border:none; border-radius:8px; cursor:pointer; font-weight:600; transition:all 0.2s ease; }
        .btn-ok { background:#4f46e5; color:white; }
        .btn-ok:hover { background:#4338ca; }
        .btn-cancel { background:#64748b; color:white; }
        .btn-cancel:hover { background:#475569; }
        .hidden { display:none; }
        .state { display:none; }
        .state.active { display:block; }
        .spinner { display:inline-block; width:18px; height:18px; border:3px solid #e2e8f0; border-top:3px solid #4f46e5; border-radius:50%; animation:spin 1s linear infinite; vertical-align:middle; margin-right:6px; }
        @keyframes spin { to { transform:rotate(360deg); } }

        /* Camera status UI */
        .detection-debug { font-size:10px; color:#64748b; margin-top:6px; font-weight:normal; }
    </style>
</head>
<body>
    <div class="kiosk">
        <div class="header">
            <h1>📍 Attendance Kiosk</h1>
            <div class="subtitle">AI Face Recognition</div>
        </div>

        <div id="state-init" class="state active">
            <div class="status info"><span class="spinner"></span> <span id="init-status">Loading camera...</span></div>
        </div>

        <div id="state-ready" class="state">
            <div class="camera-box">
                <video id="video" autoplay playsinline></video>
            </div>
            <div class="status info" id="ready-status">Stand in front of the camera to begin</div>
            <div class="detection-debug" id="ready-debug">DEBUG | Waiting for camera frame and face detection.</div>
        </div>

        <div id="state-no-face" class="state">
            <div class="camera-box"><video id="video-noface" autoplay playsinline></video></div>
            <div class="status warning">No face detected. Please look into the camera.</div>
            <div class="detection-debug" id="noface-debug">DEBUG | Waiting for camera frame and face detection.</div>
        </div>

        <div id="state-multiple" class="state">
            <div class="camera-box"><video id="video-multiple" autoplay playsinline></video></div>
            <div class="status error">Multiple faces detected. Only one person at a time, please.</div>
            <div class="detection-debug" id="multiple-debug">DEBUG | Waiting for camera frame and face detection.</div>
        </div>

        <div id="state-processing" class="state">
            <div class="camera-box"><video id="video-processing" autoplay playsinline></video></div>
            <div class="status info"><span class="spinner"></span> Identifying employee...</div>
        </div>

        <div id="state-found" class="state">
            <div class="camera-box"><video id="video-found" autoplay playsinline></video></div>
            <div class="emp-info">
                <div class="code" id="code-display">EMP001</div>
                <div class="name" id="name-display">Employee</div>
                <div class="badge">✓ Face Recognized</div>
            </div>
            <div class="status success" id="action-display">CHECK-IN</div>
            <div class="buttons">
                <button class="btn-ok" onclick="confirmAtt()">Confirm Attendance</button>
                <button class="btn-cancel" onclick="resetState()">Cancel</button>
            </div>
        </div>

        <div id="state-notfound" class="state">
            <div class="status error">Face was not matched to a registered employee.</div>
            <div class="buttons">
                <button class="btn-cancel" onclick="resetState()" style="flex:1">Try Again</button>
            </div>
        </div>

        <div id="state-recorded" class="state">
            <div class="status success" id="recorded-heading">✓ Attendance Recorded Successfully</div>
            <div class="emp-info">
                <div class="code" id="rec-code">EMP001</div>
                <div class="name" id="rec-name">Employee</div>
                <div style="font-size:13px; margin-top:8px; color:#64748b;" id="rec-date"></div>
                <div style="font-size:14px; margin-top:4px; font-weight:600; color:#4f46e5;" id="rec-action"></div>
            </div>
            <div class="buttons">
                <button class="btn-ok" onclick="resetState()" style="flex:1">Start Next Scan</button>
            </div>
        </div>

        <div id="state-error" class="state">
            <div class="status error" id="error-msg">Error occurred</div>
            <div class="buttons">
                <button class="btn-cancel" onclick="window.location.reload()" style="flex:1">Restart Kiosk</button>
            </div>
        </div>
    </div>

    <canvas id="canvas"></canvas>

    <!-- face-api.js: local copy is the primary source; CDN is the fallback -->
    <script>
        window.__faceApiLoaded = false;
        window.__faceApiError  = false;
    </script>
    <script
        src="{{ asset(trim($modelsPath, '/') . '/face-api.min.js') }}"
        onload="window.__faceApiLoaded = true; console.info('[FACE API] Local library loaded');"
        onerror="loadFaceApiFromCDN();"
    ></script>
    <script>
        function loadFaceApiFromCDN() {
            window.__faceApiError = true;
            console.warn('[FACE API] Local library failed to load; trying CDN fallback.');
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/dist/face-api.js';
            s.onload = function() {
                window.__faceApiLoaded = true;
                window.__faceApiError  = false;
                console.info('[FACE API] CDN fallback loaded');
            };
            s.onerror = function() {
                console.error('[FACE API] Local and CDN library loads both failed.');
                document.getElementById('error-msg').textContent =
                    'face-api.js library failed to load (both local and CDN). ' +
                    'Check your internet connection or replace the local file.';
                setState('error');
            };
            document.head.appendChild(s);
        }
    </script>
    <script>
        // Global state
        let stream = null;
        let currentState = 'init';
        let lastRecognized = null;
        let matchThreshold = {{ $matchingThreshold }};
        let modelsLoaded = false;
        let consecutiveDetectionErrors = 0;

        const FACE_DETECTOR_INPUT_SIZE = 320;
        const FACE_DETECTOR_SCORE_THRESHOLD = 0.35;
        let lastDiagnosticLog = 0;
        let lastDetectionStatusLog = 0;
        let detectionLoopTimer = null;
        let detectionInProgress = false;
        let pendingDescriptor = null;

        const VIDEO_IDS = [
            'video',
            'video-noface',
            'video-multiple',
            'video-processing',
            'video-found'
        ];

        function setState(newState) {
            document.querySelectorAll('.state').forEach(s => s.classList.remove('active'));
            const el = document.getElementById('state-' + newState);
            if (el) el.classList.add('active');
            currentState = newState;
            if (newState === 'error' && detectionLoopTimer !== null) {
                clearTimeout(detectionLoopTimer);
                detectionLoopTimer = null;
            }
        }

        function scheduleDetectionLoop(delay) {
            if (detectionLoopTimer !== null || currentState === 'error') return;
            detectionLoopTimer = setTimeout(() => {
                detectionLoopTimer = null;
                detectLoop();
            }, delay);
        }

        async function initCamera() {
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Camera access is unavailable in this browser or page context.');
                }

                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 640 },
                        height: { ideal: 480 }
                    }
                });

                VIDEO_IDS.forEach(vid => {
                    const el = document.getElementById(vid);
                    if (el) {
                        el.srcObject = stream;
                    }
                });

                const video = document.getElementById('video');
                await waitForVideoReady(video);
                await video.play();

                document.getElementById('init-status').textContent = 'Loading face recognition...';
                waitForFaceApi();
            } catch (err) {
                setState('error');
                const message = err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError'
                    ? 'Camera permission was denied. Allow camera access and try again.'
                    : 'Camera could not be started. Check that a camera is connected and available.';
                document.getElementById('error-msg').textContent = message;
            }
        }

        function waitForVideoReady(video) {
            return new Promise((resolve, reject) => {
                const timeout = setTimeout(() => finish(new Error('Camera video did not become ready.')), 10000);

                function finish(error) {
                    clearTimeout(timeout);
                    video.removeEventListener('loadeddata', checkReady);
                    video.removeEventListener('error', handleError);
                    if (error) reject(error);
                    else resolve();
                }

                function checkReady() {
                    if (video.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA && video.videoWidth > 0 && video.videoHeight > 0) {
                        finish();
                    }
                }

                function handleError() {
                    finish(new Error('Camera video failed to load.'));
                }

                video.addEventListener('loadeddata', checkReady);
                video.addEventListener('error', handleError);
                checkReady();
            });
        }

        function waitForFaceApi(attempts) {
            attempts = attempts || 0;
            if (typeof faceapi !== 'undefined') {
                startDetection();
                return;
            }
            if (attempts > 60) {
                // Timed out after ~6 seconds
                document.getElementById('error-msg').textContent =
                    'face-api.js library failed to load. ' +
                    'The local file may be corrupt – replace public/models/face-api/face-api.min.js ' +
                    'with a complete copy from https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/dist/face-api.js';
                setState('error');
                return;
            }
            setTimeout(function() { waitForFaceApi(attempts + 1); }, 100);
        }

        async function startDetection() {
            const modelsPath = '{{ asset(trim($modelsPath, '/')) }}';
            try {
                document.getElementById('init-status').textContent = 'Loading face recognition models...';
                console.info('[FACE MODELS] Loading required models from', modelsPath);
                // faceapi is guaranteed defined here (waitForFaceApi verified it)
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(modelsPath),
                    faceapi.nets.faceLandmark68Net.loadFromUri(modelsPath),
                    faceapi.nets.faceRecognitionNet.loadFromUri(modelsPath),
                    faceapi.nets.faceExpressionNet.loadFromUri(modelsPath),
                ]);

                modelsLoaded = true;
                console.info('[FACE MODELS] TinyFaceDetector, landmarks, recognition, and expression models loaded.');
                setState('ready');
                scheduleDetectionLoop(0);
            } catch (err) {
                console.error('[FACE MODELS] Failed to load models from ' + modelsPath, err);
                document.getElementById('error-msg').textContent = 'Model files failed to load from ' + modelsPath + ': ' + err.message;
                setState('error');
            }
        }

        function updateDetectionDebug(faceDetected, detectionCount = null, video = null, detectorOptions = null) {
            const debugElements = ['ready-debug', 'noface-debug', 'multiple-debug']
                .map(id => document.getElementById(id))
                .filter(Boolean);
            if (debugElements.length === 0) return;

            const videoReady = video
                && video.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA
                && video.videoWidth > 0
                && video.videoHeight > 0;
            const values = {
                face: faceDetected === null ? 'WAITING' : faceDetected ? 'YES' : 'NO',
                detectionCount: Number.isInteger(detectionCount) ? detectionCount : '--',
                videoId: video ? video.id : '--',
                videoReady: videoReady ? 'YES' : 'NO',
                videoSize: video ? video.videoWidth + 'x' + video.videoHeight : '0x0',
                videoReadyState: video ? video.readyState : '--',
                videoPlaying: video ? !video.paused : false,
                videoTime: video ? video.currentTime.toFixed(2) : '--',
                cameraStreamAttached: video ? video.srcObject === stream : false,
                cameraTrackStates: stream
                    ? stream.getVideoTracks().map(track => track.readyState + (track.enabled ? '' : '/disabled')).join(',')
                    : '--',
                videoInActiveState: video
                    ? !!document.getElementById('state-' + currentState)?.classList.contains('active')
                    : false,
                modelsLoaded,
                detectorInputSize: detectorOptions ? detectorOptions.inputSize : '--',
                detectorScoreThreshold: detectorOptions ? detectorOptions.scoreThreshold : '--'
            };

            const text = 'DEBUG | Face: ' + values.face
                + ' (' + values.detectionCount + ' detections)'
                + ' | Video: ' + values.videoId
                + ' | Frame: ' + values.videoReady + ' ' + values.videoSize
                + (video ? (values.videoPlaying ? ' playing' : ' paused') + ' @' + values.videoTime + 's' : '')
                + ' | Detector: ' + values.detectorInputSize + 'px/' + values.detectorScoreThreshold;
            debugElements.forEach(debug => {
                debug.textContent = text;
            });

            if (Date.now() - lastDiagnosticLog >= 1000) {
                console.debug('[FACE DETECTION]', values);
                lastDiagnosticLog = Date.now();
            }
        }

        function logDetectionStatus(message, details) {
            if (Date.now() - lastDetectionStatusLog < 1000) return;
            console.warn(message, details);
            lastDetectionStatusLog = Date.now();
        }

        async function captureDescriptorAndRecognize() {
            const video = document.getElementById('video-processing');
            const detectorOptions = new faceapi.TinyFaceDetectorOptions({
                scoreThreshold: FACE_DETECTOR_SCORE_THRESHOLD,
                inputSize: FACE_DETECTOR_INPUT_SIZE
            });
            const detections = await faceapi.detectAllFaces(video, detectorOptions)
                .withFaceLandmarks()
                .withFaceDescriptors();

            console.debug('[FACE RECOGNITION CAPTURE]', {
                videoId: video.id,
                videoSize: video.videoWidth + 'x' + video.videoHeight,
                detectionCount: detections.length,
                inputSize: detectorOptions.inputSize,
                scoreThreshold: detectorOptions.scoreThreshold
            });

            if (detections.length !== 1) {
                document.getElementById('error-msg').textContent =
                    'A clear single face could not be captured for recognition. Please try again.';
                setState('error');
                return;
            }

            pendingDescriptor = detections[0].descriptor;
            await recognizeFace(pendingDescriptor);
        }

        async function detectLoop() {
            if (!stream || !['ready', 'no-face', 'multiple'].includes(currentState) || !modelsLoaded || detectionInProgress) return;
            const loopStartedAt = performance.now();
            let video = null;
            let detectorOptions = null;
            const detectionState = currentState;
            const videoMap = {
                ready: 'video',
                'no-face': 'video-noface',
                multiple: 'video-multiple'
            };
            const videoId = videoMap[currentState];
            video = document.getElementById(videoId);

            if (!video || video.srcObject !== stream) {
                logDetectionStatus('[FACE DETECTION] Selected video is missing or not attached to the active camera stream.', {
                    state: currentState,
                    videoId,
                    videoFound: !!video,
                    cameraStreamAttached: !!video && video.srcObject === stream,
                    modelsLoaded
                });
                scheduleDetectionLoop(100);
                return;
            }

            detectionInProgress = true;
            try {
                const videoTracks = stream.getVideoTracks();
                if (!videoTracks.some(track => track.readyState === 'live' && track.enabled)) {
                    logDetectionStatus('[FACE DETECTION] Active camera stream has no enabled live video track.', {
                        state: currentState,
                        videoId: video.id,
                        trackStates: videoTracks.map(track => track.readyState + (track.enabled ? '' : '/disabled'))
                    });
                    scheduleDetectionLoop(100);
                    return;
                }

                if (video.paused) {
                    console.info('[FACE DETECTION] Starting playback on selected camera video.', {
                        state: currentState,
                        videoId: video.id
                    });
                    await video.play();
                }

                if (video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA || video.videoWidth === 0 || video.videoHeight === 0) {
                    scheduleDetectionLoop(100);
                    return;
                }

                detectorOptions = new faceapi.TinyFaceDetectorOptions({
                    scoreThreshold: FACE_DETECTOR_SCORE_THRESHOLD,
                    inputSize: FACE_DETECTOR_INPUT_SIZE
                });
                const detections = await faceapi.detectAllFaces(video, detectorOptions).withFaceLandmarks();
                if (currentState !== detectionState) return;
                consecutiveDetectionErrors = 0;

                if (detections.length === 0) {
                    updateDetectionDebug(false, detections.length, video, detectorOptions);
                    setState('no-face');
                } else if (detections.length > 1) {
                    updateDetectionDebug(true, detections.length, video, detectorOptions);
                    setState('multiple');
                } else {
                    updateDetectionDebug(true, detections.length, video, detectorOptions);
                    setState('processing');
                    setTimeout(async () => {
                        try {
                            await captureDescriptorAndRecognize();
                        } catch (err) {
                            document.getElementById('error-msg').textContent =
                                'Face recognition capture failed: ' + err.message;
                            setState('error');
                        }
                    }, 500);
                }
            } catch (err) {
                console.error('[FACE DETECTION ERROR]', {
                    state: currentState,
                    videoId: video ? video.id : '--',
                    cameraStreamAttached: !!video && video.srcObject === stream,
                    cameraTrackStates: stream
                        ? stream.getVideoTracks().map(track => track.readyState + (track.enabled ? '' : '/disabled'))
                        : '--',
                    videoReadyState: video ? video.readyState : '--',
                    videoSize: video ? video.videoWidth + 'x' + video.videoHeight : '--',
                    modelsLoaded,
                    detectorInputSize: detectorOptions ? detectorOptions.inputSize : '--',
                    detectorScoreThreshold: detectorOptions ? detectorOptions.scoreThreshold : '--',
                    error: err
                });
                consecutiveDetectionErrors++;
                if (consecutiveDetectionErrors >= 3) {
                    document.getElementById('error-msg').textContent = 'Face detection failed repeatedly. Check the camera and try again.';
                    setState('error');
                    return;
                }
            } finally {
                detectionInProgress = false;
            }

            if (['ready', 'no-face', 'multiple'].includes(currentState)) {
                scheduleDetectionLoop(Math.max(0, 450 - (performance.now() - loopStartedAt)));
            }
        }

        async function recognizeFace(descriptor) {
            try {
                const response = await fetch('/api/v1/face-attendance/recognize', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        face_descriptor: Array.from(descriptor)
                    })
                });

                const data = await response.json().catch(() => ({}));

                if (data.success) {
                    lastRecognized = data;
                    document.getElementById('code-display').textContent = data.employee.code;
                    document.getElementById('name-display').textContent = data.employee.name;
                    document.getElementById('action-display').textContent = data.attendance_action.toUpperCase();
                    setState('found');
                } else {
                    if (data.error === 'not_recognized' || data.error === 'ambiguous') {
                        setState('notfound');
                    } else {
                        document.getElementById('error-msg').textContent = data.message || 'Recognition request failed (HTTP ' + response.status + ').';
                        setState('error');
                    }
                }
            } catch (err) {
                document.getElementById('error-msg').textContent = 'Recognition failed: ' + err.message;
                setState('error');
            }
        }

        async function confirmAtt() {
            if (!lastRecognized) return;

            try {
                const response = await fetch('/api/v1/face-attendance/record', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        employee_id: lastRecognized.employee.id,
                        action: lastRecognized.attendance_action,
                        face_descriptor: Array.from(pendingDescriptor || [])
                    })
                });

                const data = await response.json().catch(() => ({}));

                if (data.success) {
                    document.getElementById('rec-code').textContent = data.attendance.employee_code;
                    document.getElementById('rec-name').textContent = data.attendance.employee_name;
                    const isCheckIn = data.attendance.action === 'checkin';
                    document.getElementById('recorded-heading').textContent = isCheckIn
                        ? '✓ CHECK IN SUCCESSFUL'
                        : '✓ CHECK OUT SUCCESSFUL';
                    document.getElementById('rec-date').textContent = 'Date: ' + data.attendance.date;
                    document.getElementById('rec-action').textContent =
                        (isCheckIn ? 'Check-In Time: ' : 'Check-Out Time: ') + data.attendance.timestamp;
                    setState('recorded');
                } else {
                    document.getElementById('error-msg').textContent = data.message || 'Attendance submission failed (HTTP ' + response.status + ').';
                    setState('error');
                }
            } catch (err) {
                document.getElementById('error-msg').textContent = 'Error: ' + err.message;
                setState('error');
            }
        }

        function resetState() {
            if (detectionLoopTimer !== null) {
                clearTimeout(detectionLoopTimer);
                detectionLoopTimer = null;
            }
            lastRecognized = null;
            pendingDescriptor = null;
            consecutiveDetectionErrors = 0;
            lastDiagnosticLog = 0;
            const readyStatus = document.getElementById('ready-status');
            if (readyStatus) readyStatus.textContent = 'Stand in front of the camera to begin';

            setState('ready');
            scheduleDetectionLoop(100);
        }

        window.addEventListener('load', function() {
            initCamera();
        });
    </script>
</body>
</html>
