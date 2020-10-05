# Star Citizen Wiki API

The Star Citizen Wiki API serves as an interface between different Star Citizen services and the Star Citizen Wiki itself.

## Star Citizen

Endpoints related to Star Citizen

### Funding statistics related

Funding, Fleet and Fan statistics.

* [All](stats/get.md) : `GET /api/stats`
* [Latest](stats/latest.md) : `GET /api/stats/latest`

### Manufacturer related

Star Citizen vehicle manufacturers.

* [All](manufacturers/get.md) : `GET /api/manufacturers`
* [Single](manufacturers/show.md) : `GET /api/manufacturers/NAME`
* [Search](manufacturers/search.md) : `POST /api/manufacturers`

### Vehicle related

Star Citizen vehicles.

#### Ships
* [All](vehicles/ships/get.md) : `GET /api/ships`
* [Single](vehicles/ships/show.md) : `GET /api/ships/NAME`
* [Search](vehicles/ships/search.md) : `POST /api/ships/search`

#### Ground Vehicles
* [All](vehicles/ground_vehicles/get.md) : `GET /api/vehicles`
* [Single](vehicles/ground_vehicles/show.md) : `GET /api/vehicles/NAME`
* [Search](vehicles/ground_vehicles/search.md) : `POST /api/vehicles/search`

### Starmap related

Starsystems and celestial objects from the RSI Starmap.

#### Starsystems
* [All](starmap/starsystems/get.md) : `GET /api/starmap/starsystems`
* [Single](starmap/starsystems/show.md) : `GET /api/starmap/starsystems/CODE`

#### Celestial Objects
* [All](starmap/celestial_objects/get.md) : `GET /api/starmap/celestial-objects`
* [Single](starmap/celestial_objects/show.md) : `GET /api/starmap/celestial-objects/CODE`


## Roberts Space Industries

Endpoints related to the RSI Website

### Comm-Links

Parsed Comm-Links

* [All](comm_links/get.md) : `GET /api/comm-links`
* [Single](comm_links/show.md) : `GET /api/comm-links/ID`

#### Search

Comm-Link search. Comm-Links by title or reverse search by image.

* [Search by title](comm_links/search_title.md) : `POST /api/comm-links/search`
* [Search by image link](comm_links/search_image_link.md) : `POST /api/comm-links/reverse-image-link-search`
* [Search by image](comm_links/search_image.md) : `POST /api/comm-links/reverse-image-search`

#### Categories

Comm-Link Categories

* [All](comm_links/categories/get.md) : `GET /api/comm-links/categories`
* [Single](comm_links/categories/show.md) : `GET /api/comm-links/categories/CATEGORY`

#### Channels

Comm-Link Channels

* [All](comm_links/channels/get.md) : `GET /api/comm-links/channels`
* [Single](comm_links/channels/show.md) : `GET /api/comm-links/channels/CHANNEL`

#### Series

Comm-Link Series

* [All](comm_links/series/get.md) : `GET /api/comm-links/series`
* [Single](comm_links/series/show.md) : `GET /api/comm-links/series/SERIES`

## Global request options

Information about global request options

### Pagination
Many responses are paginated. A pagianted response contains a `pagination`-key in the metadata.  
Example:
```json
{
  "data": [],
  "meta": {
    "pagination": {
      "total": 2847,
      "count": 1,
      "per_page": 1,
      "current_page": 1,
      "total_pages": 2847,
      "links": {
        "previous": "PREV_PAGE",
        "next": "NEXT_PAGE"
      }
    }
  }
}
```

Pagination can be disabled by requesting an endpoint with the `limit` parameter set to `0`.  

### Relations
Some endpoints contain relations that include additional data.  
Available includes are listed under `valid_relations` in the metadata field.  
Example:
```json
{
  "data": [],
  "meta": {
    "valid_relations": [
      "ships",
      "vehicles"
    ]
  }
}
```

Relations can be included by passing the relation name in the `include` url parameter.  
Example: `https://api.star-citizen.wiki/api/manufacturers/RSI?include=ships`  
Multiple includes are separated by `,`.

Child relations in a parent can be included by adding the child relation with a dot.  
Example: `https://api.star-citizen.wiki/api/starmap/starsystems/SOL?include=celestial_objects.jumppoint`  
This call would include all celestial objects of the starsystem and the jumppoint associated with a celestial object.

### Localization
Some endpoints allow localization of the returned text.  
The locale can be set by setting the `locale` url parameter.  
Currently `de_DE` and `en_EN` are supported. 