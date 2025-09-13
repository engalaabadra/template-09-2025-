### Database Structure

Each translatable table (e.g., banners, users) includes two key columns:

***lang***: Represents the language of the record. Default is set to the app locale (localeLang()).

***translate_id***: References the ID of the original record (in the same table) for which this record is a translation. Null for the original language version.

Example Table Structure (banners)
```
Schema::create('banners', function (Blueprint $table) {
    $table->id();
    $table->string('lang')->default(deafultLang());
    $table->foreignId('translate_id')->nullable()->constrained('banners', 'id')->cascadeOnUpdate()->cascadeOnDelete();
    $table->string('title');
    $table->string('url')->nullable();
    $table->longText('description')->nullable();
    $table->tinyInteger('active')->default(1);
    $table->timestamps();
});
```