<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Resources\BaseResource;
use App\Resources\UserBasicResource;

/**
 * Class ChatResource
 *
 * Resource class responsible for transforming Chat model data into a JSON-friendly format.
 * This class utilizes Laravel's JsonResource and provides a clear structure for API responses,
 * including optional related data such as user, client, and files.
 *
 * @property \App\Models\Chat $resource
 */
class ChatResource extends BaseResource
{
     // Define which relations use which Resource
    public array $relationsResources = [
        'user' => UserBasicResource::class,
        'client' => UserBasicResource::class,
        'files' => FileResource::class,
    ];
    /**
     * Transform the resource into an array.
     *
     * This method customizes the API representation by adding specific fields
     * or related data to the base resource response.
     * 
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request instance
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // Load main fields dynamically (default fillable attributes) from BaseResource
        $data = parent::toArray($request);

        // Add specific  Handling data  here

        // override the 'created_at' field
        $data['time'] = strtoupper($this->created_at);

        return $data;

    }
}
