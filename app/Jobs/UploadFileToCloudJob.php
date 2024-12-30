<?php

namespace App\Jobs;

use App\Models\File;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadFileToCloudJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected File $file)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $model = $this->file;
        if (! $model->uploaded_on_cloud) {
            $localPath = Storage::disk('local')->path($model->storage_path);
            Log::debug(("Uploading file on S3: $localPath"));
            try {
                $success = Storage::put($model->storage_path, Storage::disk('local')->get($model->storage_path));
                if ($success) {
                    Log::debug('File uploaded on S3');
                    $model->uploaded_on_cloud = true;
                    $model->saveQuietly();
                } else {
                    Log::error('Error uploading file');
                    Log::error($success);
                }
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
