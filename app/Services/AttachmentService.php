<?php

namespace App\Services;

use App\Helper\StrHelper;
use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseError;
use App\Http\Response\ResponseSuccess;
use App\Repositories\AttachmentRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    public function __construct(protected readonly AttachmentRepository $attachmentRepository)
    {

    }

    /**
     * @param string $base64Encode
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function create(string $base64Encode): ApiResponse
    {
        $disk = 'public';
        $name = "/".date('Ym')."/".Str::uuid().".jpeg";
        $upload = Storage::disk($disk)->put($name, base64_decode($base64Encode));
        if (!$upload) {
            return new ResponseError();
        }

        /** @var \App\Models\Attachment $create */
        $create = $this->attachmentRepository->create([
            'id' => $this->attachmentRepository->getId(),
            'path' => $name,
            'disk' => $disk,
            'use' => false
        ]);

        return new ResponseSuccess([
            'attachment_id' => $create->id,
            'url' => Storage::disk($disk)->url($name)
        ]);
    }

    /**
     * @param \Illuminate\Http\UploadedFile $file
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function upload(UploadedFile $file):ApiResponse
    {
        $ext = $file->extension();
        $disk = 'public';
        $fileName = "/".date('Ym')."/".time().Str::slug($file->getClientOriginalName()).".".$ext;
        $upload = Storage::disk($disk)->put($fileName, file_get_contents($file));
        if (!$upload) {
            return new ResponseError();
        }

        /** @var \App\Models\Attachment $create */
        $create = $this->attachmentRepository->create([
            'id' => $this->attachmentRepository->getId(),
            'path' => $fileName,
            'disk' => $disk,
            'use' => false
        ]);

        return new ResponseSuccess([
            'attachment_id' => $create->id,
            'url' => Storage::disk($disk)->url($fileName)
        ]);

    }
}
