<?php

namespace App\Http\Controllers\Dashboard\Geocode;

use App\Http\Controllers\BaseController;
use App\Repositories\Dashboard\Geocode\Country\CountryRepository;
use App\Services\Dashboard\Geocode\Country\CountryService;
use App\Models\Geocodes\Country;
use App\Resources\Geocode\CountryResource;
use App\Http\Requests\Dashboard\Geocode\CountryRequest;

use Inertia\Inertia;
use App\Http\Requests\BulkActivationActionRequest;
use App\Http\Requests\File\DeleteFilesRequest;
use App\Http\Requests\ActivationActionRequest;
use App\Http\Requests\RestoreActionRequest;
use App\Http\Requests\BulkDeleteActionRequest;
use App\Http\Requests\BulkRestoreActionRequest;

/**
 * Class CountryController
 *
 * Handles country management operations for dashboard including:
 * CRUD actions, activation/deactivation, trash management and file uploads.
 */
class CountryController extends BaseController
{

    #region Constructor

    /**
     * @var CountryService Handles business logic
     */
    protected $CountryService;

    /**
     * @var CountryRepository Handles data access layer
     */
    protected $countryRepository;
    /**
     * @var Country
     * The Country model instance.
     */
    protected $country;

    /**
     * CountryController constructor.
     * Dependency Injection for Country model, CountryService.
     *
     * @param Country $country
     * @param CountryService $CountryService
     * @param CountryRepository $countryRepository
     */
    public function __construct(Country $country, CountryService $CountryService, CountryRepository $countryRepository)
    {
        $this->country = $country;
        $this->CountryService = $CountryService;
        $this->countryRepository = $countryRepository;
    }
    #endregion Constructor

    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of countries.
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function index()
    {
        $result = $this->countryRepository->getData($this->country);  // Fetch country data (may be paginated or collection)

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in CountryResource
        return $this->respond($result, CountryResource::class);
    }

    /**
     * Show details of a specific country.
     *
     * @param int $id Country ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function show($id)
    {
        $result = $this->countryRepository->show($id, $this->country); // Retrieve country details

        if (isWebRequest()) { // If web request, setup breadcrumb navigation
            $this->setBreadcrumb('countries', 'show', $id); 
        }

        // Respond with country data wrapped in CountryResource
        return $this->respond($result, CountryResource::class);
    }

    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    /**
     * Store a new country or update an existing one.
     *
     * @param CountryRequest $request Validated country creation/update request
     * @param int|null $id Country ID to update, or null to create new
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(CountryRequest $request, $id = null)
    {
        $result = $this->CountryService->store($request, $this->country, $id); // Create or update country via service
        // Respond with status 201 Created and redirect route 'dashboard.countries.index'
        return $this->respond($result, CountryResource::class);
        //return $this->respond($result, CountryResource::class, $message = null, 'dashboard.countries.index');
    }

    /**
     * Update an existing country.
     *
     * @param CountryRequest $request Validated country update request
     * @param int $id Country ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(CountryRequest $request, $id)
    {
        // Update the country data by calling the CountryService
        $result = $this->CountryService->update($request, $id, $this->country);
        // Return the response wrapped with CountryResource,
        // which formats the country data consistently for API or web responses
        return $this->respond($result, CountryResource::class);
    }

    
    /**
     * Permanently delete a country from the database.
     *
     * @param int $id Country ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        $result = $this->CountryService->forceDelete($id, $this->country);
        return $this->respond($result);
    }

    /**
     * Permanently delete multiple countries at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDeleteMany(BulkDeleteActionRequest $request)
    {
        $result = $this->CountryService->forceDeleteMany($request, $this->country);
        return $this->respond($result);
    }

    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================

    /**
     * Soft delete a country (mark as deleted without removing from DB).
     *
     * @param int $id Country ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $result = $this->CountryService->destroy($id, $this->country);
        return $this->respond($result, CountryResource::class);
    }

    /**
     * Soft delete multiple countries at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyMany(BulkDeleteActionRequest $request)
    {
        $result = $this->CountryService->destroyMany($request, $this->country);
        return $this->respond($result);
    }

    
    /**
     * Restore a soft deleted country.
     *
     * @param int $id Country ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restore(RestoreActionRequest $request, $id)
    {
        $result = $this->CountryService->restore($request, $id, $this->country);
        return $this->respond($result, CountryResource::class);
    }

    /**
     * Restore multiple soft deleted countries at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restoreMany(BulkRestoreActionRequest $request)
    {
        $result = $this->CountryService->restoreMany($request, $this->country);
        return $this->respond($result);
    }
    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate(Request $request, $id), changeActivateMany(Request $request)=====================
    
    /**
     * Toggle activation status (activate/deactivate) for a specific country.
     *
     * @param int $id Country ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivate(ActivationActionRequest $request, $id)
    {
        $result = $this->CountryService->changeActivate($request, $id, $this->country);
        return $this->respond($result, CountryResource::class);
    }

    /**
     * Activate or deactivate multiple countries at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivateMany(BulkActivationActionRequest $request)
    {
        $result = $this->CountryService->changeActivateMany($request, $this->country);
        return $this->respond($result);
    }

    #endregion ===================== End ACTIVATION Methods =====================

}
