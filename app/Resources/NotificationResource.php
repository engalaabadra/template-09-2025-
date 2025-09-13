<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use App\Resources\BaseResource;
use App\Resources\UserBasicResource;

/**
 * Class NotificationResource
 *
 * A resource class that transforms the Notification model for API responses.
 * It extends BaseResource to inherit the default field transformations and allows
 * appending additional attributes like related usernames or custom accessors.
 *
 * @property \App\Models\Notification $resource
 */
class NotificationResource extends BaseResource
{
    // Define which relations use which Resource
    public array $relationsResources = [
        'user' => UserBasicResource::class,
    ];
    /**
     * Transform the resource into an array.
     *
     * This method customizes the API response by including both the base fields
     * and any extra related data (e.g., user information).
     * 
     * @param  \Illuminate\Http\Request  $request  The current request instance
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
