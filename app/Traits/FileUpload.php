<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

trait FileUpload
{
    // Base directories (relative to the "public" disk, i.e. storage/app/public)
    protected string $logo_dir = 'uploads/logo';

    protected string $proPic_dir = 'uploads/profilePic';

    protected string $banner_dir = 'uploads/banners';

    protected string $galary_dir = 'uploads/galleries';

    protected string $images_dir = 'uploads/images';

    protected string $client_images_dir = 'uploads/client/images';

    protected string $file_dir = 'uploads/files';

    /**
     * Ensure a directory exists on the public disk (idempotent).
     */
    protected function ensureDir(string $dir): void
    {
        $dir = trim($dir, '/');
        if (! Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
    }

    /**
     * Remove an existing file if present. Accepts either disk-relative path
     * like 'uploads/files/abc.pdf' or a full URL produced by asset('storage/...').
     */
    protected function removeFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        // Normalize URL -> disk path if needed
        $diskPath = $this->toDiskPath($path);
        if ($diskPath && Storage::disk('public')->exists($diskPath)) {
            Storage::disk('public')->delete($diskPath);
        }
    }

    /**
     * Convert a public URL (asset('storage/...')) or absolute OS path back to a disk-relative path.
     */
    protected function toDiskPath(string $maybeUrlOrPath): ?string
    {
        $maybeUrlOrPath = str_replace('\\', '/', $maybeUrlOrPath);

        // If already looks like a disk path, return it
        if (
            ! str_starts_with($maybeUrlOrPath, 'http://') &&
            ! str_starts_with($maybeUrlOrPath, 'https://') &&
            ! str_starts_with($maybeUrlOrPath, '/')
        ) {
            return ltrim($maybeUrlOrPath, '/');
        }

        // If it is an asset URL like https://domain/storage/...
        $storagePrefix = '/storage/';
        if (($pos = strpos($maybeUrlOrPath, $storagePrefix)) !== false) {
            return ltrim(substr($maybeUrlOrPath, $pos + strlen($storagePrefix)), '/');
        }

        // If someone passed a full real path pointing into storage/app/public
        $publicRoot = str_replace('\\', '/', storage_path('app/public/'));
        if (str_starts_with($maybeUrlOrPath, $publicRoot)) {
            return ltrim(substr($maybeUrlOrPath, strlen($publicRoot)), '/');
        }

        return null;
    }

    /**
     * Build a public URL from a disk-relative path.
     */
    protected function toPublicUrl(string $diskPath): string
    {
        return asset('storage/'.ltrim($diskPath, '/'));
    }

    /**
     * Upload & (optionally) resize a single image with Intervention.
     * - $dir is a folder relative to the "public" disk (e.g. 'uploads/images')
     * - $width/$height: pass one or both. If one is null, aspect ratio is preserved.
     * - $oldFile: optional previous file path or URL to delete.
     *
     * @return array{path:string,url:string,filename:string}
     */
    protected function uploadImage(Request $request, string $field, string $dir = 'uploads', ?int $width = null, ?int $height = null, ?string $oldFile = null): ?array
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $this->ensureDir($dir);

        $file = $request->file($field);
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $basename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';
        $filename = $basename.'_'.time().'_'.Str::random(6).'.'.$ext;
        $diskPath = trim($dir, '/').'/'.$filename;

        // Read to Intervention
        $img = Image::make($file->getRealPath());

        if ($width || $height) {
            $img->resize($width, $height, function ($constraint) use ($width, $height) {
                // If only one dimension is provided, keep aspect ratio
                if (empty($width) || empty($height)) {
                    $constraint->aspectRatio();
                }
                $constraint->upsize();
            });
        }

        // Stream to Storage (no manual mkdir)
        Storage::disk('public')->put($diskPath, (string) $img->encode($ext, 85));

        // Remove old if provided
        $this->removeFile($oldFile);

        return [
            'path' => $diskPath,
            'url' => $this->toPublicUrl($diskPath),
            'filename' => $filename,
        ];
    }

    /**
     * Upload a single file (any kind).
     *
     * @return array{path:string,url:string,filename:string,mime:string,size:int}
     */
    protected function uploadFile(Request $request, string $field, string $dir = 'uploads/files', ?string $oldFile = null): ?array
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $this->ensureDir($dir);

        $file = $request->file($field);
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $basename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
        $filename = $basename.'_'.time().'_'.Str::random(6).'.'.$ext;
        $diskPath = trim($dir, '/').'/'.$filename;

        // Stream to Storage
        Storage::disk('public')->putFileAs(trim($dir, '/'), $file, $filename);

        // Remove old if provided
        $this->removeFile($oldFile);

        return [
            'path' => $diskPath,
            'url' => $this->toPublicUrl($diskPath),
            'filename' => $filename,
            'mime' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize() ?? 0,
        ];
    }

    /**
     * Upload multiple generic files.
     *
     * @return array<int, array{path:string,url:string,filename:string,mime:string,size:int}>
     */
    protected function uploadMultipleFile(Request $request, string $field, string $dir = 'uploads/files'): array
    {
        if (! $request->hasFile($field)) {
            return [];
        }

        $this->ensureDir($dir);

        $out = [];
        foreach ((array) $request->file($field) as $idx => $file) {
            $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $basename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
            $filename = $basename.'_'.time().'_'.Str::random(6).'.'.$ext;
            $diskPath = trim($dir, '/').'/'.$filename;

            Storage::disk('public')->putFileAs(trim($dir, '/'), $file, $filename);

            $out[] = [
                'path' => $diskPath,
                'url' => $this->toPublicUrl($diskPath),
                'filename' => $filename,
                'mime' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize() ?? 0,
            ];
        }

        return $out;
    }

    /**
     * Upload multiple images with optional resize.
     *
     * @return array<int, array{path:string,url:string,filename:string}>
     */
    protected function uploadMultipleImage(Request $request, string $field, string $dir = 'uploads/images', ?int $width = null, ?int $height = null): array
    {
        if (! $request->hasFile($field)) {
            return [];
        }

        $this->ensureDir($dir);

        $out = [];
        foreach ((array) $request->file($field) as $file) {
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $basename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';
            $filename = $basename.'_'.time().'_'.Str::random(6).'.'.$ext;
            $diskPath = trim($dir, '/').'/'.$filename;

            $img = Image::make($file->getRealPath());

            if ($width || $height) {
                $img->resize($width, $height, function ($constraint) use ($width, $height) {
                    if (empty($width) || empty($height)) {
                        $constraint->aspectRatio();
                    }
                    $constraint->upsize();
                });
            }

            Storage::disk('public')->put($diskPath, (string) $img->encode($ext, 85));

            $out[] = [
                'path' => $diskPath,
                'url' => $this->toPublicUrl($diskPath),
                'filename' => $filename,
            ];
        }

        return $out;
    }

    /**
     * Upload multiple generic attachments while preserving original meta.
     * (Useful for email-like attachments listing.)
     *
     * @return array<int, array{file_name:string,file_type:string,file_size:int,file_path:string,url:string}>
     */
    protected function uploadMultipleAttachment(Request $request, string $field, string $dir = 'uploads/attachments'): array
    {
        if (! $request->hasFile($field)) {
            return [];
        }

        $this->ensureDir($dir);

        $out = [];
        foreach ((array) $request->file($field) as $file) {
            $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $origName = $file->getClientOriginalName() ?: ('attachment.'.$ext);
            $basename = Str::slug(pathinfo($origName, PATHINFO_FILENAME)) ?: 'attachment';
            $filename = $basename.'_'.time().'_'.Str::random(6).'.'.$ext;
            $diskPath = trim($dir, '/').'/'.$filename;

            Storage::disk('public')->putFileAs(trim($dir, '/'), $file, $filename);

            $out[] = [
                'file_name' => $origName,
                'file_type' => $file->getMimeType() ?? 'application/octet-stream',
                'file_size' => $file->getSize() ?? 0,
                'file_path' => $diskPath,
                'url' => $this->toPublicUrl($diskPath),
            ];
        }

        return $out;
    }

    /**
     * Rotate an image and save as a new file, then optionally delete the original.
     *
     * @return array{path:string,url:string,filename:string}
     */
    protected function rotateImage(string $pathOrUrl, int $deg = 90, bool $deleteOriginal = true): ?array
    {
        $diskPath = $this->toDiskPath($pathOrUrl);
        if (! $diskPath || ! Storage::disk('public')->exists($diskPath)) {
            return null;
        }

        $this->ensureDir(dirname($diskPath));

        $raw = Storage::disk('public')->get($diskPath);
        $img = Image::make($raw)->rotate($deg);
        $info = pathinfo($diskPath);
        $ext = strtolower($info['extension'] ?? 'jpg');
        $stem = $info['filename'] ?? 'image';
        $newName = $stem.'_rot'.'_'.time().'_'.Str::random(4).'.'.$ext;
        $newPath = trim($info['dirname'], '/').'/'.$newName;

        Storage::disk('public')->put($newPath, (string) $img->encode($ext, 85));

        if ($deleteOriginal) {
            Storage::disk('public')->delete($diskPath);
        }

        return [
            'path' => $newPath,
            'url' => $this->toPublicUrl($newPath),
            'filename' => $newName,
        ];
    }

    /**
     * Delete a file given its path or URL.
     */
    protected function deleteFile(string $pathOrUrl): void
    {
        $this->removeFile($pathOrUrl);
    }
}
