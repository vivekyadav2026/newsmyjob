<?php
/**
 * File Upload Functions
 */

declare(strict_types=1);

/**
 * Upload file with validation
 */
function uploadFile(array $file, string $directory, array $allowedTypes, int $maxSize = MAX_UPLOAD_SIZE): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload failed with error code: ' . $file['error']];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size exceeds maximum allowed size.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes, true)) {
        return ['success' => false, 'message' => 'File type not allowed.'];
    }

    $uploadDir = UPLOADS_PATH . '/' . trim($directory, '/');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('file_', true) . '.' . strtolower($extension);
    $filepath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Failed to move uploaded file.'];
    }

    $relativePath = trim($directory, '/') . '/' . $filename;

    return [
        'success'   => true,
        'filename'  => $filename,
        'path'      => $relativePath,
        'mime_type' => $mimeType,
        'size'      => $file['size'],
    ];
}

/**
 * Upload image
 */
function uploadImage(array $file, string $directory = 'news'): array
{
    return uploadFile($file, $directory, ALLOWED_IMAGE_TYPES);
}

/**
 * Upload document/PDF
 */
function uploadDocument(array $file, string $directory = 'media'): array
{
    $types = array_merge(ALLOWED_DOC_TYPES, ALLOWED_IMAGE_TYPES);
    return uploadFile($file, $directory, $types);
}

/**
 * Upload video
 */
function uploadVideo(array $file, string $directory = 'media'): array
{
    return uploadFile($file, $directory, ALLOWED_VIDEO_TYPES, 50 * 1024 * 1024);
}

/**
 * Delete uploaded file
 */
function deleteFile(string $path): bool
{
    $fullPath = UPLOADS_PATH . '/' . ltrim($path, '/');
    if (file_exists($fullPath) && is_file($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Resize image (basic resize using GD)
 */
function resizeImage(string $sourcePath, string $destPath, int $maxWidth = 1200, int $maxHeight = 800): bool
{
    if (!extension_loaded('gd')) {
        return false;
    }

    $info = getimagesize($sourcePath);
    if (!$info) {
        return false;
    }

    [$width, $height, $type] = $info;

    if ($width <= $maxWidth && $height <= $maxHeight) {
        return copy($sourcePath, $destPath);
    }

    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int) ($width * $ratio);
    $newHeight = (int) ($height * $ratio);

    $source = match ($type) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
        IMAGETYPE_PNG  => imagecreatefrompng($sourcePath),
        IMAGETYPE_GIF  => imagecreatefromgif($sourcePath),
        IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
        default        => null,
    };

    if (!$source) {
        return false;
    }

    $dest = imagecreatetruecolor($newWidth, $newHeight);

    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
    }

    imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $result = match ($type) {
        IMAGETYPE_JPEG => imagejpeg($dest, $destPath, 85),
        IMAGETYPE_PNG  => imagepng($dest, $destPath, 8),
        IMAGETYPE_GIF  => imagegif($dest, $destPath),
        IMAGETYPE_WEBP => imagewebp($dest, $destPath, 85),
        default        => false,
    };

    imagedestroy($source);
    imagedestroy($dest);

    return $result;
}
