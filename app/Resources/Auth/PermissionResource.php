<?php

namespace App\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

/**
 * Class PermissionResource
 *
 * Transforms a permission model into a structured JSON response,
 * dynamically including fillable attributes and translations.
 */
class PermissionResource extends JsonResource
{
    // Define which relations use which Resource
    public array $relationsResources = [
        
    ];
    /**
     * Transform the resource into an array.
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
