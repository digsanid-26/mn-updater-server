<?php

namespace App\Console\Commands;

use App\Models\PluginVersion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixPluginVersionFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugins:fix-version-files {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix plugin version files by moving temp files to permanent locations and updating records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $disk = Storage::disk('local');

        $this->info('Scanning for plugin versions with missing or temp files...');

        $versions = PluginVersion::with('plugin')->get();
        $fixed = 0;
        $errors = 0;

        foreach ($versions as $version) {
            $plugin = $version->plugin;

            if (!$plugin) {
                $this->warn("Version ID {$version->id} has no associated plugin, skipping.");
                continue;
            }

            // Check if file_path is empty or file doesn't exist
            $needsFix = false;
            $reason = '';

            if (empty($version->file_path)) {
                $needsFix = true;
                $reason = 'No file_path set';
            } elseif (!$disk->exists($version->file_path)) {
                $needsFix = true;
                $reason = "File not found at {$version->file_path}";
            }

            if (!$needsFix) {
                continue;
            }

            $this->line("Plugin: {$plugin->name}, Version: {$version->version} - {$reason}");

            // Look for temp files
            $tempDir = 'plugins/uploads/temp';
            if ($disk->exists($tempDir)) {
                $tempFiles = $disk->files($tempDir);
                
                foreach ($tempFiles as $tempFile) {
                    $fileName = basename($tempFile);
                    
                    // Check if this temp file might belong to this version
                    if (str_contains($fileName, '.zip')) {
                        $this->info("  Found temp file: {$fileName}");

                        if (!$dryRun) {
                            // Move to permanent location
                            $permanentFileName = $plugin->slug . '-' . $version->version . '.zip';
                            $permanentDir = 'plugins/' . $plugin->slug;
                            $permanentPath = $permanentDir . '/' . $permanentFileName;

                            // Ensure directory exists
                            if (!$disk->exists($permanentDir)) {
                                $disk->makeDirectory($permanentDir);
                            }

                            // Move file
                            $disk->move($tempFile, $permanentPath);

                            // Calculate file info
                            $fullPath = $disk->path($permanentPath);
                            $checksum = hash_file('sha256', $fullPath);
                            $fileSize = $disk->size($permanentPath);

                            // Update version record
                            $version->update([
                                'file_path' => $permanentPath,
                                'file_name' => $permanentFileName,
                                'file_size' => $fileSize,
                                'checksum' => $checksum,
                            ]);

                            $this->info("  ✓ Moved to {$permanentPath}");
                            $fixed++;
                        } else {
                            $this->info("  [DRY-RUN] Would move to plugins/{$plugin->slug}/{$plugin->slug}-{$version->version}.zip");
                        }

                        break; // Only process first matching file
                    }
                }
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Fixed: {$fixed}");
        $this->info("  Errors: {$errors}");

        if ($dryRun) {
            $this->warn("This was a dry run. Run without --dry-run to apply changes.");
        }

        return Command::SUCCESS;
    }
}
