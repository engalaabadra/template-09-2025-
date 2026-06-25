<?php
namespace App\Repositories\Base;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExcelExport;
use App\Services\ServiceResponse;
use App\Exceptions\ApiResponseException;
use App\Enums\ServiceResponseEnum;

/**
 * BaseRepository
 *
 * This is a Base Repository class implementing the BaseRepositoryInterface.
 * It provides methods for using in whole project such as : 
 */
class BaseRepository  implements BaseRepositoryInterface
{

    /**
     * Build and execute a dynamic query with filtering.
     *
     * Steps:
     * 1. Start with the base query.
     * 2. Call ->filter() to apply filters dynamically from the request.
     *    - this meth. in BaseBuilder in FilterTrait , call it when ->query()->filter() : which is i reached into it becuase this model builder extends BasBuilder
     *    - in this meth. filter() -> loop on $this->filters() : this filters in basebuilder & model builder in FilterTrai, as : new ActiveFilter(fn ($value) => $this->isActive($value)) ...
     *    - inside loop $value = request($filter->key) -> this ActiveFilter file contain $key = 'is_active'; , so will take this key 'is_active' , and get this key from req. if exist get it to continue basd on it
     *    - after get value this key 'is_active' from req , data_get($filter, 'callback')($value); -> this means , every filter contain callback , which is this line excute this callback
     *    - callback : $this->isActive($value)) -> excute it : $q->where('is_active', $active) , $active this value is_active key from req, like '1', means that get all is_active = 1
     *    - now filtered data (get only is_active = 1)
     * 3. Dynamic searching : Quick ID search using #123 format, Regular columns, Translatable fields (model::getProp('translationFields')), Nested relations of arbitrary depth(user.profile)
     * 4. Finally, return the processed result ready for use.
 
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

            // 3. Apply all request-based filters dynamically to the query (this method in basebuilder in FilterTrait)
            ->filter()

            // 4. Apply search on model-defined searchable columns. (this method in basebuilder in SearchTrait)
            ->search($model::getProp('columnsSearch'))

            // Handle trashed data request.
            // ->when(request()->boolean('only_trashed') && in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses($model)), fn($q) => $q->onlyTrashed())

            // 5. Finally, order results by latest "id".
            ->latest('id');
    }

    /**
     * Handle report request.
     */
    protected function handleReport($model, $filters)
    {
        $filters = request()->except(['report', 'page', 'export', 'only_trashed', 'report_types']);
        $types = request()->input('report_types');
        $types = is_array($types) && count($types) > 0 ? $types : ['default'];

        $result = [];
        foreach ($types as $type) {
            $result[$type] = $model::generateReport($model, $filters, $type);
        }

        return ['reports' => $result];
    }
 
    /**
     * Find a model by ID for API usage, with optional ownership check.
     *
     * @param int|string $id The primary key of the model to find.
     * @param \Illuminate\Database\Eloquent\Model $model The Eloquent model class to query.
     * @param  bool    $forUser  if false (show data that only has it this user)
     *
     * @return \Illuminate\Database\Eloquent\Model The found model instance.
     *
     * @throws \App\Exceptions\ApiResponseException If the model is not found or access is denied.
     */
    public function findOrFailApi($id, $model, $forUser = false)
    {
        // ✅ Handle if passed as class name (string)
        if (is_string($model) && class_exists($model)) {
            $model = new $model;
        }

        $query = $model::query();
        // $auth = $model->getAuthUser();
        // // If the user is Admin → bypass all restrictions
        // if ($auth?->hasRole('superadmin') || $auth?->hasRole('admin')) {
        //     return $query->find($id)
        //         ?? throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);

        // }

        // // check if this item for her
        // $hasUserId = Schema::hasColumn($model->getTable(), 'user_id');

        // if($auth && $hasUserId && $forUser){
        //     $query->OwnedByUser();
        // }
        $item = $query->find($id);
        return $item ?? throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);

    }

    /**
     * Find a model by ID (excluding trashed) or throw not found (not soft-deleted) -> using in activate & destroy(temporary deleting)
     *
     * @param  int|string  $id
     * @param  string      $model  Model class name.
     * @return \Illuminate\Database\Eloquent\Model
     * 
     *
     * @throws \App\Exceptions\ApiResponseException
     */
    public function findWithoutTrashedOrFail($id, $model, $forUser = false)
    {
        $user = $model->getAuthUser();

        $query = $model::query();

        // If the user is Admin → bypass all restrictions
        if ($user?->hasRole('superadmin') || $user?->hasRole('admin')) {
            return $query->withoutTrashed()->find($id)
                ?? throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);

        }
        // check if this item for her
        $hasUserId = Schema::hasColumn($model->getTable(), 'user_id');

        if($user && $hasUserId && $forUser){
            $query->OwnedByUser();
        }
        
        $item = $query->withoutTrashed()->find($id);

        return $item ?? throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);
    }


    /**
     * Find a soft-deleted model by ID or throw not found -> using in restore , force delete
     *
     * @param  int|string  $id
     * @param  string      $model
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \App\Exceptions\ApiResponseException
     */
    public function findOnlyTrashedOrFail(int $id, string|object $model, bool $forUser = false)
    {
        // If string => class name, if object => instance
        $modelInstance = is_string($model) ? new $model : $model;
        $modelClass    = is_string($model) ? $model : get_class($model);

        $query = $modelClass::query();
        $user  = getAuthUser();

        // check if this model has 'user_id' column
        $hasUserId = Schema::hasColumn($modelInstance->getTable(), 'user_id');

        if ($user && $hasUserId && $forUser) {
            $query->OwnedByUser();
        }

        $item = $query->onlyTrashed()->find($id);

        return $item ?? throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);
    }



    ////////////////////////////////////////////
    protected function findOrFail(object $row): Model|ServiceResponseEnum
    {
        $item = $this->model->find($id);
        return $item ?? ServiceResponseEnum::NOT_FOUND;
    }

    /**
     * Try deleting a record and execute an optional callback.
     *
     * @param object $row
     * @param \Closure|null $callback
     * @return bool
     */
    public function tryDelete(object $row, ?\Closure $callback = NULL): bool
    {
        return DB::transaction(function () use ($row, $callback) {
            if ($row->delete()) {
                if ($callback) {
                    $callback($row);
                }
                $this->makeSuccessSessionMessage();
                return true;
            }
            $this->makeErrorSessionMessage(__('message.cant_delete'));
            return false;
        });
    }

    /**
     * Try force deleting a record.
     *
     * @param object $row
     * @return bool
     */
    public function tryForceDelete(object $row): bool
    {
        return DB::transaction(function () use ($row) {
            if ($row->forceDelete()) {
                $this->makeSuccessSessionMessage();
                return true;
            }
            $this->makeErrorSessionMessage(__('message.cant_delete'));
            return false;
        });
    }

    /**
     * Check if the current request expects JSON.
     *
     * @return bool
     */
    public function requestExpectJson(): bool
    {
        return request()->expectsJson();
    }

    /**
     * Try deleting or force deleting a record by ID.
     *
     * @param string $model
     * @param int $id
     * @param bool $makeMessageSession
     * @return bool
     */
    public function tryDeleteForceDelete(string $model, int $id, bool $makeMessageSession = false): bool
    {
        return DB::transaction(function () use ($model, $id, $makeMessageSession) {
            $row = $model::withTrashed()->findOrFail($id);
            if ($row->forceDelete()) {
                if ($makeMessageSession) {
                    $this->makeSuccessSessionMessage();
                }
                return true;
            }
            if ($makeMessageSession) {
                $this->makeErrorSessionMessage(__('message.cant_delete'));
            }
            return false;
        });
    }

    /**
     * Try restoring a soft-deleted record by ID.
     *
     * @param string $model
     * @param int $id
     * @param bool $makeMessageSession
     * @return void
     */
    public function tryRestore(string $model, int $id, bool $makeMessageSession = false): void
    {
        DB::transaction(function () use ($model, $id, $makeMessageSession) {
            $row = $model::withTrashed()->findOrFail($id);
            $row->restore();
            $row->update(['deleted_by_id' => NULL]);
            if ($makeMessageSession) {
                $this->makeSuccessSessionMessage();
            }
        });
    }

    /**
     * Create a success session message.
     *
     * @param string|null $message
     * @return void
     */
    public function makeSuccessSessionMessage(?string $message = NULL): void
    {
        $this->createToaster('success', '', $message ?? __('service_responses.success'));
    }

    /**
     * Refresh the DOM by setting a session key.
     *
     * @return void
     */
    public function refreshDom(): void
    {
        Session::flash('refresh_dom_key', time());
    }

    public function flashShareData($data = []): void
    {
        Session::flash('el_flash_temp_data', $data);
    }

    /**
     * Create a toaster flash message.
     *
     * @param string $type
     * @param string $title
     * @param string $message
     * @return void
     */
    private function createToaster(string $type, string $title, string $message): void
    {
        if (!$this->requestExpectJson()) {
            Session::flash('toastr', [['type' => $type, 'title' => $title, 'message' => $message]]);
        }
    }

    /**
     * Create an error session message.
     *
     * @param string|null $message
     * @return void
     */
    public function makeErrorSessionMessage(?string $message = NULL): void
    {
        $this->createToaster('error', '', $message ?? __('message.error_response_message'));
    }

    /**
     * Export model data to an Excel file and either download it (web) or return a download URL (API).
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model  Fully qualified model class name.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|string
     *
     * - For **web requests**, triggers file download of the Excel export.
     * - For **API requests**, returns a public URL to the exported file.
     *
     * The exported file:
     * - Uses the model’s query to fetch all records.
     * - Uses the model’s `$columnsToExport` to determine which columns to include.
     * - Is named using the translated module name and current date.
     */

    public function exportToExcel($model, $query)
    {
        $data = $query->get();
       // $fileName = ModuleNameEnum::getTrans(ModuleNameEnum::modelNameUpperCase($model));
        $fileName = class_basename($model);
        $fileNameWithDate = $fileName . ' ' . Carbon::now()->toDateString() . '.xlsx';
        $filePath = 'exports/' . $fileNameWithDate;
        
        // store file in folder in storage
        if($model::getProp('columnsToExport')){
            Excel::store(new ExcelExport($data, $model::getProp('columnsToExport')), $filePath, 'public');
            return request()->expectsJson()
                ? url('storage/' . $filePath) // API: return download URL
                : Excel::download(new ExcelExport($data, $map), $fileNameWithDate); // Web: trigger file download
        }else{
            throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);
        }


    }



    public function addElFileCard($collection, $label, $archives = null, $el_file_card_type = 'archive_card'): array
    {
        return [
            'el_file_card_type' => $el_file_card_type,
            'collection' => $collection,
            'label' => $label,
            'archives' => $archives,
        ];
    }

    /**
     * Share transparency setting with Inertia.
     *
     * @param bool $transparent
     * @return void
     */
    public function useTransparent(bool $transparent = true): void
    {
        Inertia::share(['isTransparent' => $transparent]);
    }

    /**
     * Generate a statistic card.
     *
     * @param string|null $title
     * @param string|null $value
     * @param string|null $icon
     * @return array
     */
    public function makeStatisticCard(?string $title, ?string $value, string $icon = 'pi pi-chart-line', bool $is_price = false): array
    {
        return [
            'title' => $title,
            'value' => $value ?? 0,
            'icon' => $icon,
            'is_price' => $is_price,
        ];
    }
}
