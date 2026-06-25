<?php
namespace App\Repositories\Dashboard\Chat;

use App\Repositories\Eloquent\EloquentRepository;
use Illuminate\Support\Facades\Schema;

/**
 * ChatRepository
 *
 * This is a Chat Repository class implementing the ChatRepositoryInterface.
 * It provides methods such as : getData, show
 */
class ChatRepository extends EloquentRepository implements ChatRepositoryInterface
{
    #region ===================== Start CRUD Methods extends from EloquentRepository: getData($model), show($model, $id) =====================

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

            ->where(['user_id' => adminApi()?->id, 'client_id' => clientId()])
            ->orWhere(['client_id' => adminApi()?->id, 'user_id' => clientId()])
                
            // 3. Apply all request-based filters dynamically to the query (this method in basebuilder in FilterTrait)
            ->filter()

            // 4. Apply search on model-defined searchable columns. (this method in basebuilder in SearchTrait)
            ->search($model::getProp('columnsSearch'))

            // 5. Finally, order results by latest "id".
            ->latest('id');
    }
    
    #region ===================== End CRUD Methods =====================

}

