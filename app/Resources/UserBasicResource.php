<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use App\Resources\BaseResource;
use App\Resources\FileResource;

/**
 * Class UserBasicResource
 *
 * A resource class responsible for transforming the User model 
 * and its related data (like profile, country, roles, etc.) 
 * into a structured API response.
 *
 * @property \App\Models\User $resource
 */
class UserBasicResource extends BaseResource
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
        return [

            'id' => $this->id,
            'username' => $this->whenLoaded('profile', fn() => $this->profile?->username),
            'email' => $this->email,
            'phone_no' => $this->phone_no,
            'image' => $this->whenLoaded('image', function () {
                return new FileResource($this->image);
            }),
        ];
    }
}
