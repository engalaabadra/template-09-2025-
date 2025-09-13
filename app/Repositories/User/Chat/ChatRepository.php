<?php
namespace App\Repositories\User\Chat;

use App\Repositories\Eloquent\EloquentRepository;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use App\Exceptions\ApiResponseException;
use App\Enums\ServiceResponseEnum;
 
/**
 * ChatRepository
 *
 * This is a Chat Repository class implementing the ChatRepositoryInterface.
 * It provides methods such as : getData
 */
class ChatRepository extends EloquentRepository implements ChatRepositoryInterface
{
     // Add specific  Handling data methods here

     #region Constructor
     
    #endregion Constructor

    #region ===================== Start CRUD Methods extends from EloquentRepository: getData($model), show($model, $id) =====================
    
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
        if(!clientId())  throw new ApiResponseException(ServiceResponseEnum::UNPROCESSABLE_ENTITY, null ,[
            'client_id' => __('validation.client_id_nullable')
        ]);

        if(!is_numeric(clientId()))  throw new ApiResponseException(ServiceResponseEnum::UNPROCESSABLE_ENTITY, null ,[
            'client_id' => __('validation.client_id_invalid')
        ]);

       $client = \App\Models\User::where('id', clientId())->first();

       if(!$client)  throw new ApiResponseException(ServiceResponseEnum::UNPROCESSABLE_ENTITY, null ,[
            'client_id' => __('validation.client_id_not_found')
        ]);

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
     * Build and execute a dynamic query with filtering.
     *
     * @param  string  $model  Fully qualified model class
     * @param  bool    $forUser  if true (get data that only has it this user)
     * @return \Illuminate\Database\Eloquent\Builder
     */

    protected function buildBaseQuery($model, $forUser = false)
    {

        $query = $model::query();

        return $query
            // 1. Eager load model relations if defined in the model.
            ->when($model->getProp('eagerLoading'), fn($q) => $q->with($model->getProp('eagerLoading')))

            // 2. Restrict results to the authenticated user if forUser = true AND user_id column exists. (get data that only has it this user)
            //    (via scopeOwnedByUser in OwnedByUserLocalScopeTrait -> user_id = userApi()).
            ->when(
                $forUser && Schema::hasColumn($model->getTable(), 'user_id'),
                fn($q) => $q->OwnedByUser()
            )

            ->where(['user_id' => userApi()?->id, 'client_id' => clientId()])
            // ->orWhere(['client_id' => userApi()?->id, 'user_id' => clientId()])
                
            // 3. Apply all request-based filters dynamically to the query (this method in basebuilder in FilterTrait)
            ->filter()

            // 4. Apply search on model-defined searchable columns. (this method in basebuilder in SearchTrait)
            ->search($model::getProp('columnsSearch'))

            // 5. Finally, order results by latest "id".
            ->latest('id');
    }

    
    #region ===================== End CRUD Methods =====================

    
}

