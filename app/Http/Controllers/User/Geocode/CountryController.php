<?php

namespace App\Http\Controllers\User\Geocode;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Repositories\User\Geocode\Country\CountryRepository;
use App\Services\User\Geocode\Country\CountryService;
use App\Models\Geocodes\Country;
use App\Resources\Geocode\CountryResource;
use App\Http\Requests\User\Geocode\Country\StoreCountryRequest;
use App\Http\Requests\User\Geocode\Country\UpdateCountryRequest;

/**
 * CountryController handles country-related CRUD operations.
 * It supports both API and Web requests using Inertia.
 */
class CountryController extends BaseController
{
    #region Constructor
    /**
     * @var CountryService Handles business logic
     */
    protected $countryService;

    /**
     * @var CountryRepository Handles data access layer
     */
    protected $countryRepository;

    /**
     * @var Country The Country model instance
     */
    protected $country;

    /**
     * CountryController constructor.
     *
     * @param Country $country
     * @param CountryRepository $countryRepository
     * @param CountryService $countryService
     */
    public function __construct(Country $country, CountryRepository $countryRepository, CountryService $countryService)
    {
        $this->country = $country;
        $this->countryRepository = $countryRepository;
        $this->countryService = $countryService;
    }

    #endregion Constructor
    
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a list of countries.
     * Supports both API (JSON) and Web (Inertia) responses.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all countries
        $result = $this->countryRepository->getData($this->country);

       // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // Otherwise, return API response with optional transformation
        return $this->respond($result, CountryResource::class);
    }
    
    #endregion ===================== End CRUD Methods(Get) =====================

}
