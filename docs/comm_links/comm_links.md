FORMAT: 1A

# Star Citizen Wiki Api

# Comm-Links [/comm-links]
Comm-Link API
Scraped Comm-Links from Roberts Space Industries

## Returns all Comm-Links [GET /comm-links{?page,limit,include}]


+ Parameters
    + page (integer, optional) - Pagination page
        + Default: 1
    + include (string, optional) - Relations to include. Valid relations are shown in the meta data
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
                        "id": 17911,
                        "title": "Star Citizen Live",
                        "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/17911-Star-Citizen-Live",
                        "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/17911",
                        "api_public_url": "https:\/\/api.star-citizen.wiki\/comm-links\/17911",
                        "channel": "Transmission",
                        "category": "General",
                        "series": "Star Citizen LIVE",
                        "images": 1,
                        "links": 2,
                        "comment_count": 4,
                        "created_at": "2020-12-03T23:00:00.000000Z"
                    },
                    {
                        "id": 17909,
                        "title": "Inside Star Citizen",
                        "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/17909-Inside-Star-Citizen",
                        "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/17909",
                        "api_public_url": "https:\/\/api.star-citizen.wiki\/comm-links\/17909",
                        "channel": "Transmission",
                        "category": "General",
                        "series": "Inside Star Citizen",
                        "images": 1,
                        "links": 2,
                        "comment_count": 18,
                        "created_at": "2020-12-02T23:00:00.000000Z"
                    }
                ],
                "meta": {
                    "processed_at": "2020-12-07 14:45:18",
                    "valid_relations": [
                        "images",
                        "links",
                        "english",
                        "german"
                    ],
                    "pagination": {
                        "total": 4229,
                        "count": 15,
                        "per_page": 15,
                        "current_page": 1,
                        "total_pages": 282,
                        "links": {
                            "next": "https:\/\/api.star-citizen.wiki\/api\/comm-links?page=2"
                        }
                    }
                }
            }

## Returns a singular comm-link by its cig_id [GET /comm-links/{ID}{?include}]


+ Parameters
    + ID (interger, required) - Comm-Link ID
    + include (string, optional) - Relations to include. Valid relations are shown in the meta data

+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json

+ Response 200 (application/json)
    + Body

            {
                "data": {
                    "id": 17911,
                    "title": "Star Citizen Live",
                    "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/17911-Star-Citizen-Live",
                    "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/17911",
                    "api_public_url": "https:\/\/api.star-citizen.wiki\/comm-links\/17911",
                    "channel": "Transmission",
                    "category": "General",
                    "series": "Star Citizen LIVE",
                    "images": {
                        "data": [
                            {
                                "rsi_url": "...",
                                "api_url": null,
                                "alt": "",
                                "size": 18693,
                                "mime_type": "image\/png",
                                "last_modified": "2016-05-05T01:15:45.000000Z"
                            }
                        ]
                    },
                    "links": {
                        "data": [
                            {
                                "href": "http:\/\/twitch.tv\/starcitizen",
                                "text": "http:\/\/twitch.tv\/starcitizen"
                            },
                            {
                                "href": "https:\/\/www.youtube.com\/embed\/gsWDdomcMCM?wmode=transparent",
                                "text": "iframe"
                            }
                        ]
                    },
                    "comment_count": 4,
                    "created_at": "2020-12-03T23:00:00.000000Z"
                },
                "meta": {
                    "processed_at": "2020-12-07 14:52:11",
                    "valid_relations": [
                        "images",
                        "links",
                        "english",
                        "german"
                    ],
                    "prev_id": 17909,
                    "next_id": -1
                }
            }