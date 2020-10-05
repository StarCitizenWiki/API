# Comm-Links

Comm-Links

**URL** : `/api/comm-links`

**Method** : `GET`

**Auth required** : NO

**Permissions required** : None

**Data constraints** : `{}`

## Success Response

**Code** : `200 OK`

**Content examples**

```json
{
  "data": [
    {
      "id": 17810,
      "title": "Star Citizen Live",
      "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/17810-Star-Citizen-Live",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/17810",
      "api_public_url": "https:\/\/api.star-citizen.wiki\/comm-links\/17810",
      "channel": "Transmission",
      "category": "General",
      "series": "Star Citizen LIVE",
      "images": 1,
      "links": 2,
      "comment_count": 2,
      "created_at": "2020-10-01T22:00:00.000000Z"
    }
  ],
  "meta": {
    "processed_at": "2020-10-05 13:31:50",
    "valid_relations": [
      "images",
      "links",
      "english",
      "german"
    ],
    "pagination": {
      "total": 4162,
      "count": 1,
      "per_page": 1,
      "current_page": 1,
      "total_pages": 4162,
      "links": {
        "next": "https:\/\/api.star-citizen.wiki\/api\/comm-links?page=2"
      }
    }
  }
}
```
