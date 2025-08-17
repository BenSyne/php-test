<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Carbon\Carbon;

class PrescriptionUploadService
{
    protected $imageManager;
    
    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Handle prescription file uploads with security and compliance.
     */
    public function handlePrescriptionFiles(array $files, int $patientId): array
    {
        $uploadedFiles = [];
        $uploadPath = "prescriptions/patient_{$patientId}/" . date('Y/m');

        foreach ($files as $file) {
            try {
                $uploadResult = $this->processFile($file, $uploadPath, $patientId);
                if ($uploadResult) {
                    $uploadedFiles[] = $uploadResult;
                }
            } catch (\Exception $e) {
                Log::error('File upload failed', [
                    'patient_id' => $patientId,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);
                
                // Continue with other files even if one fails
                continue;
            }
        }

        return $uploadedFiles;
    }

    /**
     * Process individual file upload.
     */
    private function processFile(UploadedFile $file, string $uploadPath, int $patientId): ?array
    {
        // Validate file
        $validation = $this->validateFile($file);
        if (!$validation['valid']) {
            throw new \Exception('File validation failed: ' . implode(', ', $validation['errors']));
        }

        // Generate secure filename
        $filename = $this->generateSecureFilename($file);
        $fullPath = $uploadPath . '/' . $filename;

        // Process based on file type
        $processedFile = null;
        $mimeType = $file->getMimeType();

        if (str_starts_with($mimeType, 'image/')) {
            $processedFile = $this->processImageFile($file, $fullPath);
        } elseif ($mimeType === 'application/pdf') {
            $processedFile = $this->processPdfFile($file, $fullPath);
        } else {
            throw new \Exception('Unsupported file type: ' . $mimeType);
        }

        if (!$processedFile) {
            throw new \Exception('Failed to process file');
        }

        // Generate metadata
        $metadata = $this->generateFileMetadata($file, $processedFile, $patientId);

        return $metadata;
    }

    /**
     * Validate uploaded file for security and compliance.
     */
    private function validateFile(UploadedFile $file): array
    {
        $errors = [];

        // Check if file was uploaded successfully
        if (!$file->isValid()) {
            $errors[] = 'File upload failed';
            return ['valid' => false, 'errors' => $errors];
        }

        // Check file size (max 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file->getSize() > $maxSize) {
            $errors[] = 'File size exceeds 10MB limit';
        }

        // Check allowed MIME types
        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/jpg',
            'application/pdf'
        ];

        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = 'Invalid file type. Only JPEG, PNG, and PDF files are allowed';
        }

        // Check file extension matches MIME type
        $extension = strtolower($file->getClientOriginalExtension());
        $expectedExtensions = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'application/pdf' => ['pdf']
        ];

        if (isset($expectedExtensions[$mimeType])) {
            if (!in_array($extension, $expectedExtensions[$mimeType])) {
                $errors[] = 'File extension does not match file type';
            }
        }

        // Scan for malicious content
        $securityCheck = $this->performSecurityScan($file);
        if (!$securityCheck['safe']) {
            $errors = array_merge($errors, $securityCheck['issues']);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Generate secure filename to prevent directory traversal and conflicts.
     */
    private function generateSecureFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $timestamp = now()->format('YmdHis');
        $randomString = bin2hex(random_bytes(8));
        
        return "prescription_{$timestamp}_{$randomString}.{$extension}";
    }

    /**
     * Process image files with optimization and watermarking.
     */
    private function processImageFile(UploadedFile $file, string $path): ?string
    {
        try {
            // Load image
            $image = $this->imageManager->read($file->getPathname());

            // Auto-orient based on EXIF data
            $image->orient();

            // Remove EXIF data for privacy
            $image->modify();

            // Resize if too large (max 2048px on longest side)
            $maxDimension = 2048;
            if ($image->width() > $maxDimension || $image->height() > $maxDimension) {
                $image->scaleDown($maxDimension, $maxDimension);
            }

            // Add watermark for security
            $this->addWatermark($image);

            // Save with optimization
            $imageData = $image->toJpeg(85); // 85% quality
            
            if (Storage::put($path, $imageData)) {
                return $path;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Image processing failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Process PDF files with security checks.
     */
    private function processPdfFile(UploadedFile $file, string $path): ?string
    {
        try {
            // Basic PDF security checks
            $content = file_get_contents($file->getPathname());
            
            // Check for suspicious PDF content
            if ($this->containsSuspiciousPdfContent($content)) {
                throw new \Exception('PDF contains potentially malicious content');
            }

            // Store the file
            if (Storage::putFileAs(dirname($path), $file, basename($path))) {
                return $path;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('PDF processing failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Add security watermark to images.
     */
    private function addWatermark($image): void
    {
        try {
            $watermarkText = "DirectMeds Rx - " . date('Y-m-d H:i:s');
            
            // Add watermark in bottom right corner
            $image->drawText($watermarkText, 
                x: $image->width() - 200, 
                y: $image->height() - 20,
                font_size: 12,
                font_color: 'rgba(255, 255, 255, 0.7)',
                align: 'right'
            );
            
        } catch (\Exception $e) {
            // Watermark failure shouldn't stop the upload
            Log::warning('Watermark failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Perform basic security scanning on uploaded files.
     */
    private function performSecurityScan(UploadedFile $file): array
    {
        $issues = [];

        try {
            $content = file_get_contents($file->getPathname());
            $mimeType = $file->getMimeType();

            // Check for embedded scripts in images
            if (str_starts_with($mimeType, 'image/')) {
                if ($this->containsEmbeddedScripts($content)) {
                    $issues[] = 'Image contains embedded scripts';
                }
            }

            // Check for suspicious file headers
            if ($this->hasSuspiciousHeader($content, $mimeType)) {
                $issues[] = 'File has suspicious header information';
            }

            // Check file size consistency
            if ($this->hasInconsistentSize($file)) {
                $issues[] = 'File size inconsistency detected';
            }

        } catch (\Exception $e) {
            Log::error('Security scan failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
            
            $issues[] = 'Security scan could not be completed';
        }

        return [
            'safe' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Check for embedded scripts in image files.
     */
    private function containsEmbeddedScripts(string $content): bool
    {
        $suspiciousPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/javascript:/i',
            '/vbscript:/i',
            '/on\w+\s*=/i',
            '/<?php/i',
            '/<%/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for suspicious PDF content.
     */
    private function containsSuspiciousPdfContent(string $content): bool
    {
        $suspiciousPatterns = [
            '/\/JavaScript/i',
            '/\/JS/i',
            '/\/URI/i',
            '/\/GoToR/i',
            '/\/Launch/i',
            '/\/EmbeddedFile/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if file has suspicious header information.
     */
    private function hasSuspiciousHeader(string $content, string $expectedMimeType): bool
    {
        // Get first few bytes to check magic numbers
        $header = substr($content, 0, 20);

        $magicNumbers = [
            'image/jpeg' => ["\xFF\xD8\xFF"],
            'image/png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
            'application/pdf' => ["%PDF-"],
        ];

        if (isset($magicNumbers[$expectedMimeType])) {
            foreach ($magicNumbers[$expectedMimeType] as $magic) {
                if (str_starts_with($header, $magic)) {
                    return false; // Header is correct
                }
            }
            return true; // Header doesn't match expected type
        }

        return false; // Unknown type, assume safe
    }

    /**
     * Check for file size inconsistencies.
     */
    private function hasInconsistentSize(UploadedFile $file): bool
    {
        $reportedSize = $file->getSize();
        $actualSize = filesize($file->getPathname());

        // Allow for small differences due to transfer encoding
        $tolerance = 1024; // 1KB tolerance
        
        return abs($reportedSize - $actualSize) > $tolerance;
    }

    /**
     * Generate comprehensive file metadata.
     */
    private function generateFileMetadata(UploadedFile $file, string $storedPath, int $patientId): array
    {
        return [
            'filename' => basename($storedPath),
            'original_name' => $file->getClientOriginalName(),
            'path' => $storedPath,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'uploaded_at' => now()->toISOString(),
            'uploaded_by' => auth()->id(),
            'patient_id' => $patientId,
            'checksum' => hash_file('sha256', Storage::path($storedPath)),
            'upload_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'file_metadata' => $this->extractFileMetadata($file),
        ];
    }

    /**
     * Extract technical metadata from files.
     */
    private function extractFileMetadata(UploadedFile $file): array
    {
        $metadata = [];
        $mimeType = $file->getMimeType();

        try {
            if (str_starts_with($mimeType, 'image/')) {
                $imageInfo = getimagesize($file->getPathname());
                if ($imageInfo) {
                    $metadata['width'] = $imageInfo[0];
                    $metadata['height'] = $imageInfo[1];
                    $metadata['bits'] = $imageInfo['bits'] ?? null;
                    $metadata['channels'] = $imageInfo['channels'] ?? null;
                }

                // Get EXIF data before processing (for audit purposes)
                if (function_exists('exif_read_data') && in_array($mimeType, ['image/jpeg', 'image/tiff'])) {
                    $exif = @exif_read_data($file->getPathname());
                    if ($exif) {
                        $metadata['exif'] = [
                            'make' => $exif['Make'] ?? null,
                            'model' => $exif['Model'] ?? null,
                            'datetime' => $exif['DateTime'] ?? null,
                            'software' => $exif['Software'] ?? null,
                        ];
                    }
                }
            }

        } catch (\Exception $e) {
            Log::warning('Metadata extraction failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
        }

        return $metadata;
    }

    /**
     * Delete uploaded file (for cleanup or security).
     */
    public function deleteFile(string $path): bool
    {
        try {
            if (Storage::exists($path)) {
                return Storage::delete($path);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get file URL for viewing (with access control).
     */
    public function getFileUrl(string $path, int $patientId): ?string
    {
        $user = auth()->user();
        
        // Check authorization
        if ($user->isPatient() && $user->id !== $patientId) {
            return null;
        }

        if (!$user->isPharmacist() && !$user->isPatient()) {
            return null;
        }

        if (Storage::exists($path)) {
            return Storage::temporaryUrl($path, now()->addHour());
        }

        return null;
    }

    /**
     * Create thumbnail for image files.
     */
    public function createThumbnail(string $imagePath, int $size = 150): ?string
    {
        try {
            if (!Storage::exists($imagePath)) {
                return null;
            }

            $thumbnailPath = str_replace('.', '_thumb.', $imagePath);
            
            $image = $this->imageManager->read(Storage::path($imagePath));
            $image->scaleDown($size, $size);
            
            $thumbnailData = $image->toJpeg(80);
            
            if (Storage::put($thumbnailPath, $thumbnailData)) {
                return $thumbnailPath;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Thumbnail creation failed', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Batch process multiple files with progress tracking.
     */
    public function batchProcessFiles(array $files, int $patientId, callable $progressCallback = null): array
    {
        $results = [];
        $total = count($files);
        
        foreach ($files as $index => $file) {
            try {
                $result = $this->handlePrescriptionFiles([$file], $patientId);
                $results = array_merge($results, $result);
                
                if ($progressCallback) {
                    $progressCallback($index + 1, $total, $file->getClientOriginalName(), 'success');
                }
                
            } catch (\Exception $e) {
                if ($progressCallback) {
                    $progressCallback($index + 1, $total, $file->getClientOriginalName(), 'error', $e->getMessage());
                }
            }
        }
        
        return $results;
    }
}