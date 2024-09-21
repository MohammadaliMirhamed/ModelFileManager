
# Model File Manager Package

## Introduction
The **ModelFileManager** package is a Laravel package designed to handle file storage at the model level. It provides features for uploading, deleting, and resizing files. Additionally, it supports multiple file collections and allows you to easily store and manage files such as images, documents, and more.

## Requirements
- **PHP:** 8.3 or higher
- **Laravel:** 8.x or higher
- **Intervention Image:** 2.x

## Installation

1. Install the package via Composer:
   ```bash
   composer require roui/model-file-manager
   ```

2. Publish the configuration file:
   ```bash
   php artisan vendor:publish --tag=config
   ```

3. Ensure the `Intervention Image` package is installed:
   ```bash
   composer require intervention/image
   ```

## Configuration

After publishing the configuration, you will find the config file at `config/modelfilemanager.php`. You can customize the following settings:

- **storage_disk**: The default storage disk to use.
- **resized_image_path**: The path for storing resized images.
- **default_collection**: The default collection name for file uploads.

### Example of `config/modelfilemanager.php`:

```php
<?php

return [
    'storage_disk' => env('MODEL_FILE_STORAGE_DISK', 'public'),
    'resized_image_path' => env('RESIZED_IMAGE_PATH', 'resized/{width}x{height}'),
    'default_collection' => env('DEFAULT_FILE_COLLECTION', 'default'),
];
```

## Usage

1. **Upload a File**:
   You can upload a file to a model by using the `uploadFile()` method. Specify the file, collection, and disk if needed.

   ```php
   $user->uploadFile($file, 'profile_pictures');
   ```

2. **Delete a File**:
   To delete a file from a specific collection, use the `deleteFile()` method:

   ```php
   $user->deleteFile('path/to/file.jpg', 'profile_pictures');
   ```

3. **Get Files**:
   Retrieve all files from a model’s files column:

   ```php
   $files = $user->getFiles();
   ```

4. **Get Files from a Collection**:
   Get files from a specific collection, with optional resizing:

   ```php
   $files = $user->getFilesFromCollection('profile_pictures', 300, 300);
   ```

5. **Replace a File**:
   You can replace an old file with a new one in a collection:

   ```php
   $user->replaceFileInCollection($newFile, 'profile_pictures');
   ```

6. **Get Resized Image**:
   Get a resized version of a file by specifying dimensions:

   ```php
   $resizedUrl = $user->getResizedImage('path/to/file.jpg', 300, 300);
   ```

7. **Automatically Cast Files as Array**:
   The files attribute is automatically cast as an array when accessing it:

   ```php
   $files = $user->files; // Returns array of files
   ```

## Methods Overview

- **uploadFile($file, $collection = null, $disk = null)**: Upload a file to a collection and disk.
- **deleteFile($filePath, $collection = null, $disk = null)**: Delete a file from a collection and disk.
- **getFiles()**: Get all files from the model.
- **getFilesFromCollection($collection = null, $width = null, $height = null, $disk = null)**: Get files from a collection with optional resizing.
- **replaceFileInCollection($newFile, $collection = null, $disk = null)**: Replace an old file with a new one in a collection.
- **getResizedImage($filePath, $width, $height, $disk = null)**: Get a resized version of an image.
- **getFirstFileFromCollection($collection = null, $width = null, $height = null, $disk = null)**: Get the first file from a collection.
- **getFilesAttribute($value)**: Automatically cast the 'files' column to an array.

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
