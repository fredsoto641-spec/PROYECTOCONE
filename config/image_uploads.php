<?php

return [
    'disk' => env('IMAGE_UPLOAD_DISK', 'public'),
    'max_kilobytes' => (int) env('IMAGE_UPLOAD_MAX_KB', 10240),
    'max_width' => (int) env('IMAGE_UPLOAD_MAX_WIDTH', 6000),
    'max_height' => (int) env('IMAGE_UPLOAD_MAX_HEIGHT', 6000),
    'max_pixels' => (int) env('IMAGE_UPLOAD_MAX_PIXELS', 25000000),
    'output_max_width' => (int) env('IMAGE_UPLOAD_OUTPUT_MAX_WIDTH', 2400),
    'output_max_height' => (int) env('IMAGE_UPLOAD_OUTPUT_MAX_HEIGHT', 2400),
    'webp_quality' => (int) env('IMAGE_UPLOAD_WEBP_QUALITY', 85),
    'allowed_mimes' => [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/webp' => ['webp'],
    ],
];
