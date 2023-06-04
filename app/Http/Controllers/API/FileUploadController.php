<?php

namespace App\Http\Controllers\API;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use App\Models\TempFile;
use App\Models\File;
use Illuminate\Support\Str;

use App\Models\Media;

use App\Http\Controllers\API\BaseController as BaseController;

class FileUploadController extends BaseController {

    public function uploadLargeFiles(Request $request) {
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        if (!$receiver->isUploaded()) {
            // file not uploaded
        }

        $fileReceived = $receiver->receive(); // receive file
        if ($fileReceived->isFinished()) { // file uploading is complete / all chunks are uploaded
            $file = $fileReceived->getFile(); // get file
            $uuid = $request->get('uuid');
            $extension = $file->getClientOriginalExtension();
            $title = $file->getClientOriginalName(); //file name without extenstion
            $fileTitle =  $title;
            $fileName = $uuid . '.' . $extension; // a unique file name

            $disk = Storage::disk(config('filesystems.default'));
            $disk->putFileAs('temp_files', $file, $fileName);

            $path = 'files/' . $fileName;

            // delete old temporal files with same uuid
            $old_temp_files = TempFile::where('uuid', $uuid)->delete();

            $temporal_file = TempFile::create([
                'title' => $fileTitle,
                'name' => $fileName,
                'path' => $path,
                'uuid' => $uuid
            ]);

            // delete chunked file
            unlink($file->getPathname());
            return [
                'path' => $path,
                'filename' => $fileName,
                'uuid' => $uuid
            ];
        }

        // otherwise return percentage informatoin
        $handler = $fileReceived->handler();
        return [
            'done' => $handler->getPercentageDone(),
            'status' => true
        ];
    }

    public function updateAssets(Request $request)
    {
        $uuids = $request->get('file_uuid');
        if (isset($uuids) && count($uuids) > 0) {
            foreach ($uuids as $uuid) {
                $temp_file = TempFile::where('uuid', $uuid)->first();
                if (isset($temp_file)) {
                    $old_file = File::where('uuid', $uuid)->delete();

                    $old_path = 'temp_files/' . $temp_file->name;
                    $path = 'files/' . $temp_file->name;

                    if (Storage::exists($path)) {
                        Storage::delete($path);
                    }

                    Storage::move($old_path, $path);

                    $file = File::create([
                        'title' => $temp_file->title,
                        'name' => $temp_file->name,
                        'path' => $path,
                        'uuid' => $uuid
                    ]);
                    //Delete temporal file
                    $temp_file->delete(); 
                    }               
            }
        }

        return $this->sendResponse([], 'Files actualizados correctamente.');
    }

    public function getFileData(Request $request)
    {
        $path = $request->get('path');
        
        $file = File::where('path',$path)->first();

        if ($file) {
            $size = Storage::size($path);
            $file->size = $size;

            return $this->sendResponse($file, 'Files recuperado correctamente.');
        } else {
            return $this->sendError('No hay archivo asignado');
        }
    }

    public function downloadFile(Request $request)
    {
        $path = $request->get('path');

        $p = '7f038850-41ac-4787-937b-0c88b773dc1a.pdf';
        $headers = [
            'Content-Type' => 'application/pdf',
         ];

        return response()->download($p, 'filename.pdf', $headers);
        // return Storage::download($path);

        //return response()->download(storage_path('app/public/files/7f038850-41ac-4787-937b-0c88b773dc1a.pdf'));
    }

    public function getFile($file)
    {
        $p = 'storage/files/' . $file;

        $current_file = File::where('name', $file)->first();

        return response()->download($p, $current_file->title, []);
    }

    public function upload_image(Request $request)
    {
        $image = $request->file('file');
        $new_image = '';
        if ($image) {
            $name = $image->getClientOriginalName();  
            $new_image = $this->uploadImage($image, $name);
            return $this->sendResponse($new_image, 'Imagen creada correctamente.');
        } else {
            return $this->sendError('No hay archivo asignado');
        }
    }

    function uploadImage($image, $name)
    {
        $media = Media::where('title', $name)->first();
        if (isset($media)) {
            // $path = $image->storeAs('media', $name);
            $path = $image->storeAs('media', $name);
            $media->path = $path;
            $media->save();
            return $media;
        } else {
            $path = $image->storeAs('media', $name);
            $img = Media::create([
                'path' => $path,
                'title' => $name,
                'uuid' => $name,
            ]);
            $img->save();
            return $img;
        }
    }
}
