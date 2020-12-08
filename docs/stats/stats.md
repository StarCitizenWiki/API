FORMAT: 1A

# Star Citizen Wiki Api

# Stats [/stats]
Stat API
Returns current funding, fan and fleet stats
Import happens daily at 8PM UTC+1

## Returns latest stats [GET /stats/latest]


+ Request (application/json)
    + Headers

            Accept: application/x.StarCitizenWikiApi.v1+json

+ Response 200 (application/json)
    + Body

            {
                "data": {
                    "funds": "335901302.36",
                    "fans": 2910921,
                    "fleet": 2910921,
                    "timestamp": "2020-12-06T19:00:55.000000Z"
                },
                "meta": {
                    "processed_at": "2020-12-07 13:11:56"
                }
            }

## Returns all funding stats [GET /stats{?page,limit}]


+ Parameters
    + page (integer, optional) - Pagination page
        + Default: 1
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
                        "funds": "335901302.36",
                        "fans": 2910921,
                        "fleet": 2910921,
                        "timestamp": "2020-12-06T19:00:55.000000Z"
                    },
                    {
                        "funds": "335700500.89",
                        "fans": 2909995,
                        "fleet": 2909995,
                        "timestamp": "2020-12-05T19:00:46.000000Z"
                    },
                    {
                        "funds": "..."
                    }
                ],
                "meta": {
                    "processed_at": "2020-12-07 13:11:53",
                    "pagination": {
                        "total": 2903,
                        "count": 10,
                        "per_page": 10,
                        "current_page": 1,
                        "total_pages": 291,
                        "links": {
                            "next": "https:\/\/api.star-citizen.wiki\/api\/stats?page=2"
                        }
                    }
                }
            }