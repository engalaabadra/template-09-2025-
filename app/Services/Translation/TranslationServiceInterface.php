<?php
namespace App\Services\Translation;

interface TranslationServiceInterface{
     
    public function createTranslations( $model, $translations, $newItem);
    public function updateTranslations($model, $translations, $newItem);
    public function handleTranslations(object $model, object $mainItem, array|string $translations, string $type);

}