<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use App\Resources\BaseResource;

/**
 * Class FileResource
 *
 * A resource class responsible for transforming the User model 
 * and its related data (like profile, country, roles, etc.) 
 * into a structured API response.
 *
 * @property \App\Models\User $resource
 */
class FileResource extends BaseResource
{
   
    /**
     * Transform the resource into an array.
     *
     * This method customizes the API representation by extending BaseResource 
     * and adding extra attributes such as profile data, translations, roles, etc.
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
