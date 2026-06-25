<?php

namespace App\Models\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Traits\Relations\MediaRelationsTrait;
use App\Enums\ServiceResponseEnum;
 

/**
 * Trait HasMediaTrait
 *
 * Provides common functionality for uploading, storing, retrieving, and deleting media files 
 * (e.g., images and files) associated with a model.
 * 
 * This trait assumes you have defined appropriate media relationships 
 * via MediaRelationsTrait (e.g., image(), images(), file(), files()).
 */
trait HasMediaTrait
{
    /**
     * Handle file uploads.
     *
     * @param object $request  The request object containing validated data.
     * @param object $model    The model to query.
     * @param object $item     The item being processed.
     */
    public function handleFiles($request,  $model, $item)
    {
        $data = $request->validated();

        // Handle file uploads
        $folder = modelName($model);
        // Upload single or multiple media based on provided input
        foreach (['file' => 'file', 'image' => 'image'] as $key => $type) {

            if (isset($data[$key])) {
                $this->uploadSingleMedia($request->file($key), $type, $folder);
            }
        }

        foreach (['files' => 'file', 'images' => 'image'] as $key => $type) {
            if (isset($data[$key])) {
                $this->uploadMultipleMedia($request->file($key), $type, $folder);
            }
        }

    }

    /**
     * Store a single uploaded media file in the specified folder.
     *
     * - Generates a unique filename by appending the current timestamp.
     * - Stores file under "uploads/{folder}" path in the `public` disk.
     *
     * @param  UploadedFile $file   The uploaded file.
     * @param  string       $type   Type of media (e.g., 'image', 'file').
     * @param  string       $folder Destination folder name.
     * @return string               The full path to the stored file.
     */
    protected function storeMediaFileInFolder(UploadedFile $file, string $type, string $folder): string
    {
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filenameToStore = "{$filename}_" . time() . ".{$extension}";

        return $file->storeAs("uploads/{$folder}", $filenameToStore, 'public');
    }

    /**
     * Upload and attach a single media file to the model.
     *
     * - Supports both 'image' and 'file' types.
     * - Automatically updates the existing related media if exists; otherwise creates a new one.
     *
     * @param  UploadedFile $file   The uploaded file.
     * @param  string       $type   Type of media ('image' or 'file').
     * @param  string       $folder Folder name to store the media.
     * @return string               Publicly accessible URL of the uploaded file.
     */
    public function uploadSingleMedia(UploadedFile $file, $type, string $folder): string
    {
        $path = $this->storeMediaFileInFolder($file, $type, $folder);
        $url = str_replace('public/', 'storage/', $path);

        $data = [
            'url' => $url,
            'type' => $type,
        ];

        if ($type === 'image') {
            $this->image()->exists() ? $this->image()->update($data) : $this->image()->create($data);
        } elseif ($type === 'file') {
            $this->file()->exists() ? $this->file()->update($data) : $this->file()->create($data);
        }

        return $url;
    }

    /**
     * Upload and attach multiple media files to the model.
     *
     * - Stores all files under the given folder.
     * - Creates many records in the related media table.
     *
     * @param  array  $files  Array of UploadedFile objects.
     * @param  string $type   Type of media ('image' or 'file').
     * @param  string $folder Target folder name.
     * @return array          Array of uploaded items with URLs and types.
     */
    public function uploadMultipleMedia(array $files, $type, string $folder): array
    {
        $uploaded = [];
        foreach ($files as $file) {
            $path = $this->storeMediaFileInFolder($file, $type, $folder);
            $url = str_replace('public/', 'storage/', $path);
            $uploaded[] = ['url' => $url, 'type' => $type];
        }

        // Select the correct relation based on type
        $relation = $type === 'image' ? $this->images() : $this->files();

        // Check if the relation has any existing records
        if ($relation->exists()) {
            // Delete old records first
            // This ensures that we remove previous media/files of this type
            $relation->delete();

            // Insert new records
            // createMany() will insert multiple related records in one call
            $relation->createMany($uploaded);
        } else {
            // No existing records found
            // Simply add new records without deleting anything
            $relation->createMany($uploaded);
        }


        return $uploaded;
    }

    /**
     * Delete media records by their IDs from the specified relation.
     *
     * - Supports deletion from any relation name (e.g., 'images', 'files').
     * - Also deletes physical files from the disk.
     * - Returns `NOT_FOUND` enum if any of the IDs do not belong to the relation.
     *
     * @param  array|string  $ids       Array or string("all") of media IDs to delete. Can include comma-separated string as a single item.
     * @param  string $relation  The media relation name on the model (e.g., 'files', 'images').
     * @return int|\App\Enums\ServiceResponseEnum
     *
     * @throws \InvalidArgumentException If the relation method does not exist.
     */
    
    public function deleteMediaByIds($ids, string $relation)
    {
        if (! method_exists($this, $relation)) {
            throw new \InvalidArgumentException("Relation [$relation] does not exist on model " . static::class);
        }

        // If "all", delete everything in the relation
        if ($ids === 'all') {
            return $this->$relation()->delete();
        }

        // Otherwise, $ids is guaranteed by IdsRule to be an array of integers
        return $this->$relation()->whereIn('id', $ids)->delete();
        
    }


    /**
     * Delete a single related media item (e.g., `image` or `file`).
     *
     * - Automatically deletes the file from storage if it exists.
     * - Deletes the database record.
     *
     * @param  string $relation  The name of the relation method (e.g., 'image', 'file').
     * @return void
     */
    public function deleteSingleMedia(string $relation): void
    {
        $item = $this->{$relation};
        if ($item) {
            $path = str_replace(url('storage') . '/', '', $item->url);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            $item->delete();
        }
    }
}
