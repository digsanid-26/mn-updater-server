<?php

namespace App\Services;

use App\Models\Plugin;
use App\Models\PluginVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * Store a plugin zip file
     */
    public function storePluginZip(UploadedFile $file, Plugin $plugin, string $version): array
    {
        $fileName = $plugin->slug . '-' . $version . '.zip';
        $directory = 'plugins/' . $plugin->slug;

        $path = $file->storeAs($directory, $fileName, 'local');

        $fullPath = Storage::disk('local')->path($path);
        $checksum = hash_file('sha256', $fullPath);
        $fileSize = $file->getSize();

        return [
            'file_path' => $path,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'checksum' => $checksum,
        ];
    }

    /**
     * Get the full path to a plugin version's zip file
     */
    public function getZipPath(PluginVersion $version): ?string
    {
        if (! $version->file_path) {
            return null;
        }

        $path = Storage::disk('local')->path($version->file_path);

        if (! file_exists($path)) {
            return null;
        }

        return $path;
    }

    /**
     * Delete a plugin version's zip file
     */
    public function deleteZip(PluginVersion $version): bool
    {
        if (! $version->file_path) {
            return false;
        }

        return Storage::disk('local')->delete($version->file_path);
    }
}
