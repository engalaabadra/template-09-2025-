<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Resources\BaseResource;
use App\Resources\FileResource;

/**
 * Class PaymentMethodResource
 *
 * Transforms a Banner model into a structured JSON response.
 * Inherits field rendering logic from BaseResource and appends
 * additional fields like image URLs.
 *
 * @property \App\Models\PaymentMethod $resource
 */
class PaymentMethodResource extends BaseResource
{
    // Define which relations use which Resource
    public array $relationsResources = [

    ];
    /**
     * Transform the resource into an array.
     *
     * This method customizes the API representation by including fields
     * from BaseResource (based on fillable attributes), and additional
     * fields such as related media or accessors.
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
