# face-api.js Model Files

Place the downloaded face-api.js model files in this directory.

## Download from CDN

Download files from: https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/

Required files:
1. face-api.min.js
2. tiny_face_detector_model-weights_manifest.json
3. tiny_face_detector_model-weights_shard1.bin
4. face_landmark_68_model-weights_manifest.json
5. face_landmark_68_model-weights_shard1.bin
6. face_recognition_model-weights_manifest.json
7. face_recognition_model-weights_shard1.bin
8. face_expression_model-weights_manifest.json
9. face_expression_model-weights_shard1.bin

## Alternative: Using curl/wget

```bash
# From the project root
cd public/models/face-api
curl -O https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/face-api.min.js
curl -O https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/tiny_face_detector_model-weights_manifest.json
curl -O https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/tiny_face_detector_model-weights_shard1.bin
curl -O https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/face_landmark_68_model-weights_manifest.json
curl -O https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/face_landmark_68_model-weights_shard1.bin
curl -O https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/face_recognition_model-weights_manifest.json
curl -O https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/face_recognition_model-weights_shard1.bin
curl -O https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/face_expression_model-weights_manifest.json
curl -O https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/face_expression_model-weights_shard1.bin
```

Once downloaded, the kiosk will be fully functional at http://localhost:8000/kiosk
