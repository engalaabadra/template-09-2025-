<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Resources\BaseResource;

/**
 * Class BoardResource
 *
 * Resource class used to transform Board model data for JSON responses.
 * It extends the BaseResource to include default fillable fields and 
 * optionally adds extra attributes like image paths or accessors.
 *
 * @property \App\Models\Board $resource
 */
class BoardResource extends BaseResource
{
    // Define which relations use which Resource
    public array $relationsResources = [
        'image' => FileResource::class
    ];

    /**
     * Transform the resource into an array.
     *
     * This method customizes the API representation by extending BaseResource
     * with additional attributes such as image paths or accessors.
     * 
     * @param  \Illuminate\Http\Request  $request  The current HTTP request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // Load main fields dynamically (default fillable attributes) from BaseResource
        $data = parent::toArray($request);

        // Add specific  Handling data  here
        
        return $data;
    }
}
