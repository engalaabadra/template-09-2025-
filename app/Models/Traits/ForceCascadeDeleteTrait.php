<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Trait ForceCascadeDeleteTrait
 *
 * Automatically deletes related records when deleting a model.
 * Define `$forceCascadeDelete` in the model with relationship names to be deleted.
 *
 * Example:
 * ```php
 * class Post extends Model {
 *     use ForceCascadeDeleteTrait;
 *     protected array $forceCascadeDelete = ['comments', 'tags'];
 *
 *     public function comments() { return $this->hasMany(Comment::class); }
 *     public function tags() { return $this->belongsToMany(Tag::class); }
 * }
 *
 * $post->handleForceCascadeDelete($post); // deletes comments & detaches tags
 * ```
 */
trait ForceCascadeDeleteTrait
{
    /**
     * Execute force cascade deletes for defined relationships.
     *
     * @param Model $model
     * @return void
     */
    public function handleForceCascadeDelete(Model $model): void
    {
        // Skip if the model does not define cascade config
        if (!property_exists($model, 'forceCascadeDelete')) {
            return;
        }

        foreach ($model->forceCascadeDelete as $relation) {
            // Skip if relation method doesn't exist in model
            if (!method_exists($model, $relation)) {
                logger()->warning("[CascadeDelete] Skipped missing relation method: {$relation} on model " . get_class($model));
                continue;
            }

            /** @var Relation $related */
            $related = $model->$relation();

            if ($related instanceof \Illuminate\Database\Eloquent\Relations\HasMany ||
                $related instanceof \Illuminate\Database\Eloquent\Relations\MorphMany) {
                $related->get()->each->delete();

            } elseif ($related instanceof \Illuminate\Database\Eloquent\Relations\HasOne ||
                      $related instanceof \Illuminate\Database\Eloquent\Relations\MorphOne) {

                // Handle media if applicable
                if (in_array($relation, ['file', 'files', 'image', 'images'])) {
                    match ($relation) {
                        'file'   => $model->deleteSingleMedia('file'),
                        'files'  => $model->deleteAllMedia('files'),
                        'image'  => $model->deleteSingleMedia('image'),
                        'images' => $model->deleteAllMedia('images'),
                    };
                }

                $related->first()?->delete();

            } elseif ($related instanceof BelongsToMany) {
                $related->detach();
            }

            logger()->info("[CascadeDelete] Deleted relation '{$relation}' from model " . get_class($model));
        }
    }
}
