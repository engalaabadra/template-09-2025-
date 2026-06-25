<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Resources\BaseResource;
use App\Resources\UserBasicResource;
use App\Resources\PaymentMethodResource;

/**
 * Class OrderResource
 *
 * A resource class used to transform the Order model for API responses.
 * It extends the BaseResource to include the model’s base fields and adds
 * any additional relationships or computed attributes as needed.
 *
 * @property \App\Models\Order $resource
 */
class OrderResource extends BaseResource
{
    // Define which relations use which Resource
    public array $relationsResources = [
        'user' => UserBasicResource::class,
        'paymentMethod' => paymentMethodResource::class
    ];
    /**
     * Transform the resource into an array.
     *
     * This method customizes the API representation by merging the default
     * BaseResource fields with extra fields such as related usernames or payment methods.
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
