FORMAT: 1A

# Star Citizen Wiki Api

# Starsystems [/starmap/starsystems]
Star System API
Systems from the Starmap

## All available Star Systems [GET /starmap/starsystems]


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
                        "id": 398,
                        "code": "AYR'KA",
                        "system_api_url": "http:\/\/api\/api\/starmap\/starsystems\/AYR'KA",
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
                        "time_modified": "2018-11-14 19:51:33",
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
                    [
                        "..."
                    ]
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
                            "next": "http:\/\/localhost:8000\/api\/starmap\/starsystems?page=2"
                        }
                    }
                }
            }

## A singular Star System [GET /starmap/starsystems/{CODE}]


+ Parameters
    + CODE (string, required) - Star System Code or ID
    + include (string, optional) - Relations to include. Valid relations are shown in the meta data

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
                    "system_api_url": "http:\/\/api\/api\/starmap\/starsystems\/SOL",
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
                    "time_modified": "2015-10-10 14:09:45",
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