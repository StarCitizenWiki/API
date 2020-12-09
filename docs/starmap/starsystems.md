FORMAT: 1A

# Star Citizen Wiki Api

# Starsystems [/starmap/starsystems]
Star System API
Systems from the official [Starmap](https://robertsspaceindustries.com/starmap).

## Index of all available Star Systems [GET /starmap/starsystems{?page,locale,include,limit}]


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
                        "id": 398,
                        "code": "AYR'KA",
                        "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/AYR'KA",
                        "name": "Ail'ka",
                        "status": "P",
                        "type": "SINGLE_STAR",
                        "position": {
                            "x": 139.16,
                            "y": -7.99,
                            "z": 39.58
                        },
                        "frost_line": 179.1,
                        "habitable_zone_inner": 34.37,
                        "habitable_zone_outer": 174.7,
                        "info_url": null,
                        "description": {
                            "de_DE": "...",
                            "en_EN": "..."
                        },
                        "aggregated": {
                            "size": 198.97,
                            "population": 8.8,
                            "economy": 3.95,
                            "danger": 0
                        },
                        "updated_at": "2020-10-15T13:19:19.000000Z",
                        "affiliation": {
                            "data": [
                                {
                                    "id": 4,
                                    "name": "Xi'An",
                                    "code": "XIAN",
                                    "color": "#52c231"
                                }
                            ]
                        }
                    },
                    {
                        "id": "..."
                    }
                ],
                "meta": {
                    "processed_at": "2020-12-08 20:37:11",
                    "valid_relations": [
                        "jumppoints",
                        "celestial_objects"
                    ],
                    "pagination": {
                        "total": 90,
                        "count": 15,
                        "per_page": 15,
                        "current_page": 1,
                        "total_pages": 6,
                        "links": {
                            "next": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems?page=2"
                        }
                    }
                }
            }

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "include": "jumppoints"
            }

+ Response 200 (application/json)
    + Body

            {
                "data": [
                    {
                        "id": 398,
                        "code": "AYR'KA",
                        "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/AYR'KA",
                        "name": "Ail'ka",
                        "status": "P",
                        "type": "SINGLE_STAR",
                        "position": {
                            "x": 139.16,
                            "y": -7.99,
                            "z": 39.58
                        },
                        "frost_line": 179.1,
                        "habitable_zone_inner": 34.37,
                        "habitable_zone_outer": 174.7,
                        "info_url": null,
                        "description": {
                            "de_DE": "...",
                            "en_EN": "..."
                        },
                        "aggregated": {
                            "size": 198.97,
                            "population": 8.8,
                            "economy": 3.95,
                            "danger": 0
                        },
                        "updated_at": "2020-10-15T13:19:19.000000Z",
                        "affiliation": {
                            "data": [
                                {
                                    "id": 4,
                                    "name": "Xi'An",
                                    "code": "XIAN",
                                    "color": "#52c231"
                                }
                            ]
                        },
                        "jumppoints": {
                            "data": [
                                {
                                    "id": 1341,
                                    "size": "L",
                                    "direction": "B",
                                    "entry": {
                                        "id": 2175,
                                        "system_id": 377,
                                        "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/377",
                                        "celestial_object_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/celestial-objects\/HADUR.JUMPPOINTS.AYR'KA",
                                        "status": "P",
                                        "code": "HADUR.JUMPPOINTS.AYR'KA",
                                        "designation": "Yā’mon (Hadur) - Ail'ka"
                                    },
                                    "exit": {
                                        "id": 2270,
                                        "system_id": 398,
                                        "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/398",
                                        "celestial_object_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/celestial-objects\/AYR'KA.JUMPPOINTS.HADUR",
                                        "status": "P",
                                        "code": "AYR'KA.JUMPPOINTS.HADUR",
                                        "designation": "Ail'ka - Yā’mon (Hadur)"
                                    }
                                },
                                {
                                    "id": 1378,
                                    "size": "L",
                                    "direction": "B",
                                    "entry": {
                                        "id": 2269,
                                        "system_id": 398,
                                        "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/398",
                                        "celestial_object_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/celestial-objects\/AYR'KA.JUMPPOINTS.INDRA",
                                        "status": "P",
                                        "code": "AYR'KA.JUMPPOINTS.INDRA",
                                        "designation": "Ail'ka - Kyuk’ya (Indra)"
                                    },
                                    "exit": {
                                        "id": 2274,
                                        "system_id": 399,
                                        "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/399",
                                        "celestial_object_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/celestial-objects\/INDRA.JUMPPOINTS.AYR'KA",
                                        "status": "P",
                                        "code": "INDRA.JUMPPOINTS.AYR'KA",
                                        "designation": "Kyuk’ya (Indra) - Ail'ka"
                                    }
                                }
                            ]
                        }
                    },
                    {
                        "id": "..."
                    }
                ],
                "meta": {
                    "processed_at": "2020-12-08 20:37:11",
                    "valid_relations": [
                        "jumppoints",
                        "celestial_objects"
                    ],
                    "pagination": {
                        "total": 90,
                        "count": 15,
                        "per_page": 15,
                        "current_page": 1,
                        "total_pages": 6,
                        "links": {
                            "next": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems?page=2"
                        }
                    }
                }
            }

## A singular Star System [GET /starmap/starsystems/{CODE}{?locale,include}]


+ Parameters
    + CODE (string, required) - Star System Code or ID
    + include (string, optional) - Relations to include. Valid relations are listed in the meta data
    + locale (string, optional) - Localization to use. Supported codes: 'de_DE', 'en_EN'

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "CODE": "SOL"
            }

+ Response 200 (application/json)
    + Body

            {
                "data": {
                    "id": 355,
                    "code": "SOL",
                    "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/SOL",
                    "name": "Sol",
                    "status": "P",
                    "type": "SINGLE_STAR",
                    "position": {
                        "x": 0,
                        "y": 0,
                        "z": 0
                    },
                    "frost_line": 5,
                    "habitable_zone_inner": 0.9,
                    "habitable_zone_outer": 3,
                    "info_url": null,
                    "description": {
                        "de_DE": "...",
                        "en_EN": "..."
                    },
                    "aggregated": {
                        "size": 51,
                        "population": 8.59,
                        "economy": 5.58,
                        "danger": 0
                    },
                    "updated_at": "2020-10-15T13:19:19.000000Z",
                    "affiliation": {
                        "data": [
                            {
                                "id": 1,
                                "name": "UEE",
                                "code": "uee",
                                "color": "#48bbd4"
                            }
                        ]
                    }
                },
                "meta": {
                    "processed_at": "2020-12-08 20:40:13",
                    "valid_relations": [
                        "jumppoints",
                        "celestial_objects"
                    ]
                }
            }