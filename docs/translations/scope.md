***LanguageScope***

Automatically applies a global scope to restrict query results to the current locale.

```
public function apply(Builder $builder, Model $model)
{
    $builder->where('lang', localeLang());
}
```
Can be bypassed using:
```
Model::withoutGlobalScope(LanguageScope::class)
```
