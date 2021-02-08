FORMAT: 1A

# Star Citizen Wiki Api

# Galactapedia [/galactapedia]
Galactapedia article API

## Returns all galactapedia articles [GET /galactapedia{?page,limit,include,locale}]


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
                        "id": "0rxrqgDP12",
                        "title": "Gammon Messer",
                        "slug": "gammon-messer",
                        "thumbnail": "...",
                        "type": "People",
                        "url": "https:\/\/robertsspaceindustries.com\/galactapedia\/article\/0rxrqgDP12-gammon-messer",
                        "api_url": "https:\/\/api.star-citizen.wiki\/api\/alactapedia\/0rxrqgDP12"
                    },
                    {
                        "id": "0KxqnXDpQ2",
                        "title": "Empire's Light Conversion Centers",
                        "slug": "empires-light-conversion-centers",
                        "thumbnail": "...",
                        "type": "PlanetMoonSpaceStationPlatform",
                        "url": "...",
                        "api_url": "https:\/\/api.star-citizen.wiki\/api\/galactapedia\/0KxqnXDpQ2"
                    }
                ],
                "meta": {
                    "processed_at": "2020-12-07 13:25:54",
                    "valid_relations": [
                        "categories",
                        "properties",
                        "tags",
                        "related_articles",
                        "english"
                    ],
                    "pagination": {
                        "total": 17,
                        "count": 10,
                        "per_page": 10,
                        "current_page": 1,
                        "total_pages": 2,
                        "links": {
                            "next": "https:\/\/api.star-citizen.wiki\/api\/galactapedia?page=2"
                        }
                    }
                }
            }

## Returns a single galactapedia article [GET /galactapedia/{ID}{?include,locale}]


+ Parameters
    + ID (string, required) - Galactapedia Article ID
    + include (string, optional) - Relations to include. Valid relations are listed in the meta data
    + locale (string, optional) - Localization to use. Supported codes: 'de_DE', 'en_EN'

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json

+ Response 200 (application/json)
    + Body

            {
                "data": {
                    "id": "0rxrqgDP12",
                    "title": "Gammon Messer",
                    "slug": "gammon-messer",
                    "thumbnail": "https:\/\/cig-galactapedia-prod.s3.amazonaws.com\/upload\/1b0d3793-0d81-4fe9-8d31-9483088e30a8",
                    "type": "People",
                    "url": "https:\/\/robertsspaceindustries.com\/galactapedia\/article\/0rxrqgDP12-gammon-messer",
                    "api_url": "https:\/\/api.star-citizen.wiki\/api\/galactapedia\/0rxrqgDP12",
                    "categories": {
                        "data": [
                            {
                                "id": "R6vNW9BjPa",
                                "name": "People"
                            },
                            {
                                "id": "R6vqrLdp2e",
                                "name": "Human"
                            }
                        ]
                    },
                    "properties": {
                        "data": [
                            {
                                "name": "classification",
                                "value": "Human"
                            },
                            {
                                "name": "affiliation",
                                "value": "United Empire of Earth"
                            },
                            {
                                "name": "born",
                                "value": "2629"
                            },
                            {
                                "name": "died",
                                "value": "2662"
                            }
                        ]
                    },
                    "tags": {
                        "data": [
                            {
                                "id": "bo1gxKa8xw",
                                "name": "gammon messer"
                            },
                            {
                                "id": "R56nykvABN",
                                "name": "messer dynasty"
                            },
                            {
                                "id": "VaZwQy6PX5",
                                "name": "messer era"
                            }
                        ]
                    },
                    "related_articles": {
                        "data": [
                            {
                                "id": "0KnA1D5nQM",
                                "title": "Astrid Messer VII",
                                "url": "https:\/\/robertsspaceindustries.com\/galactapedia\/article\/0KnA1D5nQM-astrid-messer-vii",
                                "api_url": "https:\/\/api.star-citizen.wiki\/api\/galactapedia\/0KnA1D5nQM"
                            },
                            {
                                "id": "0OaB8O6awO",
                                "title": "Corsen Messer V",
                                "url": "https:\/\/robertsspaceindustries.com\/galactapedia\/article\/0OaB8O6awO-corsen-messer-v",
                                "api_url": "https:\/\/api.star-citizen.wiki\/api\/galactapedia\/0OaB8O6awO"
                            },
                            {
                                "id": "bZwQBNmWrL",
                                "title": "Illyana Messer VI",
                                "url": "https:\/\/robertsspaceindustries.com\/galactapedia\/article\/bZwQBNmWrL-illyana-messer-vi",
                                "api_url": "https:\/\/api.star-citizen.wiki\/api\/galactapedia\/bZwQBNmWrL"
                            }
                        ]
                    },
                    "english": {
                        "data": {
                            "locale": "en_EN",
                            "translation": "..."
                        }
                    },
                    "meta": {
                        "processed_at": "2021-02-08 10:45:28",
                        "valid_relations": [
                            "categories",
                            "properties",
                            "tags",
                            "related_articles",
                            "english"
                        ]
                    }
                }
            }

## Search Endpoint [POST /galactapedia/search]


+ Parameters
    + query (string, required) - Article (partial) title or slug
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
                "data": {
                    "Like show": ""
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