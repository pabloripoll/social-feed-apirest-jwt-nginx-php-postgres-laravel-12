<?php

namespace App\Http\Services\Storage\Local;

use Illuminate\Support\Facades\Storage;

class StorageService
{
    /**
     * Stores in storage/app/public/images (public disk)
     */
    public function put(object $file, object $object): ?object
    {
        $object->path = $object->path ?? null;
        $object->name = $object->name ?? null;

        if (! $object->path || ! $object->name) {
            return null;
        }

        // Store in storage/app/public/images (public disk)
        $file->uploaded->storeAs($object->path, $object->name, 'public');

        $response = new \stdClass;
        $response->path = $object->path;
        $response->name = $object->name;
        $response->extension = $file->extension;

        return $response;
    }

    /**
     * Deletes single file by url or multimedia row database object from bucket storage
     */
    public function delete(object|string $file, ?string $bucket = null): ?object
    {
        $bucket = $bucket ?? '';
        $object = $bucket.ltrim((is_object($file) ? $file->path.'/'.$file->name : $file), '/');
        $object = preg_replace('#^static/#', '', $object);

        Storage::disk('public')->delete($object);

        $response = new \stdClass;

        if (isset($file->id)) {
            $response->id = $file->id;
            $response->type = $file->type;
            $response->extension = $file->extension;
        }

        $response->storage = (bool) Storage::disk('public')->exists($object);
        $response->url = is_object($file) ? $file->url : $file;

        return $response;
    }
}
