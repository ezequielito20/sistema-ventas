<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageUrlService;

class DebugImageUrl extends Command
{
    protected $signature = 'debug:image-url {path}';
    protected $description = 'Debug image URL generation step by step';

    public function handle()
    {
        $path = $this->argument('path');
        
        $this->info("🔍 Debugging Image URL Generation");
        $this->line("Path: {$path}");
        $this->line('');

        // Step 1: Environment info
        $this->info("📋 Environment Info:");
        $this->line("  Environment: " . app()->environment());
        $this->line("  Default disk: " . config('filesystems.default'));
        $this->line('');

        // Step 2: Normalize path
        $this->info("📋 Path Normalization:");
        $normalizedPath = str_starts_with($path, 'storage/') ? substr($path, 8) : $path;
        $this->line("  Original: {$path}");
        $this->line("  Normalized: {$normalizedPath}");
        $this->line('');

        // Step 3: Test disk connection
        $this->info("📋 Disk Connection:");
        try {
            $defaultDisk = config('filesystems.default');
            $disk = Storage::disk($defaultDisk);
            $this->line("  ✅ Disk '{$defaultDisk}' connected");
            
            // Check if file exists
            if ($disk->exists($normalizedPath)) {
                $this->line("  ✅ File exists: {$normalizedPath}");
            } else {
                $this->line("  ❌ File does not exist: {$normalizedPath}");
                
                // List files in directory
                $directory = dirname($normalizedPath);
                $files = $disk->files($directory);
                $this->line("  Files in '{$directory}':");
                foreach (array_slice($files, 0, 5) as $file) {
                    $this->line("    - {$file}");
                }
            }
        } catch (\Exception $e) {
            $this->line("  ❌ Error: " . $e->getMessage());
        }
        $this->line('');

        // Step 4: Test temporaryUrl
        $this->info("📋 Testing temporaryUrl:");
        try {
            $defaultDisk = config('filesystems.default');
            $disk = Storage::disk($defaultDisk);
            
            if ($disk->exists($normalizedPath)) {
                $url = $disk->temporaryUrl($normalizedPath, now()->addHours(24));
                $this->line("  ✅ Generated URL: {$url}");
            } else {
                $this->line("  ❌ Cannot generate URL: file does not exist");
            }
        } catch (\Exception $e) {
            $this->line("  ❌ Error generating temporaryUrl: " . $e->getMessage());
        }
        $this->line('');

        // Step 5: Test ImageUrlService
        $this->info("📋 Testing ImageUrlService:");
        try {
            $url = ImageUrlService::getImageUrl($path);
            if ($url) {
                $this->line("  ✅ Generated URL: {$url}");
            } else {
                $this->line("  ❌ Empty URL returned");
            }
        } catch (\Exception $e) {
            $this->line("  ❌ Error: " . $e->getMessage());
        }

        return 0;
    }
} 