# Comm-Link Series

Comm-Link series

**URL** : `/api/comm-links/series`

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
      "name": "10 For the Chairman",
      "slug": "10-for-the-chairman",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/series\/10-for-the-chairman"
    },
    {
      "name": "A Separate Law",
      "slug": "a-separate-law",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/series\/a-separate-law"
    },
    {
      "name": "Around the Verse",
      "slug": "around-the-verse",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/series\/around-the-verse"
    },
    {
      "name": "Behind the Scenes",
      "slug": "behind-the-scenes",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/series\/behind-the-scenes"
    },
    {
      "name": "Brothers In Arms",
      "slug": "brothers-in-arms",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/series\/brothers-in-arms"
    }
  ],
  "meta": {
    "processed_at": "2020-10-05 13:35:06",
    "pagination": {
      "total": 54,
      "count": 5,
      "per_page": 5,
      "current_page": 1,
      "total_pages": 11,
      "links": {
        "next": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/series?page=2"
      }
    }
  }
}
```
