FORMAT: 1A

# Star Citizen Wiki Api

# Vehicles [/vehicles]
Ground Vehicle API
All Vehicles found in the official [Ship Matrix](https://robertsspaceindustries.com/ship-matrix).

## Index of all ground vehicles [GET /vehicles{?page,locale,include,limit}]


+ Parameters
    + page (integer, optional) - Pagination page
        + Default: 1
    + include (string, optional) - Relations to include. Valid relations are listed in the meta data
    + locale (string, optional) - Localization to use. Supported codes: 'de_DE', 'en_EN'
    + limit (integer, optional) - Items per page, set to 0, to return all items
        + Default: 10

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
                    {
                        "id": "..."
                    }
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
                            "next": "https:\/\/api.star-citizen.wiki\/api\/vehicles?page=2"
                        }
                    }
                }
            }

## Single vehicle
Output of a single vehicle by vehicle name or slug (e.g. Cyclone) [GET /vehicles/{NAME}{?locale,include}]


+ Parameters
    + NAME (string, required) - URL encoded Name or Slug
    + include (string, optional) - Relations to include. Valid relations are listed in the meta data
    + locale (string, optional) - Localization to use. Supported codes: 'de_DE', 'en_EN'

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

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "NAME": "Cyclone",
                "locale": "de_DE"
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
                        "Erkundung",
                        "Aufklärung"
                    ],
                    "production_status": "Flugbereit",
                    "production_note": "Keine",
                    "type": "Gelände",
                    "description": "...",
                    "size": "Fahrzeug",
                    "manufacturer": {
                        "code": "TMBL",
                        "name": "Tumbril"
                    },
                    "updated_at": "2019-11-10T17:40:17.000000Z",
                    "missing_translations": []
                },
                "meta": {
                    "processed_at": "2020-12-08 20:29:47",
                    "valid_relations": [
                        "components"
                    ]
                }
            }

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "NAME": "Cyclone",
                "locale": "de_DE",
                "include": "components"
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
                        "Erkundung",
                        "Aufklärung"
                    ],
                    "production_status": "Flugbereit",
                    "production_note": "Keine",
                    "type": "Gelände",
                    "description": "...",
                    "size": "Fahrzeug",
                    "manufacturer": {
                        "code": "TMBL",
                        "name": "Tumbril"
                    },
                    "updated_at": "2019-11-10T17:40:17.000000Z",
                    "missing_translations": [],
                    "components": {
                        "data": [
                            {
                                "type": "radar",
                                "name": "Radar",
                                "mounts": 1,
                                "component_size": "S",
                                "category": "",
                                "size": "S",
                                "details": "",
                                "quantity": 1,
                                "manufacturer": "TBD",
                                "component_class": "RSIAvionic"
                            },
                            {
                                "type": "..."
                            }
                        ]
                    }
                },
                "meta": {
                    "processed_at": "2020-12-08 20:29:47",
                    "valid_relations": [
                        "components"
                    ]
                }
            }

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "NAME": "invalid"
            }

+ Response 404 (application/json)
    + Body

            {
                "message": "No Results for Query 'invalid'",
                "status_code": 404
            }