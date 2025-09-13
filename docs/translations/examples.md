***Store Request Example***
```
{
  "lang": "ar",
  "title": "عنوان رئيسي",
  "url": "https://example.com",
  "translations": [
    {
      "lang": "en",
      "title": "Main Title"
    },
    {
      "lang": "fr",
      "title": "Titre Principal"
    }
  ]
}
```
***Update Request Example***
for id : 1
```
{
  "lang": "ar",
  "title": "عنوان رئيسي",
  "url": "https://example.com",
  "translations": [
    {
      translate_id: 1,
      "lang": "en",

      "title": "Main Title"
    },
    {
      translate_id: 1,
      "lang": "fr",
      "title": "Titre Principal"
    }
  ]
}
```

```
id lang translate_id title

1   ar       NULL  "عنوان رئيسي"

2   en      1       "Main Title"

3  fr      1        "Titre Principal"
```
