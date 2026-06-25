<?php

namespace App\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use App\Resources\BaseResource;
use App\Resources\Auth\PermissionResource;

/**
 * Class RoleResource
 *
 * Transforms a Role model into a structured JSON response.
 * Dynamically includes fillable attributes, permissions, and translations.
 */
class RoleResource extends BaseResource
{
    // Define which relations use which Resource
    public array $relationsResources = [
        'permission' => PermissionResource::class
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
