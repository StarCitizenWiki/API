# Star Citizen Wiki API

The Star Citizen Wiki API serves as an interface between different Star Citizen services and the Star Citizen Wiki itself.

## Star Citizen

Endpoints related to Star Citizen

### Funding statistics related

[Funding, Fleet and Fan statistics.](stats/stats.md)

* All : `GET /api/stats`
* Latest : `GET /api/stats/latest`

### Manufacturer related

[Star Citizen vehicle manufacturers.](manufacturers/manufacturers.md)

* All : `GET /api/manufacturers`
* Single : `GET /api/manufacturers/NAME`
* Search : `POST /api/manufacturers`

### Vehicle related

[Star Citizen ships.](vehicles/ships.md)
[Star Citizen vehicles.](vehicles/ground_vehicles.md)

#### Ships
* All : `GET /api/ships`
* Single : `GET /api/ships/NAME`
* Search : `POST /api/ships/search`

#### Ground Vehicles
* All : `GET /api/vehicles`
* Single : `GET /api/vehicles/NAME`
* Search : `POST /api/vehicles/search`

### Starmap related

[Starsystems](starmap/starsystems.md) and celestial objects from the RSI Starmap.

#### Starsystems
* All : `GET /api/starmap/starsystems`
* Single : `GET /api/starmap/starsystems/CODE`


## Roberts Space Industries

Endpoints related to the RSI Website

### Comm-Links

[Parsed Comm-Links](comm_links/comm_links.md)

* All : `GET /api/comm-links`
* Single : `GET /api/comm-links/ID`

#### Search

[Comm-Link search. Comm-Links by title or reverse search by image.](comm_links/comm_links_search.md)

* Search by title : `POST /api/comm-links/search`
* Search by image link : `POST /api/comm-links/reverse-image-link-search`
* Search by image : `POST /api/comm-links/reverse-image-search`

## Global request options

Information about global request options

### API Key
An API Key can be obtained by registering on the german [Star Citizen Wiki](https://star-citizen.wiki) and logging into the [API](https://api.star-citizen.wiki).  
The key should be send in the `Authorization` header, e.g.: `Authorization Bearer: KEY`.

### Pagination
Many responses are paginated. A paginated response contains a `pagination`-key in the metadata.  
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
