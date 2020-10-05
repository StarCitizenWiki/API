# Show Starsystems

Starmap Starsystems

**URL** : `/api/starmap/starsystems`

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
      "id": 398,
      "code": "AYR'KA",
      "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/AYR'KA",
      "name": "Ail'ka",
      "status": "P",
      "type": "SINGLE_STAR",
      "position": {
        "x": 139.16,
        "y": -7.99,
        "z": 39.58
      },
      "frost_line": 179.1,
      "habitable_zone_inner": 34.37,
      "habitable_zone_outer": 174.7,
      "info_url": null,
      "description": {
        "de_DE": "Primarily a military system focused on supporting, housing, and training Xi’an ground troops. Its close proximity to the Perry Line made it strategically important during the Human \/ Xi’an cold war. Once relations normalized, Xi’an forces from the Perry Line systems withdrew to here.",
        "en_EN": "Primarily a military system focused on supporting, housing, and training Xi’an ground troops. Its close proximity to the Perry Line made it strategically important during the Human \/ Xi’an cold war. Once relations normalized, Xi’an forces from the Perry Line systems withdrew to here."
      },
      "aggregated": {
        "size": 198.97,
        "population": 8.8,
        "economy": 3.95,
        "danger": 0
      },
      "time_modified": "2018-11-14 19:51:33",
      "affiliation": {
        "data": [
          {
            "id": 4,
            "name": "Xi'An",
            "code": "XIAN",
            "color": "#52c231"
          }
        ]
      }
    }
  ],
  "meta": {
    "processed_at": "2020-10-05 13:29:56",
    "valid_relations": [
      "jumppoints",
      "celestial_objects"
    ],
    "pagination": {
      "total": 90,
      "count": 1,
      "per_page": 1,
      "current_page": 1,
      "total_pages": 90,
      "links": {
        "next": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems?page=2"
      }
    }
  }
}
```
