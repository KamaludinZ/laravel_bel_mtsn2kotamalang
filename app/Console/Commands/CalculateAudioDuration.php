<?php

namespace App\Console\Commands;

use App\Models\AudioLibrary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CalculateAudioDuration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audio:calculate-duration {--force : Force recalculate all audio durations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and store duration for all audio files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        if ($force) {
            $audios = AudioLibrary::all();
            $this->info('Recalculating duration for all audio files...');
        } else {
            $audios = AudioLibrary::whereNull('duration')->get();
            $this->info('Calculating duration for audio files without duration...');
        }

        if ($audios->count() === 0) {
            $this->info('No audio files to process.');
            return 0;
        }

        $this->info("Found {$audios->count()} audio file(s) to process.");
        $bar = $this->output->createProgressBar($audios->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($audios as $audio) {
            try {
                $filePath = storage_path('app/public/' . $audio->file_path);

                if (!file_exists($filePath)) {
                    $this->newLine();
                    $this->error("File not found: {$audio->file_path}");
                    $failed++;
                    $bar->advance();
                    continue;
                }

                $duration = $this->getAudioDuration($filePath);

                if ($duration !== null) {
                    $audio->update(['duration' => $duration]);
                    $success++;
                } else {
                    $this->newLine();
                    $this->warn("Could not calculate duration for: {$audio->title}");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error processing {$audio->title}: {$e->getMessage()}");
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done! Success: {$success}, Failed: {$failed}");

        return 0;
    }

    /**
     * Get audio duration using getID3 library or native PHP
     */
    private function getAudioDuration($filePath)
    {
        // Try using getID3 if available
        if (class_exists('getID3')) {
            try {
                $getID3 = new \getID3;
                $fileInfo = $getID3->analyze($filePath);

                if (isset($fileInfo['playtime_seconds'])) {
                    return (int) round($fileInfo['playtime_seconds']);
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("getID3 error: " . $e->getMessage());
                // Fall through to alternative method
            }
        }

        // Alternative: Use ffmpeg/ffprobe if available
        if ($this->commandExists('ffprobe')) {
            try {
                $command = sprintf(
                    'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s',
                    escapeshellarg($filePath)
                );

                $output = shell_exec($command);
                if ($output !== null && is_numeric(trim($output))) {
                    return (int) round(floatval(trim($output)));
                }
            } catch (\Exception $e) {
                // Fall through
            }
        }

        // Last resort: estimate based on file size (very rough approximation)
        // MP3 typically: 128kbps = 16KB/s, so duration ≈ filesize / 16000
        $fileSize = filesize($filePath);
        $estimatedDuration = (int) round($fileSize / 16000);

        $this->newLine();
        $this->warn("Using estimated duration for: " . basename($filePath));

        return $estimatedDuration > 0 ? $estimatedDuration : null;
    }

    /**
     * Check if a command exists
     */
    private function commandExists($command)
    {
        $os = strtoupper(substr(PHP_OS, 0, 3));

        if ($os === 'WIN') {
            $whereIsCommand = "where $command 2>nul";
        } else {
            $whereIsCommand = "command -v $command 2>/dev/null";
        }

        $result = shell_exec($whereIsCommand);
        return !empty($result);
    }
}
