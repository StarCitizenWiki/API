# Show Ships

Shipmatrix ships

**URL** : `/api/ships`

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
      "id": 27,
      "chassis_id": 14,
      "name": "Idris-M",
      "slug": "idris-m",
      "sizes": {
        "length": 242,
        "beam": 126,
        "height": 46
      },
      "mass": 37459548,
      "cargo_capacity": 831,
      "crew": {
        "min": 8,
        "max": 28
      },
      "speed": {
        "scm": 0,
        "afterburner": 0
      },
      "agility": {
        "pitch": 0,
        "yaw": 0,
        "roll": 0,
        "acceleration": {
          "x_axis": 0,
          "y_axis": 0,
          "z_axis": 0
        }
      },
      "foci": [
        {
          "de_DE": "Fregatte",
          "en_EN": "Frigate"
        }
      ],
      "production_status": {
        "de_DE": "In Produktion",
        "en_EN": "in-production"
      },
      "production_note": {
        "de_DE": "Wird derzeit für die Implementierung in das Spiel gebaut und getestet",
        "en_EN": "Currently being built and tested for implementation in-game"
      },
      "type": {
        "de_DE": "Gefecht",
        "en_EN": "combat"
      },
      "description": {
        "de_DE": "Größer als ein Bomber, aber kleiner als ein Schiff der Linie, nehmen Fregatten einen interessanten Platz im Pantheon der Kriegsschiffe ein. Während ihnen die schwere Rüstung und die Hauptwaffen eines Kreuzers fehlen, sind Fregatten manövrierfähiger und hoch konfigurierbar.",
        "en_EN": "Larger than a bomber but smaller than a ship of the line, frigates occupy an interesting space in the pantheon of warships. While they lack the heavy armor and the capital weaponry of a cruiser, frigates are more maneuverable and are highly configurable."
      },
      "size": {
        "de_DE": "Kapitalklasse",
        "en_EN": "capital"
      },
      "manufacturer": {
        "code": "AEGS",
        "name": "Aegis Dynamics"
      },
      "updated_at": "2017-10-20T12:45:17.000000Z",
      "missing_translations": []
    }
  ],
  "meta": {
    "processed_at": "2020-10-05 13:23:55",
    "valid_relations": [
      "components"
    ],
    "pagination": {
      "total": 152,
      "count": 1,
      "per_page": 1,
      "current_page": 1,
      "total_pages": 152,
      "links": {
        "next": "https:\/\/api.star-citizen.wiki\/api\/ships?page=2"
      }
    }
  }
}
```
