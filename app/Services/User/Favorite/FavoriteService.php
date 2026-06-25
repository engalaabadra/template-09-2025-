<?php

namespace App\Services\User\Favorite;

use App\Services\Eloquent\EloquentService;
use App\Scopes\LanguageScope;

/**
 * Class FavoriteService
 *
 * This service handles business logic related to favorites.
 * Implements the FavoriteServiceInterface and provides functionality for:
 * - Storing a favorite (toggle: adds if not exists, removes if exists).
 *
 * @package App\Services\User\Favorite
 */
class FavoriteService extends EloquentService implements FavoriteServiceInterface
{
    // Add specific business logic methods here

    #region Constructor

    #endregion Constructor

    #region ===================== Start CRUD Methods: store($request, $model), update($request, $id, $model), forceDelete($id, $model), forceDeleteMany($request, $model) =====================

    /**
     * Store or toggle a favorite record for the authenticated user.
     *
     * If the favorite already exists for the given Content by the user, it deletes it (toggle off).
     * If it doesn't exist, it creates a new favorite (toggle on).
     *
     * @param StoreFavoriteRequest $request  The validated request containing favorite data.
     * @param Favorite             $model    The Favorite model instance.
     *
     * @return object                        The created or deleted favorite record.
     */
    public function store($request, $model)
    {

        // Retrieve validated data from the request
        $data = $request->validated();
        // Assign the authenticated user's ID to the data
        $data['user_id'] = userApi()->id;
        // Check if the user has already favorited this Content
        $favUserContent = $model->where([
            'user_id' => $data['user_id'],
            'content_id' => $data['content_id'],
        ])->first();

        // If favorite exists, delete it (toggle off)
        if ($favUserContent) {
            $favUserContent->delete();
            return $favUserContent;
        }
        // Otherwise, create a new favorite (toggle on)
        $item = $model->create($data);

        // Return the item with its eager-loaded relationships
        return $item->load($model->getProp('eagerLoading'));
    }
    #endregion ===================== End CRUD Methods =====================

    
    #region ===================== Start ACTIVATION Methods changeActivate($request, $id, $model), changeActivateMany($request, $model)=====================

    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start TRASH Methods: destroy($id, $model), destroyMany($request, $model), restore($request, $id, $model), restoreMany($request, $model) =====================

    #endregion ===================== End TRASH Methods =====================

    
    #region ===================== Start File Handling Methods uploadFile($request, $id, $model), uploadFiles($request, $id, $model), deleteFile($id, $model), deleteFiles($request, $id, $model)=====================

    #endregion ===================== End File Handling Methods =====================
    
    #region ===================== Start Protected & Private Methods =====================
    
    #endregion ===================== End Protected & Private Methods =====================

}
