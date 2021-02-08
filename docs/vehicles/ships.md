FORMAT: 1A

# Star Citizen Wiki Api

# Ships [/ships]
Ship API
All Ships found in the official [Ship Matrix](https://robertsspaceindustries.com/ship-matrix).

## Index of all ships [GET /ships{?page,locale,include,limit}]


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
                        "id": 159,
                        "chassis_id": 63,
                        "name": "100i",
                        "slug": "100i",
                        "sizes": {
                            "length": 19.3,
                            "beam": 11,
                            "height": 4
                        },
                        "mass": 0,
                        "cargo_capacity": 2,
                        "crew": {
                            "min": 1,
                            "max": 1
                        },
                        "speed": {
                            "scm": 210,
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
                                "de_DE": "Einsteiger",
                                "en_EN": "Starter"
                            },
                            {
                                "de_DE": "Reisen",
                                "en_EN": "Touring"
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
                            "de_DE": "Mehrzweck",
                            "en_EN": "multi"
                        },
                        "description": {
                            "de_DE": "....",
                            "en_EN": "..."
                        },
                        "size": {
                            "de_DE": "Klein",
                            "en_EN": "small"
                        },
                        "manufacturer": {
                            "code": "ORIG",
                            "name": "Origin Jumpworks GmbH"
                        },
                        "updated_at": "2020-10-15T13:19:19.000000Z",
                        "missing_translations": []
                    },
                    {
                        "id": "..."
                    }
                ],
                "meta": {
                    "processed_at": "2020-12-08 20:27:04",
                    "valid_relations": [
                        "components"
                    ],
                    "pagination": {
                        "total": 154,
                        "count": 5,
                        "per_page": 5,
                        "current_page": 1,
                        "total_pages": 31,
                        "links": {
                            "next": "https:\/\/api.star-citizen.wiki\/api\/ships?page=2"
                        }
                    }
                }
            }

## Single ship
Output of a single ship by name or slug (e.g. 3001) [GET /ships/{NAME}{?locale,include}]


+ Parameters
    + NAME (string, required) - URL encoded Name or Slug
    + include (string, optional) - Relations to include. Valid relations are listed in the meta data
    + locale (string, optional) - Localization to use. Supported codes: 'de_DE', 'en_EN'

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "NAME": "100i"
            }

+ Response 200 (application/json)
    + Body

            {
                "data": {
                    "id": 159,
                    "chassis_id": 63,
                    "name": "100i",
                    "slug": "100i",
                    "sizes": {
                        "length": 19.3,
                        "beam": 11,
                        "height": 4
                    },
                    "mass": 0,
                    "cargo_capacity": 2,
                    "crew": {
                        "min": 1,
                        "max": 1
                    },
                    "speed": {
                        "scm": 210,
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
                            "de_DE": "Einsteiger",
                            "en_EN": "Starter"
                        },
                        {
                            "de_DE": "Reisen",
                            "en_EN": "Touring"
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
                        "de_DE": "Mehrzweck",
                        "en_EN": "multi"
                    },
                    "description": {
                        "de_DE": "...",
                        "en_EN": "..."
                    },
                    "size": {
                        "de_DE": "Klein",
                        "en_EN": "small"
                    },
                    "manufacturer": {
                        "code": "ORIG",
                        "name": "Origin Jumpworks GmbH"
                    },
                    "updated_at": "2020-10-15T13:19:19.000000Z",
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
                "NAME": "100i",
                "locale": "de_DE"
            }

+ Response 200 (application/json)
    + Body

            {
                "data": {
                    "id": 159,
                    "chassis_id": 63,
                    "name": "100i",
                    "slug": "100i",
                    "sizes": {
                        "length": 19.3,
                        "beam": 11,
                        "height": 4
                    },
                    "mass": 0,
                    "cargo_capacity": 2,
                    "crew": {
                        "min": 1,
                        "max": 1
                    },
                    "speed": {
                        "scm": 210,
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
                        "Einsteiger",
                        "Reisen"
                    ],
                    "production_status": "Flugbereit",
                    "production_note": "Keine",
                    "type": "Mehrzweck",
                    "description": "...",
                    "size": "Klein",
                    "manufacturer": {
                        "code": "ORIG",
                        "name": "Origin Jumpworks GmbH"
                    },
                    "updated_at": "2020-10-15T13:19:19.000000Z",
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
                "NAME": "100i",
                "locale": "de_DE",
                "include": "components"
            }

+ Response 200 (application/json)
    + Body

            {
                "data": {
                    "id": 159,
                    "chassis_id": 63,
                    "name": "100i",
                    "slug": "100i",
                    "sizes": {
                        "length": 19.3,
                        "beam": 11,
                        "height": 4
                    },
                    "mass": 0,
                    "cargo_capacity": 2,
                    "crew": {
                        "min": 1,
                        "max": 1
                    },
                    "speed": {
                        "scm": 210,
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
                        "Einsteiger",
                        "Reisen"
                    ],
                    "production_status": "Flugbereit",
                    "production_note": "Keine",
                    "type": "Mehrzweck",
                    "description": "...",
                    "size": "Klein",
                    "manufacturer": {
                        "code": "ORIG",
                        "name": "Origin Jumpworks GmbH"
                    },
                    "updated_at": "2020-10-15T13:19:19.000000Z",
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