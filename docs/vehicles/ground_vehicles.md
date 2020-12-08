FORMAT: 1A

# Star Citizen Wiki Api

# Vehicles [/vehicles]
Ground Vehicle API
Output of the ground vehicles of the Ship Matrix

## All ground vehicles
Output of all ground vehicles of the Ship Matrix paginated [GET /vehicles]


+ Parameters
    + page (integer, optional) - Pagination page
        + Default: 1
    + include (string, optional) - Relations to include. Valid relations are shown in the meta data

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json

+ Response 200 (application/json)
    + Body

            {
                "data": [
                    {
                        "id": 183,
                        "chassis_id": 75,
                        "name": "Anvil Ballista ",
                        "slug": "anvil-ballista",
                        "sizes": {
                            "length": 17,
                            "beam": 7,
                            "height": 5.5
                        },
                        "mass": 0,
                        "cargo_capacity": 0,
                        "crew": {
                            "min": 1,
                            "max": 2
                        },
                        "speed": {
                            "scm": 33
                        },
                        "foci": [
                            {
                                "de_DE": "Militär",
                                "en_EN": "Military"
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
                            "de_DE": "Gefecht",
                            "en_EN": "combat"
                        },
                        "description": [],
                        "size": {
                            "de_DE": "Fahrzeug",
                            "en_EN": "vehicle"
                        },
                        "manufacturer": {
                            "code": "ANVL",
                            "name": "Anvil Aerospace"
                        },
                        "updated_at": "2020-11-20T00:49:52.000000Z",
                        "missing_translations": [
                            "de_DE",
                            "anvil-ballista"
                        ]
                    },
                    [
                        "..."
                    ]
                ],
                "meta": {
                    "processed_at": "2020-12-08 20:32:51",
                    "valid_relations": [
                        "components"
                    ],
                    "pagination": {
                        "total": 17,
                        "count": 5,
                        "per_page": 5,
                        "current_page": 1,
                        "total_pages": 4,
                        "links": {
                            "next": "http:\/\/localhost:8000\/api\/vehicles?page=2"
                        }
                    }
                }
            }

## Single ground vehicle
Output of a single ground vehicle by vehicle name (e.g. Cyclone)
Name of ground vehicle should be URL encoded [GET /vehicles/{NAME}]


+ Parameters
    + NAME (string, required) - Vehicle Name or Slug
    + include (string, optional) - Relations to include. Valid relations are shown in the meta data

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "NAME": "Cyclone"
            }

+ Response 200 (application/json)
    + Body

            {
                "data": {
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
                        "de_DE": "...",
                        "en_EN": "..."
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
                },
                "meta": {
                    "processed_at": "2020-12-08 20:31:53",
                    "valid_relations": [
                        "components"
                    ]
                }
            }