### Model Configuration ***TranslationRelationsTrait***

This trait provides **automatic locale-based filtering** and **helper methods** for Eloquent models that use `lang` and `translate_id` fields for translations.

which is in Each model using translation defines:
```
public static $translationFields = ['title', 'description'];
public static $excludedFields = ['url'];
```
Trait Usage
```
use App\Models\Traits\Relations\TranslationRelationsTrait;
```

## Features
- **Automatic Global Scope** → Queries automatically filter by the current app locale (`localeLang()`).
- **Identify Translations** → `isTranslation()` to check if a record is a translated version.
- **Relationships**:
  - `original()` → Get the original record for a translation.
  - `translations()` → Get all translations of a record.
- **Query Scopes**:
  - `scopeOriginals()` → Filter only original records.
  - `scopeInLang($locale)` → Fetch records in a specific language.

---

## Example Usage

```php
use App\Models\Post;
use App\Models\Traits\Relations\TranslationRelationsTrait;

class Post extends Model
{
    use TranslationRelationsTrait;
}

// Example: Fetch posts in current app locale (auto-applied)
$posts = Post::all();

// Example: Get translations of a post
$post = Post::find(1);
$translations = $post->translations;

// Example: Check if a post is a translation
if ($post->isTranslation()) {
    echo "This is a translated version.";
}

// Example: Get only original posts
$originalPosts = Post::originals()->get();

// Example: Fetch posts in French (ignores global scope)
$frenchPosts = Post::inLang('fr')->get();
```
