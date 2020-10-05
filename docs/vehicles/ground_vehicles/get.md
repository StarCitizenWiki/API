# Show Ground Vehicles

Shipmatrix ground vehicles

**URL** : `/api/vehicles`

**Method** : `GET`

**Auth required** : NO

**Permissions required** : None

## Success Response

**Code** : `200 OK`

**Content examples**

```json
{
  "data": [
    {
      "id": 134,
      "chassis_id": 53,
      "name": "Cyclone",
      "slug": "cyclone",
      "sizes": {
        "length": 6,
        "beam": 4,
        "height": 2.5
      },
      "mass": 3022,
      "cargo_capacity": 1,
      "crew": {
        "min": 1,
        "max": 2
      },
      "speed": {
        "scm": 0
      },
      "foci": [
        {
          "de_DE": "Erkundung",
          "en_EN": "Exploration"
        },
        {
          "de_DE": "Aufklärung",
          "en_EN": "Recon"
        }
      ],
      "production_status": {
        "de_DE": "Flugbereit",
        "en_EN": "flight-ready"
      },
      "production_note": {
        "de_DE": "Keine",
        "en_EN": "None"
      },
      "type": {
        "de_DE": "Gelände",
        "en_EN": "ground"
      },
      "description": {
        "de_DE": "Mit einer starken Kombination aus Geschwindigkeit, Manövrierfähigkeit und robuster Langlebigkeit ist die Cyclone die perfekte Wahl für lokale Lieferungen und Transporte zwischen Heimatstationen auf Planeten und Außenposten.",
        "en_EN": "With a potent combination of speed, maneuverability, and rugged durability, the Cyclone is a perfect choice for local deliveries and transport between planetside homesteads and outposts."
      },
      "size": {
        "de_DE": "Fahrzeug",
        "en_EN": "vehicle"
      },
      "manufacturer": {
        "code": "TMBL",
        "name": "Tumbril"
      },
      "updated_at": "2019-11-10T17:40:17.000000Z",
      "missing_translations": []
    }
  ],
  "meta": {
    "processed_at": "2020-10-05 13:25:24",
    "valid_relations": [
      "components"
    ],
    "pagination": {
      "total": 17,
      "count": 1,
      "per_page": 1,
      "current_page": 1,
      "total_pages": 17,
      "links": {
        "next": "https:\/\/api.star-citizen.wiki\/api\/vehicles?page=2"
      }
    }
  }
}
```
