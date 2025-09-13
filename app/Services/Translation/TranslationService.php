<?php

namespace App\Services\Translation;

use Illuminate\Database\Eloquent\Model;

class TranslationService
{
    /**
     * Create or update translations for a given model.
     *
     * - If a translation for (translate_id + lang) exists → update it.
     * - If not found → create a new translation.
     * - Ensures excluded (non-translatable) fields are copied from the main item.
     * - Excludes `id` from input so that the DB primary key remains unchanged.
     *
     * @param Model $model         The model class (e.g., Profile::class).
     * @param array $translations  Array of translations from request (lang + fields).
     * @param  $mainItem      The main saved model (default/original language).
     *
     * @return void
     */
    public function handleTranslations(Model $model,  $mainItem, array $translations): void
    {
        foreach ($translations as $data) {
            // Always link translations to the main/original item
            $data['translate_id'] = $mainItem->id;

            // Copy excluded (non-translatable) fields from the main item
            foreach ($model::getProp('excludedFields') as $field) {
                $data[$field] = $mainItem->{$field};
            }

            // Perform update if exists, otherwise create
            $mainItem->translations()->updateOrCreate(
                [
                    'translate_id' => $mainItem->id, // Base condition
                    'lang' => $data['lang']          // Translation language
                ],
                collect($data)->except('id')->toArray() // Exclude `id` if passed
            );
        }
    }
}
