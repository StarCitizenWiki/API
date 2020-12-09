FORMAT: 1A

# Star Citizen Wiki Api

# Manufacturers [/manufacturers]
Manufacturer API
Manufacturers found in the ShipMatrix

## Returns all manufacturers [GET /manufacturers{?page,limit,include,locale}]


+ Parameters
    + page (integer, optional) - Pagination page
        + Default: 1
    + include (string, optional) - Relations to include. Valid relations are listed in the meta data
    + limit (integer, optional) - Items per page, set to 0, to return all items
        + Default: 10
    + locale (string, optional) - Localization to use. Supported codes: 'de_DE', 'en_EN'

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json

+ Response 200 (application/json)
    + Body

            {
                "data": [
                    {
                        "code": "RSI",
                        "name": "Roberts Space Industries",
                        "known_for": {
                            "de_DE": "Die Aurora und die Constellation",
                            "en_EN": "the Aurora and the Constellation"
                        },
                        "description": {
                            "de_DE": "...",
                            "en_EN": "..."
                        }
                    },
                    {
                        "code": "ORIG",
                        "name": "Origin Jumpworks GmbH",
                        "known_for": {
                            "de_DE": "Die 300i Serie",
                            "en_EN": "the 300i series"
                        },
                        "description": {
                            "de_DE": "...",
                            "en_EN": "..."
                        }
                    }
                ],
                "meta": {
                    "processed_at": "2020-12-07 13:25:54",
                    "valid_relations": [
                        "ships",
                        "vehicles"
                    ],
                    "pagination": {
                        "total": 17,
                        "count": 10,
                        "per_page": 10,
                        "current_page": 1,
                        "total_pages": 2,
                        "links": {
                            "next": "https:\/\/api.star-citizen.wiki\/api\/manufacturers?page=2"
                        }
                    }
                }
            }

## Returns a single manufacturer [GET /manufacturers/{CODE}{?include,locale}]


+ Parameters
    + CODE (string, required) - Manufacturer Code
    + include (string, optional) - Relations to include. Valid relations are listed in the meta data
    + locale (string, optional) - Localization to use. Supported codes: 'de_DE', 'en_EN'

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json

+ Response 200 (application/json)
    + Body

            {
                "data": [
                    {
                        "code": "RSI",
                        "name": "Roberts Space Industries",
                        "known_for": {
                            "de_DE": "Die Aurora und die Constellation",
                            "en_EN": "the Aurora and the Constellation"
                        },
                        "description": {
                            "de_DE": "...",
                            "en_EN": "..."
                        }
                    }
                ],
                "meta": {
                    "processed_at": "2020-12-07 13:25:54",
                    "valid_relations": [
                        "ships",
                        "vehicles"
                    ]
                }
            }

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "locale": "de_DE"
            }

+ Response 200 (application/json)
    + Body

            {
                "data": [
                    {
                        "code": "RSI",
                        "name": "Roberts Space Industries",
                        "known_for": "Die Aurora und die Constellation",
                        "description": "..."
                    }
                ],
                "meta": {
                    "processed_at": "2020-12-07 13:25:54",
                    "valid_relations": [
                        "ships",
                        "vehicles"
                    ]
                }
            }

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "include": "ships"
            }

+ Response 200 (application/json)
    + Body

            {
                "data": [
                    {
                        "code": "RSI",
                        "name": "Roberts Space Industries",
                        "known_for": {
                            "de_DE": "Die Aurora und die Constellation",
                            "en_EN": "the Aurora and the Constellation"
                        },
                        "description": {
                            "de_DE": "...",
                            "en_EN": "..."
                        },
                        "ships": {
                            "data": [
                                {
                                    "name": "Orion",
                                    "slug": "orion",
                                    "api_url": "https:\/\/api.star-citizen.wiki\/api\/ships\/orion"
                                },
                                {
                                    "name": "Polaris",
                                    "slug": "polaris",
                                    "api_url": "https:\/\/api.star-citizen.wiki\/api\/ships\/polaris"
                                },
                                {
                                    "name": "..."
                                }
                            ]
                        }
                    }
                ],
                "meta": {
                    "processed_at": "2020-12-07 13:25:54",
                    "valid_relations": [
                        "ships",
                        "vehicles"
                    ]
                }
            }

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "include": "ships,vehicles"
            }

+ Response 200 (application/json)
    + Body

            {
                "data": [
                    {
                        "code": "RSI",
                        "name": "Roberts Space Industries",
                        "known_for": {
                            "de_DE": "Die Aurora und die Constellation",
                            "en_EN": "the Aurora and the Constellation"
                        },
                        "description": {
                            "de_DE": "...",
                            "en_EN": "..."
                        },
                        "ships": {
                            "data": [
                                {
                                    "name": "Orion",
                                    "slug": "orion",
                                    "api_url": "https:\/\/api.star-citizen.wiki\/api\/ships\/orion"
                                },
                                {
                                    "name": "Polaris",
                                    "slug": "polaris",
                                    "api_url": "https:\/\/api.star-citizen.wiki\/api\/ships\/polaris"
                                },
                                {
                                    "name": "..."
                                }
                            ]
                        },
                        "vehicles": {
                            "data": [
                                {
                                    "name": "Ursa Rover",
                                    "slug": "ursa-rover",
                                    "api_url": "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ursa-rover"
                                },
                                {
                                    "name": "..."
                                }
                            ]
                        }
                    }
                ],
                "meta": {
                    "processed_at": "2020-12-07 13:25:54",
                    "valid_relations": [
                        "ships",
                        "vehicles"
                    ]
                }
            }

## Search Endpoint [POST /manufacturers/search]


+ Parameters
    + query (string, required) - Manufacturer Code or partial name
    + include (string, optional) - Relations to include. Valid relations are listed in the meta data

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "query": "RSI"
            }

+ Response 200 (application/json)
    + Body

            {
                "data": [
                    {
                        "code": "RSI",
                        "name": "Roberts Space Industries",
                        "known_for": {
                            "de_DE": "Die Aurora und die Constellation",
                            "en_EN": "the Aurora and the Constellation"
                        },
                        "description": {
                            "de_DE": "...",
                            "en_EN": "..."
                        }
                    }
                ],
                "meta": {
                    "processed_at": "2020-12-07 13:25:54",
                    "valid_relations": [
                        "ships",
                        "vehicles"
                    ]
                }
            }

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "query": "INVALID"
            }

+ Response 404 (application/json)
    + Body

            {
                "message": "No Results for Query 'INVALID'",
                "status_code": 404
            }