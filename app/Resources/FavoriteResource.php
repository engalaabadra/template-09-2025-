<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Resources\UserBasicResource;

/**
 * Class FavoriteResource
 *
 * A resource class responsible for formatting Favorite model data for API responses.
 * Extends JsonResource and leverages the base transformation while allowing the
 * inclusion of related data such as the associated user's username.
 *
 * @property \App\Models\Favorite $resource
 */
class FavoriteResource extends JsonResource
{
     // Define which relations use which Resource
    public array $relationsResources = [
        'user' => UserBasicResource::class,
        'content' => ContentResource::class,
    ];
    /**
     * Transform the resource into an array.
     *
     * This method customizes the API representation by extending the parent transformation,
     * and optionally includes extra fields such as image paths, accessors, or relationships.
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
