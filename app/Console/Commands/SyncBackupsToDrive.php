<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SyncBackupsToDrive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:sync-drive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Internet milne par local backups ko Google Drive par upload karna';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Check karein ke Internet chal raha hai ya nahi
        if (!$this->hasInternet()) {
            $this->info('Internet connection unavailable. Skipping Drive upload.');
            return;
        }

        $localDisk = Storage::disk('local');
        $googleDisk = Storage::disk('google');

        // Backup folder name (App Name ya Spatie default)
        $folderName = config('app.name', 'Laravel');
        $localFiles = $localDisk->files($folderName);

        if (empty($localFiles)) {
            $this->info('No local backups found to upload.');
            return;
        }

        // 2. Local Backup Files ko check karke Google Drive par bhejna
        foreach ($localFiles as $filePath) {
            $fileName = basename($filePath);

            // Agar yeh file pehle se Google Drive par nahi hai
            if (!$googleDisk->exists($fileName)) {
                $this->info("Uploading {$fileName} to Google Drive...");

                // Stream use kar rahe hain taake memory leak na ho (Large Zip files ke liye best practice)
                $stream = $localDisk->readStream($filePath);
                $googleDisk->writeStream($fileName, $stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }

                $this->info("Successfully uploaded {$fileName} to Google Drive!");
            } else {
                $this->info("{$fileName} already exists on Google Drive.");
            }
        }
    }

    /**
     * Light-weight Internet Check
     */
    private function hasInternet(): bool
    {
        try {
            // 2 second timeout ke sath Google check
            $response = Http::timeout(2)->get('https://www.google.com');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}