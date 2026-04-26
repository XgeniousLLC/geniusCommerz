<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaFolder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class MediaService
{
    private string $chunkDir;

    public function __construct()
    {
        $this->chunkDir = storage_path('app/chunks');
    }

    public function saveChunk(UploadedFile $chunk, string $uuid, int $index): void
    {
        $dir = $this->chunkDir . '/' . $uuid;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents("{$dir}/chunk_{$index}", file_get_contents($chunk->getRealPath()));
    }

    public function chunksComplete(string $uuid, int $total): bool
    {
        for ($i = 0; $i < $total; $i++) {
            if (!file_exists("{$this->chunkDir}/{$uuid}/chunk_{$i}")) {
                return false;
            }
        }
        return true;
    }

    public function assembleAndStore(
        string $uuid,
        int $total,
        string $originalName,
        string $mimeType,
        string $disk,
        ?int $folderId
    ): Media {
        $tmpFile = tempnam(sys_get_temp_dir(), 'media_');
        $fp = fopen($tmpFile, 'wb');
        for ($i = 0; $i < $total; $i++) {
            fwrite($fp, file_get_contents("{$this->chunkDir}/{$uuid}/chunk_{$i}"));
        }
        fclose($fp);

        $type = $this->detectType($mimeType);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) ?: 'bin';
        $datePath = now()->format('Y/m');
        $storedName = $uuid . '.' . $ext;
        $path = "media/{$datePath}/{$storedName}";

        Storage::disk($disk)->put($path, file_get_contents($tmpFile));
        $size = Storage::disk($disk)->size($path);

        $width = $height = null;
        $conversions = [];

        $isSvg = $mimeType === 'image/svg+xml' || strtolower($ext) === 'svg';

        if ($type === 'image' && !$isSvg) {
            [$width, $height, $conversions] = $this->processImage($tmpFile, $uuid, "media/{$datePath}", $disk, $ext);
        }

        @unlink($tmpFile);
        $this->cleanupChunks($uuid);

        return Media::create([
            'uuid'              => $uuid,
            'folder_id'         => $folderId,
            'type'              => $type,
            'disk'              => $disk,
            'path'              => $path,
            'filename'          => $storedName,
            'original_filename' => $originalName,
            'mime_type'         => $mimeType,
            'size'              => $size,
            'width'             => $width,
            'height'            => $height,
            'conversions'       => $conversions ?: null,
            'collection_name'   => 'default',
            'created_by'        => auth('admin')->id(),
        ]);
    }

    public function deleteMedia(Media $media): void
    {
        if ($media->conversions) {
            foreach ($media->conversions as $path) {
                Storage::disk($media->disk)->delete($path);
            }
        }
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
    }

    private function processImage(string $tmpFile, string $uuid, string $dirPath, string $disk, string $ext): array
    {
        $manager = new ImageManager(new GdDriver());

        $img    = $manager->read($tmpFile);
        $width  = $img->width();
        $height = $img->height();
        $conversions = [];

        $thumbTmp  = tempnam(sys_get_temp_dir(), 'thumb_');
        $manager->read($tmpFile)->cover(300, 300)->save($thumbTmp);
        $thumbPath = "{$dirPath}/thumbs/{$uuid}_thumb.{$ext}";
        Storage::disk($disk)->put($thumbPath, file_get_contents($thumbTmp));
        @unlink($thumbTmp);
        $conversions['thumb'] = $thumbPath;

        if ($width > 800) {
            $medTmp  = tempnam(sys_get_temp_dir(), 'med_');
            $manager->read($tmpFile)->scaleDown(800)->save($medTmp);
            $medPath = "{$dirPath}/medium/{$uuid}_med.{$ext}";
            Storage::disk($disk)->put($medPath, file_get_contents($medTmp));
            @unlink($medTmp);
            $conversions['medium'] = $medPath;
        }

        return [$width, $height, $conversions];
    }

    private function detectType(string $mimeType): string
    {
        if ($mimeType === 'image/svg+xml') return 'image';
        if (str_starts_with($mimeType, 'image/')) return 'image';
        if (str_starts_with($mimeType, 'video/')) return 'video';
        return 'document';
    }

    private function cleanupChunks(string $uuid): void
    {
        $dir = $this->chunkDir . '/' . $uuid;
        if (is_dir($dir)) {
            array_map('unlink', glob($dir . '/*') ?: []);
            @rmdir($dir);
        }
    }
}
