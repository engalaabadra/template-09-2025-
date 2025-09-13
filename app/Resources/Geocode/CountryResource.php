<?php

namespace App\Resources\Geocode;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Resources\BaseResource;

/**
 * Class CountryResource
 *
 * Transforms a Country model into a structured JSON response.
 * Inherits main logic from BaseResource and allows for appending
 * additional attributes such as accessors or custom image paths.
 *
 * @property \App\Models\Country $resource
 */
class CountryResource extends BaseResource
{
    // Define which relations use which Resource
    public array $relationsResources = [
        
    ];

    /**
     * Transform the resource into an array.
     *
     * This method customizes the API representation by extending BaseResource,
     * and allows the inclusion of extra fields like image paths or accessors.
     *
     * @param  \Illuminate\Http\Request  $request
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
