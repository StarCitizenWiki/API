FORMAT: 1A

# Star Citizen Wiki Api

# Ships [/ships]
Spaceship API
Output of the spaceships of the Ship Matrix

## All spaceships
Output of all spaceships of the Ship Matrix paginated [GET /ships]


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
                            "de_DE": "Tour durch das Universum mit der perfekten Verbindung von Luxus und Leistung. Die 100i verfügt über das patentierte AIR-Kraftstoffsystem von Origin Jumpworks und ist damit das effizienteste und umweltfreundlichste Schiff auf dem Markt. Die 100i ist für Langstreckenflüge geeignet, für die die meisten Schiffe ihrer Größe nicht gerüstet sind, und sie ist perfekt für Solopiloten, die auf sich aufmerksam machen wollen, ohne auf Funktionalität oder Zuverlässigkeit zu verzichten.",
                            "en_EN": "Tour the universe with the perfect coupling of luxury and performance. The 100i features Origin Jumpworks' patented AIR fuel system, making it the most efficient and eco-friendly ship on the market. Capable of long distance flights that most ships of its size aren't equipped for, the 100i is perfect for solo pilots looking to turn heads without sacrificing functionality or reliability."
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
                    [
                        "..."
                    ]
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
                            "next": "http:\/\/localhost:8000\/api\/ships?page=2"
                        }
                    }
                }
            }

## Single spaceship
Output of a single spaceship by ship name (e.g. 300i)
Name of the ship should be URL encoded [GET /ships/{NAME}]


+ Parameters
    + NAME (string, required) - Ship Name or Slug
    + include (string, optional) - Relations to include. Valid relations are shown in the meta data

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