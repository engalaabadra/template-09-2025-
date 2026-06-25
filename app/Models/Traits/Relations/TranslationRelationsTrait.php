<?php

namespace App\Models\Traits\Relations;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait TranslationRelations
 *
 * Provides translation handling for models using `lang` and `translate_id` fields.
 * Automatically applies a global scope for the app's current locale,
 * and provides helpers for identifying original records, translations, and language-based queries.
 */
trait TranslationRelationsTrait
{
    /**
     * Check if the current record is a translation.
     *
     * Example:
     *   $post = Post::find(5);
     *   if ($post->isTranslation()) {
     *       echo "This post is a translated version.";
     *   }
     *
     * @return bool
     */
    public function isTranslation(): bool
    {
        // Translation records have a non-null translate_id
        return !is_null($this->translate_id);
    }

    /**
     * Define a relationship to the original record of this translation.
     *
     * Example:
     *   $translation = Post::find(5);
     *   $original = $translation->original; // fetch the original Post
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function original()
    {
        // A translation belongs to its original record
        return $this->belongsTo(static::class, 'translate_id');
    }

    /**
     * Define a relationship to fetch all translations for the current record.
     *
     * Example:
     *   $post = Post::find(1);
     *   $translations = $post->translations; // get all translated versions of this post
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        // A record can have many translations
        return $this->hasMany(static::class, 'translate_id', 'id')->withoutGlobalScopes();
    }

    /**
     * Query scope to filter only original records (not translations).
     *
     * Example:
     *   $originalPosts = Post::originals()->get();
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOriginals(Builder $query): Builder
    {
        // Filter records that are not translations
        return $query->whereNull('translate_id');
    }

    /**
     * Query scope to filter records by specific locale.
     *
     * Example:
     *   $englishPosts = Post::inLang('en')->get();
     *   $arabicPosts  = Post::inLang('ar')->get();
     *
     * @param Builder $query
     * @param string $locale
     * @return Builder
     */
    public function scopeInLang(Builder $query, string $locale): Builder
    {
        // Remove the global lang scope and apply a manual where condition
        return $query->withoutGlobalScope('lang')->where('lang', $locale);
    }
}
