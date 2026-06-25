<?php
namespace App\Repositories\User\Category;

use App\Repositories\Eloquent\EloquentRepository;

/**
 * CategoryRepository
 *
 * This is a Category Repository class implementing the CategoryRepositoryInterface.
 * It provides methods such as : getData, show
 */
class CategoryRepository extends EloquentRepository implements CategoryRepositoryInterface
{
    // Add specific  Handling data methods here
     
    #region Constructor
     
    #endregion Constructor


    #region ===================== Start CRUD Methods extends from EloquentRepository: getData($model), show($model, $id) =====================

    #region ===================== End CRUD Methods =====================

    #region ===================== Start Special Methods =====================

     public function contentsCategory($model, $categoryId)
    {
       $category =  $this->baseRepo->findOrFailApi($categoryId, $model);
        return $category->contents;;
    }

    #endregion ===================== End Special Methods =====================
        
}
