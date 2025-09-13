<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use App\Resources\BaseResource;
use App\Resources\FileResource;
use App\Resources\ProfileResource;
use App\Resources\Auth\RoleResource;
use App\Resources\Geocode\CountryResource;

/**
 * Class UserResource
 *
 * A resource class responsible for transforming the User model 
 * and its related data (like profile, country, roles, etc.) 
 * into a structured API response.
 *
 * @property \App\Models\User $resource
 */
class UserResource extends BaseResource
{
    // Define which relations use which Resource
    public array $relationsResources = [
        'roles' => RoleResource::class,
        'profile' => ProfileResource::class,
        'files' => FileResource::class,
        'country' => CountryResource::class,
    ];
    /*
        Example Output Structure:
        {
            "lang": "ar",
            "email": "employee@example.com",
            "username": "موظف1",
            "translations": [
                {
                    "lang": "en",
                    "username": "employee Name"
                },
                {
                    "lang": "fr",
                    "username": "Nom de l'enseignant"
                }
            ]
        }
    */

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
