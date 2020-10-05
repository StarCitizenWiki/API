# Show Celestial Objects

Starmap celestial objects like Planets, Moons, Jumppoints, ...

**URL** : `/api/starmap/celestial-objects`

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
      "id": 2269,
      "code": "AYR'KA.JUMPPOINTS.INDRA",
      "system_id": 398,
      "celestial_object_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/celestial-objects\/AYR'KA.JUMPPOINTS.INDRA",
      "name": null,
      "type": "JUMPPOINT",
      "age": 0,
      "habitable": null,
      "fairchanceact": null,
      "appearance": "DEFAULT",
      "designation": "Ail'ka - Kyuk’ya (Indra)",
      "distance": 33,
      "latitude": -57,
      "longitude": 137.86,
      "axial_tilt": 0,
      "orbit_period": null,
      "info_url": null,
      "description": {
        "de_DE": "Jump Point",
        "en_EN": "Jump Point"
      },
      "sensor": {
        "population": 0,
        "economy": 0,
        "danger": 0
      },
      "size": 0,
      "parent_id": null,
      "time_modified": "2018-11-14 20:57:51",
      "affiliation": {
        "data": []
      },
      "subtype": {
        "data": []
      }
    }
  ],
  "meta": {
    "processed_at": "2020-10-05 13:30:23",
    "valid_relations": [
      "starsystem",
      "jumppoint"
    ],
    "pagination": {
      "total": 864,
      "count": 1,
      "per_page": 1,
      "current_page": 1,
      "total_pages": 864,
      "links": {
        "next": "https:\/\/api.star-citizen.wiki\/api\/starmap\/celestial-objects?page=2"
      }
    }
  }
}
```
