<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BaseResource
 *
 * Uses SmartAttributesTrait to get filtered attributes + relations.
 */
class BaseResource extends JsonResource
{
    public function toArray($request)
    {
        if (is_array($this->resource)) {
            return $this->resource;
        }

        $relationsResources = property_exists($this, 'relationsResources')
            ? $this->relationsResources
            : [];

        return $this->resource->getSmartAttributes($relationsResources);
    }
}
