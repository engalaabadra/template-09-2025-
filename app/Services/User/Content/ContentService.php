<?php
namespace App\Services\User\Content;

use App\Services\Eloquent\EloquentService;
use App\Repositories\User\Content\ContentRepository;
use App\Services\Translation\TranslationService;

/**
 * ContentService class handles all business logic related to banners in the dashboard.
 * 
 * This class extends the generic EloquentService and implements ContentServiceInterface
 * to ensure contract compliance and reuse of common service logic.
 * 
 * @package App\Services\User\Content
 * 
 */
class ContentService extends EloquentService implements ContentServiceInterface
{

    
    // Add specific business logic methods here

    #region Constructor
     /** @var ContentRepository */
    protected $contentRepo;

     /** @var TranslationService */
    protected $translationService;
    /**
     * Constructor
     *
     * @param ContentRepository    $contentRepo
     * @param TranslationService    $translationService
     * 
     */

    public function __construct(ContentRepository $contentRepo, TranslationService $translationService) {
        parent::__construct($contentRepo, $translationService);

        $this->contentRepo = $contentRepo;

    }
    #endregion Constructor

    #region ===================== Start CRUD Methods: store($request, $model), update($request, $id, $model), forceDelete($id, $model), forceDeleteMany($request, $model) =====================

      /**
     * Store a new record.
     *
     * @param object $request  The request object containing validated data.
     * @param object $model    The model to query.
     * @return object          Created record with optional eager loading.
     */
    protected function store($request, $model)
    {

        // Get validated data and filter out 'file' and 'image'
        $data = $request->validated();

        $enteredData = $model::onlyFillable($data); // Filter given data and return only fillable fields of the model(excluded: 'files')

        $user = userApi();
        $enteredData['user_id'] = $user->id;
        $enteredData['published_at'] = now();

        // Create the new model record
        $newItem = $model->create($enteredData);

        //will be is_author=1 (if 0)this user -> become author
        if($user->is_author == 0) $user->update(['is_author' => 1]);

        // Refresh the model if 'is_active' was not part of the input
        refreshIfMissing($enteredData, $newItem);

        // Handle translations
        if ($request->filled('translations')) {
            $this->translationService->handleTranslations($model, $newItem, $request->get('translations'));
        }

        //Handle Files
        $newItem->handleFiles($request, $model, $newItem);//exist in HasMediaTrait

        // Load related models if eager loading is defined
        $data = $model->getProp('eagerLoading') ? $newItem->load($model->getProp('eagerLoading')) : $newItem;

        return $data;

    }  

    #region ===================== Start TRASH Methods: destroy($id, $model), destroyMany($request, $model), restore($request, $id, $model), restoreMany($request, $model) =====================

    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate($request, $id, $model), changeActivateMany($request, $model)=====================


    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start Special Methods =====================

    /**
     * Toggle activation status for a record.
     *
     * @param int    $id     The ID of the record to toggle activation.
     * @param object $model  The model to query.
     * @return object        Updated record with toggled activation status.
     */
    public function addToMySaved($request, $id, $model, $forUser = false )
    {
        $user = userApi();
        // Find the record without elements trash
        $item = $this->baseRepo->findWithoutTrashedOrFail($id, $model, $forUser); // find item only in table not in trash to save it

        // Get the action to perform: 'save', 'unsave', or 'toggle' (default: toggle)
        $action = $request->input('action', SavedActionEnum::TOGGLE->value);

        // If the action is "toggle"
        if ($action == SavedActionEnum::TOGGLE->value)
        {
            // Toggle the saved state: if the book is already saved, remove it; if not, save it
            $user->savedBooks()->toggle($id);
        } 
        // If the action is "save"
        elseif ($action == SavedActionEnum::SAVE->value)
        {
            // Add the book to saved items (attach inserts into the pivot table)
            $user->savedBooks()->attach($id);
        } 
        // If the action is "unsave"
        elseif ($action == SavedActionEnum::UNSAVE->value)
        {
            // Remove the book from saved items (detach removes from the pivot table)
            $user->savedBooks()->detach($id);
        }

        // Load related models if eager loading is defined
        $data = $model->getProp('eagerLoading') ? $item->load($model->getProp('eagerLoading')) : $item;

        return $data;
        
    }

    /**
     * Activate multiple records.
     *
     * @param object $model  The model to query.
     * @return array         Activated records.
     */
    public function addToMySavedMany($request, $model )
    {
        return $this->handleBulkSaveContent($request, $model, 'activate' );
    }

    #endregion ===================== End Special Methods =====================

    #region ===================== Start File Handling Methods =====================

 
    #endregion ===================== End File Handling Methods =====================
   
    #region ===================== Start Protected & Private Methods =====================
    
    /**
     * Handle bulk activation, deactivation, or toggling of users.
     *
     * This method supports two input formats for IDs:
     * - A specific list of IDs (array or JSON string)
     * - The string "all" to apply the action to all users
     *
     * It also detects and returns:
     * - IDs that failed during update
     * - IDs that were not found in the database
     *
     * @param \Illuminate\Database\Eloquent\Model $model The User model instance
     * @return \App\GeneralClasses\ServiceResponse
     */
    protected function handleBulkSaveContent($request, $model )
    {
        $user = apiUser();

        // Get the validated 'ids' input from the request
        $inputIds = $request->input('ids', []);

        // Check if the operation applies to all items
        $isAll = $inputIds === 'all';

        // Get the action to perform: 'save', 'unsave', or 'toggle' (default: toggle)
        $action = $request->input('action', SavedActionEnum::TOGGLE->value);

        // Base query excluding soft-deleted records
        $query = $model;

        // Fetch items based on whether 'all' is selected or specific IDs are provided
        $items = $this->fetchItemsByIdsOrAll($query, $isAll, $inputIds);

        // Initialize result arrays
        $processedIds  = []; // Successfully updated
        $failedIds     = []; // Failed to update due to errors
        $notFoundIds   = []; // IDs that were not found in DB

        // Process each item in the collection
        foreach ($items as $item) {
            try {
                // If the action is "toggle"
                if ($action == SavedActionEnum::TOGGLE->value)
                {
                    // Toggle the saved state: if the book is already saved, remove it; if not, save it
                    $user->savedBooks()->toggle($item->id);
                } 
                // If the action is "save"
                elseif ($action == SavedActionEnum::SAVE->value)
                {
                    // Add the book to saved items (attach inserts into the pivot table)
                    $user->savedBooks()->attach($item->id);
                } 
                // If the action is "unsave"
                elseif ($action == SavedActionEnum::UNSAVE->value)
                {
                    // Remove the book from saved items (detach removes from the pivot table)
                    $user->savedBooks()->detach($item->id);
                }

                $processedIds[] = $item->id;

            } catch (\Throwable $e) {
                // Something went wrong updating this item
                $failedIds[] = $item->id;
            }
        }

        // Determine which requested IDs were not found in the database
        $notFoundIds = $isAll ? [] : array_values(array_diff($ids, $processedIds));

        $data = [
            'processed_ids'   => $processedIds,
            'failed_ids'      => array_values($failedIds),
            'not_found_ids'   => array_values($notFoundIds),
        ];

        return $data;
        
    }

    #endregion ===================== End Protected & Private Methods =====================

    
}

