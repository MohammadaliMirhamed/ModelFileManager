<?php

namespace Roui\ModelFileManager\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Intervention\Image\Facades\Image;

trait HasFiles
{
    /**
     * The default disk to store files on.
     *
     * @var string
     */
    protected string $storageDisk;

    /**
     * Initialize the trait by setting the default storage disk.
     */
    public function __construct()
    {
        // Load the default disk from configuration or use 'public' as fallback
        $this->storageDisk = config('modelfilemanager.storage_disk', 'public');
    }

    /**
     * Upload a file to a specific collection and disk.
     *
     * @param  \Illuminate\Http\UploadedFile $file The uploaded file.
     * @param  string|null $collection The collection name (optional).
     * @param  string|null $disk The storage disk name (optional).
     * @return void
     */
    public function uploadFile($file, ?string $collection = null, ?string $disk = null): void
    {
        // Set disk and collection with defaults if not provided
        $disk = $disk ?: $this->storageDisk;
        $collection = $collection ?: config('modelfilemanager.default_collection', 'default');

        // Store the file in the appropriate folder and get its file path
        $filePath = $file->store($collection, $disk);

        // Add the file path to the collection in the database
        $this->addFile($filePath, $collection);
    }

    /**
     * Delete a file from a specific collection and disk.
     *
     * @param  string $filePath The file path to delete.
     * @param  string|null $collection The collection name (optional).
     * @param  string|null $disk The storage disk name (optional).
     * @return void
     */
    public function deleteFile(string $filePath, ?string $collection = null, ?string $disk = null): void
    {
        $disk = $disk ?: $this->storageDisk;
        $collection = $collection ?: config('modelfilemanager.default_collection', 'default');

        // Delete file from storage and remove its reference from the collection
        Storage::disk($disk)->delete($filePath);
        $this->removeFile($filePath, $collection);
    }

    /**
     * Get all files from the 'files' JSON column as a Collection.
     *
     * @return Collection A collection of files.
     */
    public function getFiles(): Collection
    {
        return collect($this->files ?? []); // Handle null case by returning an empty collection
    }

    /**
     * Get files from a specific collection with optional resizing.
     *
     * @param  string|null $collection The collection name (optional).
     * @param  int|null $width The width to resize to (optional).
     * @param  int|null $height The height to resize to (optional).
     * @param  string|null $disk The storage disk name (optional).
     * @return Collection A collection of file paths or resized images.
     */
    public function getFilesFromCollection(?string $collection = null, ?int $width = null, ?int $height = null, ?string $disk = null): Collection
    {
        $disk = $disk ?: $this->storageDisk;
        $collection = $collection ?: config('modelfilemanager.default_collection', 'default');
        $files = $this->getFiles();
        $collectionFiles = collect($files[$collection] ?? []);

        // If width and height are provided, resize the images
        if ($width && $height) {
            return $collectionFiles->map(function ($file) use ($width, $height, $disk) {
                return $this->getResizedImage($file['path'], $width, $height, $disk);
            });
        }

        return $collectionFiles; // Return original files if no resizing is required
    }

    /**
     * Add a file path to a specific collection in the 'files' JSON column.
     *
     * @param  string $filePath The file path to add.
     * @param  string|null $collection The collection name (optional).
     * @return void
     */
    protected function addFile(string $filePath, ?string $collection = null): void
    {
        $collection = $collection ?: config('modelfilemanager.default_collection', 'default');

        // Decode the files JSON into an array and ensure the collection exists
        $files = $this->files ?? [];
        if (!isset($files[$collection])) {
            $files[$collection] = [];
        }

        // Add the new file path to the collection
        $files[$collection][] = ['path' => $filePath];

        // Encode back to JSON and save to the database
        $this->files = json_encode($files);
        $this->save();
    }

    /**
     * Remove a file path from a specific collection in the 'files' JSON column.
     *
     * @param  string $filePath The file path to remove.
     * @param  string|null $collection The collection name (optional).
     * @return void
     */
    protected function removeFile(string $filePath, ?string $collection = null): void
    {
        $collection = $collection ?: config('modelfilemanager.default_collection', 'default');

        // Decode the files JSON and filter out the file from the collection
        $files = $this->files ?? [];
        if (isset($files[$collection])) {
            $files[$collection] = array_filter($files[$collection], function ($file) use ($filePath) {
                return $file['path'] !== $filePath;
            });
        }

        // Encode the updated array back to JSON and save it
        $this->files = json_encode($files);
        $this->save();
    }

    /**
     * Replace an old file with a new file in the specified collection.
     *
     * @param  \Illuminate\Http\UploadedFile $newFile The new uploaded file.
     * @param  string|null $collection The collection name (optional).
     * @param  string|null $disk The storage disk name (optional).
     * @return void
     */
    public function replaceFileInCollection($newFile, ?string $collection = null, ?string $disk = null): void
    {
        $disk = $disk ?: $this->storageDisk;
        $collection = $collection ?: config('modelfilemanager.default_collection', 'default');

        // Get the first file in the collection and delete it
        $oldFile = $this->getFirstFileFromCollection($collection);
        if ($oldFile) {
            $this->deleteFile($oldFile['path'], $collection, $disk);
        }

        // Upload the new file to the collection
        $this->uploadFile($newFile, $collection, $disk);
    }

    /**
     * Get the first file from a specific collection with optional resizing.
     *
     * @param  string|null $collection The collection name (optional).
     * @param  int|null $width The width to resize to (optional).
     * @param  int|null $height The height to resize to (optional).
     * @param  string|null $disk The storage disk name (optional).
     * @return string|null The file path or resized image path.
     */
    public function getFirstFileFromCollection(?string $collection = null, ?int $width = null, ?int $height = null, ?string $disk = null): ?string
    {
        $files = $this->getFilesFromCollection($collection, $width, $height, $disk);
        return $files->first(); // Return the first file or null if no files exist
    }

    /**
     * Get a resized version of an image.
     *
     * @param  string $filePath The file path.
     * @param  int $width The width to resize to.
     * @param  int $height The height to resize to.
     * @param  string|null $disk The storage disk name (optional).
     * @return string The resized image URL.
     */
    protected function getResizedImage(string $filePath, int $width, int $height, ?string $disk = null): string
    {
        $disk = $disk ?: $this->storageDisk;

        // Get the resized image path based on width and height
        $resizedImagePath = str_replace(
            ['{width}', '{height}'],
            [$width, $height],
            config('modelfilemanager.resized_image_path', 'resized/{width}x{height}/') . basename($filePath)
        );

        // If the resized image doesn't exist, create and store it
        if (!Storage::disk($disk)->exists($resizedImagePath)) {
            $image = Image::make(Storage::disk($disk)->get($filePath));
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });

            // Save the resized image to disk
            Storage::disk($disk)->put($resizedImagePath, (string) $image->encode());
        }

        // Return the URL of the resized image
        return Storage::disk($disk)->url($resizedImagePath);
    }

    /**
     * Automatically cast the 'files' attribute to an array.
     *
     * @param  string|null $value The 'files' column value from the database.
     * @return array The decoded JSON or an empty array.
     */
    public function getFilesAttribute(?string $value): array
    {
        return json_decode($value, true) ?? [];
    }
}

