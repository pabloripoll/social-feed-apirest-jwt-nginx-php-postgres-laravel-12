<?php

namespace App\Http\Controllers\Media;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FileController extends Controller
{
    /**
     * Files formats allowed to upload
     */
    public function allowedFormats(): array
    {
        return [
            'audio' => ['mp3', '3gp', 'mp4', 'm4a'],
            'video' => ['mp4'],
            'image' => ['jpg', 'jpeg', 'png', 'gif'],
        ];
    }

    /**
     * Stores files locally if log them is required
     */
    public function logFile(Request $request, string $key): void
    {
        $file = $request->file($key);

        copy($_FILES[$key]['tmp_name'], dirname(__DIR__, 4).'/storage/app/public/'.$file->getClientOriginalName());
    }

    /**
     * Files attributes from request upload
     */
    public function fromRequest(Request $request, string $key): object
    {
        $file = $request->file($key);

        if (! $file) {
            return (object) [];
        }

        $tmp_path = dirname($file->getPathName()).'/';

        return (object) [
            'uploaded' => $file,
            'size' => $file->getSize(),
            'size_kilo' => number_format(($file->getSize() / 1024), 2),
            'size_mega' => number_format(($file->getSize() / 1024 / 1024), 2),
            'extension' => $file->extension(),
            'location' => $file->getPathName(),
            'tmp_path' => $tmp_path,
            'tmp_name' => str_replace($tmp_path, '', $file->getPathName()),
            'name' => $file->getClientOriginalName() ?? Carbon::now()->format('Y-m-d_H-i-s').'.'.$file->extension(),
        ];
    }
}
