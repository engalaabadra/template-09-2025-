<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Resources\BaseResource;

/**
 * Class ContactResource
 *
 * A resource class for transforming Contact model data into a structured JSON response.
 * Inherits from BaseResource to automatically include model fillable attributes,
 * and allows for the inclusion of additional custom or related fields.
 *
 * @property \App\Models\Contact $resource
 */
class ContactResource extends BaseResource
{
    // Define which relations use which Resource
    public array $relationsResources = [
        'files' => FileResource::class,
    ];

    /**
     * Transform the resource into an array.
     *
     * This method customizes the API representation by extending BaseResource
     * and optionally adding additional fields such as relations or accessors.
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
