FORMAT: 1A

# Star Citizen Wiki Api

# Comm-Links [/comm-links]
Comm-Link Search API
Scraped Comm-Links from Roberts Space Industries

## Search for Comm-Links by title [POST /comm-links/search]
Returns matching Comm-Links

+ Parameters
    + keyword (string, required) - (Partial) Comm-Link title
    + include (string, optional) - Relations to include. Valid relations are shown in the meta data

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "keyword": "Welcome"
            }

+ Response 200 (application/json)
    + Body

            {
                "data": [
                    {
                        "id": 12663,
                        "title": "Welcome to the Comm-Link!",
                        "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/12663-Welcome-To-The-Comm-Link",
                        "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/12663",
                        "api_public_url": "https:\/\/api.star-citizen.wiki\/comm-links\/12663",
                        "channel": "Transmission",
                        "category": "General",
                        "series": "None",
                        "images": 2,
                        "links": 1,
                        "comment_count": 130,
                        "created_at": "2012-09-04T22:00:00.000000Z"
                    },
                    {
                        "id": 13098,
                        "title": "WelcometoRSIPrime",
                        "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/13098-Welcome-To-RSI-Prime",
                        "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/13098",
                        "api_public_url": "https:\/\/api.star-citizen.wiki\/comm-links\/13098",
                        "channel": "Transmission",
                        "category": "General",
                        "series": "None",
                        "images": 0,
                        "links": 0,
                        "comment_count": 32,
                        "created_at": "2013-06-27T22:00:00.000000Z"
                    },
                    {
                        "id": 13132,
                        "title": "WelcomeNewCitizens!",
                        "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/13132-Welcome-New-Citizens",
                        "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/13132",
                        "api_public_url": "https:\/\/api.star-citizen.wiki\/comm-links\/13132",
                        "channel": "Transmission",
                        "category": "General",
                        "series": "None",
                        "images": 1,
                        "links": 8,
                        "comment_count": 86,
                        "created_at": "2013-07-07T22:00:00.000000Z"
                    },
                    {
                        "id": 14157,
                        "title": "LOREBUILDER:FOURTEEN:Welcometov2",
                        "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/spectrum-dispatch\/14157-LORE-BUILDER-FOURTEEN-Welcome-To-V2",
                        "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/14157",
                        "api_public_url": "https:\/\/api.star-citizen.wiki\/comm-links\/14157",
                        "channel": "SpectrumDispatch",
                        "category": "Lore",
                        "series": "LoreBuilder",
                        "images": 3,
                        "links": 3,
                        "comment_count": 526,
                        "created_at": "2014-09-18T22:00:00.000000Z"
                    },
                    {
                        "id": 14927,
                        "title": "WelcometoArcCorp-StarCitizen1.2Released",
                        "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/14927-Welcome-To-ArcCorp-Star-Citizen-12-Released",
                        "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/14927",
                        "api_public_url": "https:\/\/api.star-citizen.wiki\/comm-links\/14927",
                        "channel": "Transmission",
                        "category": "General",
                        "series": "None",
                        "images": 17,
                        "links": 2,
                        "comment_count": 373,
                        "created_at": "2015-08-28T22:00:00.000000Z"
                    },
                    {
                        "id": 15256,
                        "title": "Ship Shape :Welcome Aboard the Starfarer",
                        "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/15256-Ship-Shape-Welcome-Aboard-The-Starfarer",
                        "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/15256",
                        "api_public_url": "https:\/\/api.star-citizen.wiki\/comm-links\/15256",
                        "channel": "Transmission",
                        "category": "General",
                        "series": "None",
                        "images": 0,
                        "links": 1,
                        "comment_count": 219,
                        "created_at": "2016-03-17T23:00:00.000000Z"
                    },
                    {
                        "id": 17342,
                        "title": "WelcomeHub&GuideSystem",
                        "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/17342-Welcome-Hub-Guide-System",
                        "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/17342",
                        "api_public_url": "https:\/\/api.star-citizen.wiki\/comm-links\/17342",
                        "channel": "Transmission",
                        "category": "General",
                        "series": "None",
                        "images": 6,
                        "links": 3,
                        "comment_count": 51,
                        "created_at": "2019-11-21T23:00:00.000000Z"
                    }
                ],
                "meta": {
                    "processed_at": "2020-12-0819:51:58",
                    "valid_relations": [
                        "images",
                        "links",
                        "english",
                        "german"
                    ]
                }
            }

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "keyword": "Keyword"
            }

+ Response 200 (application/json)
    + Body

            [
                {
                    "data": [],
                    "meta": {
                        "processed_at": "2020-12-0819:54:01",
                        "valid_relations": [
                            "images",
                            "links",
                            "english",
                            "german"
                        ]
                    }
                }
            ]

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "keyword": ""
            }

+ Response 422 (application/json)
    + Body

            {
                "message": "The given datawasinvalid.",
                "errors": {
                    "keyword": [
                        "keyword muss ausgefüllt sein."
                    ]
                },
                "status_code": 422
            }

## Performs a reverse comm-link search with a provided image url [POST /comm-links/reverse-image-link-search]
Returns matching Comm-Links

+ Parameters
    + url (string, required) - Url to an image hosted on (media.)robertsspaceindustries.com
    + include (string, optional) - Relations to include. Valid relations are shown in the meta data

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "url": "https://robertsspaceindustries.com/media/bluo97w6u7n1ur/post_section_header/Starshipbridge.jpg"
            }

+ Response 200 (application/json)
    + Body

            {
                "data": [
                    {
                        "id": 12663,
                        "title": "Welcome to the Comm-Link!",
                        "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/12663-Welcome-To-The-Comm-Link",
                        "api_url": "http:\/\/api\/api\/comm-links\/12663",
                        "api_public_url": "http:\/\/localhost:8000\/comm-links\/12663",
                        "channel": "Transmission",
                        "category": "General",
                        "series": "None",
                        "images": 1,
                        "links": 1,
                        "comment_count": 132,
                        "created_at": "2012-09-04T22:00:00.000000Z"
                    },
                    {
                        "id": 12667,
                        "title": "A Message from Chris Roberts",
                        "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/12667-A-Message-From-Chris-Roberts",
                        "api_url": "http:\/\/api\/api\/comm-links\/12667",
                        "api_public_url": "http:\/\/localhost:8000\/comm-links\/12667",
                        "channel": "Transmission",
                        "category": "General",
                        "series": "None",
                        "images": 2,
                        "links": 0,
                        "comment_count": 146,
                        "created_at": "2012-09-10T22:00:00.000000Z"
                    },
                    [
                        "..."
                    ]
                ],
                "meta": {
                    "processed_at": "2020-12-08 20:06:30",
                    "valid_relations": [
                        "images",
                        "links",
                        "english",
                        "german"
                    ]
                }
            }

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "url": "https://i.imgur.com/example.png"
            }

+ Response 422 (application/json)
    + Body

            [
                {
                    "message": "The given data was invalid.",
                    "errors": {
                        "url": [
                            "url Format ist ungültig."
                        ]
                    },
                    "status_code": 422
                }
            ]

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
    + Body

            {
                "url": ""
            }

+ Response 422 (application/json)
    + Body

            {
                "message": "The given data was invalid.",
                "errors": {
                    "url": [
                        "url muss ausgefüllt sein."
                    ]
                },
                "status_code": 422
            }

## Performs a reverse search by comparing image hashes
This is still very experimental [POST /comm-links/reverse-image-search]
Returns matching Comm-Links

+ Parameters
    + image (file, required) - JPEG / PNG File
    + similarity (number, required) - Similairty value between 1 and 100. Where 100 denotes a perfect match.
    + method (string, required) - Available methods: perceptual, difference, average
    + include (string, optional) - Relations to include. Valid relations are shown in the meta data

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json
            0: Content-Type
            1: multipart/form-data
    + Body

            {
                "image": "file",
                "similarity": 90,
                "method": "perceptual"
            }

+ Response 200 (application/json)
    + Body

            {
                "data": [
                    {
                        "rsi_url": "https:\/\/robertsspaceindustries.com\/media\/7e1mr7g2ycanhr\/source\/MarsTerraform_Final2b.jpg",
                        "api_url": null,
                        "alt": "",
                        "size": 720981,
                        "mime_type": "image\/jpeg",
                        "last_modified": "2013-07-19T03:30:36.000000Z",
                        "similarity": 73,
                        "hashes": {
                            "perceptual_hash": "d881a1df9f2a7d9b",
                            "difference_hash": "2b2e1f6fc9c3533b",
                            "average_hash": "7ffffce080040430"
                        },
                        "commLinks": {
                            "data": [
                                {
                                    "api_url": "http:\/\/api\/api\/comm-links\/12670"
                                }
                            ]
                        }
                    },
                    {
                        "rsi_url": "https:\/\/robertsspaceindustries.com\/media\/ve1gus81zoixrr\/source\/Marssurface3_FI.jpg",
                        "api_url": null,
                        "alt": "",
                        "size": 179006,
                        "mime_type": "image\/jpeg",
                        "last_modified": "2013-10-01T17:44:56.000000Z",
                        "similarity": 64,
                        "hashes": {
                            "perceptual_hash": "c00fe7ddce33fdff",
                            "difference_hash": "1f1f173b797f7f67",
                            "average_hash": "3c7efff7e04000"
                        },
                        "commLinks": {
                            "data": [
                                {
                                    "api_url": "http:\/\/api\/api\/comm-links\/12675"
                                }
                            ]
                        }
                    },
                    {
                        "rsi_url": "https:\/\/robertsspaceindustries.com\/media\/bluo97w6u7n1ur\/source\/Starshipbridge.jpg",
                        "api_url": null,
                        "alt": "",
                        "size": 1504015,
                        "mime_type": "image\/jpeg",
                        "last_modified": "2013-07-19T03:30:55.000000Z",
                        "similarity": 63,
                        "hashes": {
                            "perceptual_hash": "c1fbf0f2960db45d",
                            "difference_hash": "63898e4ece2f9b47",
                            "average_hash": "9dfffcf8b004"
                        },
                        "commLinks": {
                            "data": [
                                {
                                    "api_url": "http:\/\/api\/api\/comm-links\/12663"
                                },
                                [
                                    "..."
                                ]
                            ]
                        }
                    }
                ],
                "meta": {
                    "processed_at": "2020-12-08 20:18:34",
                    "valid_relations": [
                        "hashes",
                        "comm_links"
                    ]
                }
            }