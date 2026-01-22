<?php

namespace App\Http\Services\Storage\Aws;

use Illuminate\Support\Facades\Storage;

class StorageService
{
    /**
     * Stores single from the location = path/file into predifined bucket storage
     */
    public function put(object $file, object $bucket): ?object
    {
        $bucket->path = $bucket->path ?? null;
        $bucket->name = $bucket->name ?? null;
        $bucket->disk = $bucket->disk ?? env('AWS_URL');

        if (! $bucket->path || ! $bucket->name) {
            return null;
        }

        $destiny = \urldecode($bucket->path.'/'.$bucket->name);
        $storage = Storage::disk('s3')->put($destiny, file_get_contents($file->location));

        if (! $storage) {
            return null;
        }

        $response = new \stdClass;
        $response->url = $bucket->disk.$destiny;
        $response->path = $bucket->path;
        $response->name = $bucket->name;
        $response->extension = $file->extension;

        return $response;
    }

    /**
     * Deletes single file by url or multimedia row database object from bucket storage
     */
    public function delete(object|string $file, ?string $bucket = null): ?object
    {
        $bucket = $bucket ?? env('AWS_URL').'/';
        $object = urldecode(str_replace($bucket, '', (is_object($file) ? $file->url : $file)));

        Storage::disk('s3')->delete($object);

        $response = new \stdClass;

        if (isset($file->id)) {
            $response->id = $file->id;
            $response->type = $file->type;
            $response->extension = $file->extension;
        }

        $response->storage = (bool) Storage::disk('s3')->exists($object);
        $response->url = is_object($file) ? $file->url : $file;

        return $response;
    }

    /**
     * Hide real bucket domain address by current project's one
     */
    public static function bucketProxy(string $link, ?string $bucket = null): string
    {
        $bucket = ($bucket ?? env('AWS_URL'));
        $bucket = rtrim($bucket, '/').'/';
        $domain = env('APP_URL');
        $proxy = $domain.'/shared/files/';

        return strpos($link, $bucket) === false ? $link : str_replace($bucket, $proxy, $link);
    }
}
