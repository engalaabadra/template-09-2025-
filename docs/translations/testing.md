Test Strategy

Seed multiple translations for a single model.

Assert retrieval returns only current locale.

Assert translations are stored/updated correctly.

```
public function test_can_store_translations()
{
    $response = $this->postJson('/api/banners', [
        'lang' => 'ar',
        'title' => 'عنوان',
        'translations' => [
            [ 'lang' => 'en', 'title' => 'Title' ]
        ]
    ]);

    $this->assertDatabaseHas('banners', ['lang' => 'en', 'title' => 'Title']);
}
```

