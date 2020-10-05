# Show Manufacturers

Ship and Vehicle manufacturers

**URL** : `/api/manufacturers/CODE`

**Method** : `GET`

**Auth required** : NO

**Permissions required** : None

**Data constraints**

Manufacturer code.

```json
{
    "CODE": "Unique manufacturer code like RSI or ORIG"
}
```

**Data example** All fields must be sent.

```json
{
    "CODE": "RSI"
}
```

## Success Response

**Code** : `200 OK`

**Content examples**

```json
{
  "data": {
    "code": "RSI",
    "name": "Roberts Space Industries",
    "known_for": {
      "de_DE": "Die Aurora und die Constellation",
      "en_EN": "the Aurora and the Constellation"
    },
    "description": {
      "de_DE": "Roberts Space Industries, Begründer des Antriebs, der die Expansion der Menschheit in den Weltraum ankurbelte, baut eine breite Palette von Raumschiffen, die alle Bedürfnisse von der einfachen interstellaren Reise bis zur Tiefenerkundung an den äußeren Rändern der Galaxie abdecken. Der Slogan lautet \"Roberts Space Industries: Überbringer der Sterne seit 2075\".",
      "en_EN": "The original creators of the engine that kickstarted humanity’s expansion into space, Roberts Space Industries build a wide range of spaceships that serve all needs starting at basic interstellar travel to deep exploration on the outer edges of the galaxy. The tagline is “Roberts Space Industries: Delivering the Stars since 2075”"
    },
    "missing_translations": []
  },
  "meta": {
    "processed_at": "2020-10-05 13:12:28",
    "valid_relations": [
      "ships",
      "vehicles"
    ]
  }
}
```

## Error Response

**Code** : `404 NOT FOUND`

**Content example**

```json
{
  "message": "No Results for Query 'QUERY'",
  "status_code": 404
}
``` 