<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Storage Disk
    |--------------------------------------------------------------------------
    | The default disk to be used for storing files. This can be one of the
    | disks defined in the "filesystems.php" configuration file.
    */
    'storage_disk' => env('MODEL_FILE_STORAGE_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Resized Images Path
    |--------------------------------------------------------------------------
    | The directory where resized images will be stored. You can specify a custom
    | path if necessary. The placeholders {width} and {height} will be replaced
    | dynamically based on the resize dimensions.
    */
    'resized_image_path' => env('RESIZED_IMAGE_PATH', 'resized/{width}x{height}'),

    /*
    |--------------------------------------------------------------------------
    | Default Collection
    |--------------------------------------------------------------------------
    | The default collection name for storing uploaded files. You can define
    | different collections such as 'avatars', 'documents', 'images', etc.
    */
    'default_collection' => env('DEFAULT_FILE_COLLECTION', 'default'),
];
