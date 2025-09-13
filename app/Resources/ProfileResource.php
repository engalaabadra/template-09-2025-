<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Resources\UserBasicResource;

/**
 * Class ProfileResource
 *
 * A resource class used to transform the Profile model for API responses.
 * Extends BaseResource to return structured and customizable output,
 * including related user data and appended attributes like the profile image.
 *
 * @property \App\Models\Profile $resource
 */
class ProfileResource extends BaseResource
{
    // Define which relations use which Resource
    public array $relationsResources = [
        'user' => UserBasicResource::class
    ];
    /**
     * Transform the resource into an array.
     *
     * This method customizes the API representation by including default model data
     * from the base JsonResource and appending related or computed fields.
     * 
     * @param  \Illuminate\Http\Request  $request  The current HTTP request instance
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
