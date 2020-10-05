# Show Manufacturers

Ship and Vehicle manufacturers

**URL** : `/api/manufacturers`

**Method** : `GET`

**Auth required** : NO

**Permissions required** : None

**Data constraints** : `{}`

## Success Response

**Code** : `200 OK`

**Content examples**

```json
{
  "data": [
    {
      "code": "RSI",
      "name": "Roberts Space Industries",
      "known_for": {
        "de_DE": "Die Aurora und die Constellation",
        "en_EN": "the Aurora and the Constellation"
      },
      "description": {
        "de_DE": "Roberts Space Industries, Begründer des Antriebs, der die Expansion der Menschheit in den Weltraum ankurbelte, baut eine breite Palette von Raumschiffen, die alle Bedürfnisse von der einfachen interstellaren Reise bis zur Tiefenerkundung an den äußeren Rändern der Galaxie abdecken. Der Slogan lautet \"Roberts Space Industries: Überbringer der Sterne seit 2075\".",
        "en_EN": "The original creators of the engine that kickstarted humanity’s expansion into space, Roberts Space Industries build a wide range of spaceships that serve all needs starting at basic interstellar travel to deep exploration on the outer edges of the galaxy. The tagline is “Roberts Space Industries: Delivering the Stars since 2075”"
      }
    },
    {
      "code": "ORIG",
      "name": "Origin Jumpworks GmbH",
      "known_for": {
        "de_DE": "Die 300i Serie",
        "en_EN": "the 300i series"
      },
      "description": {
        "de_DE": "Der BMW des Star Citizen Universums. Ihr Handwerk ist teurer, eleganter aussehende Statussymbole, vielleicht mehr als sie Wert sind? Sie bekommen Zahlen statt Namen: \"Origin 300i\", \"Origin 890 Jump\", \"Origin M50 Turbo\", etc.",
        "en_EN": "The BMW of the Star Citizen universe.  Their craft are more expensive, sleeker looking status symbols, maybe more so than they’re worth?  They get numbers instead of names: “Origin 300i,”\"Origin 890 Jump,” “Origin M50 Turbo,” etc."
      }
    },
    {
      "...": ""
    }
  ],
  "meta": {
    "processed_at": "2020-10-05 12:57:11",
    "valid_relations": [
      "ships",
      "vehicles"
    ],
    "pagination": {
      "total": 17,
      "count": 10,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 2,
      "links": {
        "next": "https:\/\/api.star-citizen.wiki\/api\/manufacturers?page=2"
      }
    }
  }
}
```
