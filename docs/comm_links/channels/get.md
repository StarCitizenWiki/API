# Comm-Link Channels

Comm-Link channels

**URL** : `/api/comm-links/channels`

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
      "name": "CitizenCon 2947",
      "slug": "citizencon-2947",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/channels\/citizencon-2947"
    },
    {
      "name": "Citizens",
      "slug": "citizens",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/channels\/citizens"
    },
    {
      "name": "Engineering",
      "slug": "engineering",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/channels\/engineering"
    },
    {
      "name": "Featured post",
      "slug": "featured-post",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/channels\/featured-post"
    },
    {
      "name": "Feedback",
      "slug": "feedback",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/channels\/feedback"
    }
  ],
  "meta": {
    "processed_at": "2020-10-05 13:34:55",
    "pagination": {
      "total": 11,
      "count": 5,
      "per_page": 5,
      "current_page": 1,
      "total_pages": 3,
      "links": {
        "next": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/channels?page=2"
      }
    }
  }
}
```
