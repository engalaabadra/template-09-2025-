<?php
namespace App\Repositories\User\Content;

use App\Repositories\Eloquent\EloquentRepository;
use App\Models\User;
use App\Repositories\Base\BaseRepository;

/**
 * ContentRepository
 *
 * This is a Content Repository class implementing the ContentRepositoryInterface.
 * It provides methods such as : getData
 */
class ContentRepository extends EloquentRepository implements ContentRepositoryInterface
{
    // Add specific  Handling data methods here

     #region Constructor
     
      /** @var BaseRepository */
    protected $baseRepo;
    
        /**
     * Constructor
     *
     * @param BaseRepository    $baseRepo
     */

    public function __construct(BaseRepository $baseRepo) {
        $this->baseRepo = $baseRepo;
    }
    #endregion Constructor

    #region ===================== Start CRUD Methods extends from EloquentRepository: getData($model), show($model, $id) =====================

    #region ===================== End CRUD Methods =====================
    

    #region ===================== Start Special Methods =====================

     public function relatedContents($model, $contentId)
    {
       $content =  $this->baseRepo->findOrFailApi($contentId, $model);
        return $this->baseRepo->buildBaseQuery($model)->where('category_id', $content->category_id)
                      ->where('id', '!=', $content->id)
                      ->get();
    }

    public function nextContents($model, $contentId)
    {
        $content =  $this->baseRepo->findOrFailApi($contentId, $model);
        return $this->baseRepo->buildBaseQuery($model)->where('id', '>', $content->id)
                      ->orderBy('id')
                      ->get();
    }

    public function editionsContents($model, $contentId)
    {
        $content =  $this->baseRepo->findOrFailApi($contentId, $model);
        return $this->baseRepo->buildBaseQuery($model)->where('parent_content_id', $contentId)->get();
    }

    public function featuredContents($model, $contentId)
    {
        $content =  $this->baseRepo->findOrFailApi($contentId, $model);
        return $this->baseRepo->buildBaseQuery($model)->orderByDesc('reads_count')
            ->orWhere('is_featured', true)->get();
    }

    public function latestContents($model, $contentId)
    {
        $content =  $this->baseRepo->findOrFailApi($contentId, $model);
        return $this->baseRepo->buildBaseQuery($model)->latest()->take(10)->get();
    }

    public function popularContents($model, $contentId)
    {
        $content =  $this->baseRepo->findOrFailApi($contentId, $model);
        return $this->baseRepo->buildBaseQuery($model)->withCount('searches')
                        ->orderByDesc('searches_count')->get();
    }

    /**
     * User saved contents.
     */
    public function mySaved($model)
    {
        $user = userApi();

        $content =  $this->baseRepo->findOrFailApi($contentId, $model);
        return $this->baseRepo->buildBaseQuery($model)
                        ->whereIn('id', $user->savedContents()->pluck('id'))
                        ->get();
    }

    /**
     * User read contents.
     */
    public function myReads($model)
    {
        $user = userApi();

        $content =  $this->baseRepo->findOrFailApi($contentId, $model);
        return $this->baseRepo->buildBaseQuery($model)
                        ->whereIn('id', $user->readContents()->pluck('id'))
                        ->get();
        
    }


    #endregion ===================== End Special Methods =====================


}



