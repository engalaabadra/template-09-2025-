<?php

namespace App\Repositories\Eloquent;

use App\Scopes\ActiveScope;
use App\Repositories\Eloquent\EloquentRepositoryInterface;
use App\Repositories\Base\BaseRepository;
use App\Traits\Controllers\InertiaShareTrait;

/**
 * EloquentRepository
 *
 * This is a Eloquent Repository class implementing the EloquentRepositoryInterface.
 * It provides methods for using in whole project such as : getData, show, trash, report
 */
class EloquentRepository extends BaseRepository implements EloquentRepositoryInterface
{
    use InertiaShareTrait; //for prepareUiData()

    #region ===================== Start CRUD Methods: getData($model), show($model, $id) =====================

    /**
     * Get Data (all, pagination) -> Taking into consideration language.
     *
     * @param object $model The model to query.
     * @param  bool    $forUser  if false (get data that only has it this user)
     * @return array Paginated or full collection of results.
     */
    public function getData($model, $forUser = false)
    {
        /** Base Query */
        // Eager loading relations - Ownership restrictions (`user_id`) if `$forUser` is true - Request-based filters via `FilterTrait` - Column-based search via `SearchTrait` - Ordering by latest ID.  
        $query = $this->buildBaseQuery($model, $forUser); // from baseRepo

        /** Fetch data (paginated or all) */
        $data = page() ? $query->paginate() : $query->get();

        /** Prepare UI-related data for a model */
        $result = $this->prepareUiData($model, $data); // from InertiaShareTrait (result -> API: filters, WEB: render filter &data in Inertia)

        /** Handle WEB request */
        if (isWebRequest()) {
            return $result;
        }

        /** Handle REPORT request */
        if (request()->boolean('report')) {
            return $this->handleReport($model, $result); // from baseRepo
        }

        /** Handle EXPORT request */
        if (request()->boolean('export')) {
            return $this->exportToExcel($model, $query); // from baseRepo
        }

        /** Return in API */
        return [
            'data'    => $data,
            'filters' => $result,
        ];
    }

    
    /**
     * Show a specific record.
     *
     * @param int $id The ID of the record to show.
     * @param object $model The model to query.
     * @param  bool    $forUser  if false (show data that only has it this user)
     * 
     * @return object The requested record.
     */
    public function show($id, $model, $forUser = false)
    {
        $item = $this->findOrFailApi($id, $model, $forUser);

        $data = $model->getProp('eagerLoading')                 // Eager load relations if defined
            ? $item->load($model->getProp('eagerLoading'))
            : $item;

        return $data;
    }

    #endregion ===================== End CRUD Methods =====================

}


