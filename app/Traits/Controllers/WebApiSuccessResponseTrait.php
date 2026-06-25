<?php

namespace App\Traits\Controllers;

use App\Enums\ServiceResponseEnum;
use App\Services\ServiceResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Trait WebApiSuccessResponseTrait
 *
 * Provides a unified way to return success responses for both API and Web:
 * - API → returns structured JSON with optional resource transformation, pagination, and filters.
 * - Web → redirects with flash success messages.
 *
 * Example usage:
 * ```php
 * class UserController
 * {
 *     use WebApiSuccessResponseTrait;
 *
 *     public function index()
 *     {
 *         $users = User::paginate();
 *         return $this->respond($users, UserResource::class, 'Users fetched successfully');
 *     }
 * }
 * ```
 */
trait WebApiSuccessResponseTrait
{
     /**
     * Return a unified API or Web response based on the request type.
     *
     * @param mixed $data Data or ServiceResponse instance.
     * @param string|null $message Optional success message.
     * @param string|null $resourceClass Optional resource class for formatting data.
     * @param string|null $redirectRoute Optional route name for web redirection.
     * @return JsonResponse|\Illuminate\Http\RedirectResponse JSON response or redirect.
     */
    protected function respond($data = null, string $resourceClass = null, $message = null, ?string $redirectRoute = null): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        // Check if the request expects JSON or not
        return isWebRequest()
            ? $this->handleWeb($data, $message = null, $redirectRoute)
            : $this->handleApi($data, $message = null, $resourceClass);
    }

    /**
     * Handle API response by formatting data as structured JSON.
     *
     * @param mixed $data Raw data to respond with.
     * @param string|null $message Optional message for the response.
     * @param string|null $resourceClass Optional Laravel resource class for formatting data.
     * @return JsonResponse Structured JSON success response.
     */

    protected function handleApi($data, $message = null, ?string $resourceClass = null): JsonResponse
    {
        // If request contains 'report' parameter as boolean true, ignore resource wrapping
        if (request()->boolean('report')) {
            $resourceClass = null;
        }

        // If request contains 'report' parameter as boolean true, ignore resource wrapping
        if ($data instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
            return $data;
        }

        // If data is a URL string that starts with storage URL, return a JSON success with that URL
        if (is_string($data) && Str::startsWith($data, url('/storage'))) {
            return $this->jsonSuccessResponse($data, $message);
        }

         // ====== Support for combined structure ['data' => ..., 'filters' => ...] ======
        $filters = null;

        // Check if data is an array containing both 'data' and 'filters'
        if (is_array($data) && array_key_exists('data', $data) && array_key_exists('filters', $data)) {
            $filters = $data['filters'];   // Extract filters to use later
            $data = $data['data'];         // Extract only the actual data for resource processing
        }

        // ================== Cases =====================
        // Summary:
        // - If resource class :
        //      - Pagination → use $resourceClass::collection($data)->response()->getData(true)
        //      - Collection → use $resourceClass::collection($data)->toArray(request())
        //      - Non Pagination & Non Collection (single item) → use (new $resourceClass($data))->toArray(request())
        // - If no resource class : just convert data to array directly ($data->toArray(request()))
        // Apply resource transformation
     
        if ($resourceClass) {
            // If a resource class is provided, we transform the data using that resource
            if ($data instanceof Paginator || $data instanceof LengthAwarePaginator) {  
                // Case 1: Data is paginated  
                // Wrap it in the resource collection and keep the full pagination metadata  
                $data = $resourceClass::collection($data)->response()->getData(true);  

            } elseif ($data instanceof \Illuminate\Support\Collection) {  
                // Case 2: Data is a plain Collection without pagination  
                // Wrap it in the resource collection and convert to an array  
                $data = $resourceClass::collection($data)->toArray(request());  

            } else {  
                // Case 3: Data is a single item (Model or array)  
                // Wrap it in a single resource and convert to an array  
                $data = (new $resourceClass($data))->toArray(request());  

            }

        } elseif ($data instanceof Paginator || $data instanceof LengthAwarePaginator || $data instanceof \Illuminate\Support\Collection) {  
            // If no resource class is provided but the data is paginated or a Collection  
            // Convert it directly to an array without resource transformation  
            $data = $data->toArray(request());
        } else {
            $data;

        }

        // Merge filters once at the end
        if ($filters !== null) {
            $data = ['data' => $data, 'filters' => $filters];
        }

        // Normalize all data (Models -> arrays, Collections -> arrays, Arrays cleaned)
        $data = $this->normalizeData($data);

        // Return JSON success response with final data
        return $this->jsonSuccessResponse($data, $message);
    }
    
    /**
     * Recursively normalize data:
     * - Convert Models to array (respect hidden attributes)
     * - Convert Collections to array
     * - Clean arrays containing models
     */
    protected function normalizeData($data)
    {
        if ($data instanceof \Illuminate\Database\Eloquent\Model) {
            return $data->toArray();
        }

        if ($data instanceof \Illuminate\Support\Collection) {
            return $data->map(function ($item) {
                return $this->normalizeData($item);
            })->toArray();
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->normalizeData($value);
            }
            return $data;
        }

        return $data;
    }
    /**
     * Handle Web (non-API) response with redirect and flash message.
     *
     * @param mixed $data Data to pass along (not usually used here).
     * @param string|null $message Optional success message for flash.
     * @param string|null $redirectRoute Optional route name to redirect to.
     * @return \Illuminate\Http\RedirectResponse Redirect response with flash message.
     */
    protected function handleWeb($data, $message = null, ?string $redirectRoute = null)
    {
        // Determine redirect target: either specific route or back to previous page
        $redirect = $redirectRoute ? redirect()->to(route($redirectRoute)) : redirect()->back();

        // Attach success status and message as flash session data
        return $redirect->with([
            'status' => true,
            'message' => $message ?? ServiceResponseEnum::SUCCESS->message(), // Default success message
        ]);
    }

    /**
     * Return a standardized JSON success response.
     *
     * @param mixed|null $data Data to include in response.
     * @param string|null $message Optional success message.
     * @return JsonResponse JSON response with status, message, and data.
     */
    protected function jsonSuccessResponse($data = null, string $message = null): JsonResponse
    {

        // Use 202 if data is empty/null/empty array, else 200
        $statusCode = (empty($data) || $data === []) ? 202 : 200;

        // ====== Support for combined structure ['data' => ..., 'filters' => ...] ======
        $meta = [];

        // Check if data is an array containing both 'data' and 'filters'
        if (is_array($data) && array_key_exists('data', $data) && array_key_exists('filters', $data)) {
            $meta['filters'] = $data['filters'];  // Extract filters into meta
            $data = $data['data'];                // Keep only the actual data
        }

        // Build and return JSON response structure
        return response()->json([
            'status' => true,                                 // Indicate success
            'message' => $message ?? ServiceResponseEnum::SUCCESS->message(), // Default or custom message
            'data' => $data,   
            'meta'    => $meta, // filters or any other meta data go here
        ], $statusCode);
    }

}

