<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Resources\BaseResource;
use App\Resources\UserBasicResource;

/**
 * Class ReviewResource
 *
 * A resource class used to transform the Review model for API responses.
 * Inherits from BaseResource and includes additional fields such as related user data.
 *
 * @property \App\Models\Review $resource
 */
class ReviewResource extends BaseResource
{
    // Define which relations use which Resource
    public array $relationsResources = [
        'user' => UserBasicResource::class,
    ];
    /**
     * Transform the resource into an array.
     *
     * This method customizes the API representation by extending BaseResource 
     * and adding extra fields like related user information.
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
