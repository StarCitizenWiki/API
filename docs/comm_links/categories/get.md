# Comm-Link Categories

Comm-Link categories

**URL** : `/api/comm-links/categories`

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
      "name": "Community",
      "slug": "community",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/categories\/community"
    },
    {
      "name": "Development",
      "slug": "development",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/categories\/development"
    },
    {
      "name": "Frankfurt - October 27th, 2017",
      "slug": "frankfurt-october-27th-2017",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/categories\/frankfurt-october-27th-2017"
    },
    {
      "name": "General",
      "slug": "general",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/categories\/general"
    },
    {
      "name": "Lore",
      "slug": "lore",
      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/categories\/lore"
    }
  ],
  "meta": {
    "processed_at": "2020-10-05 13:32:33",
    "pagination": {
      "total": 7,
      "count": 5,
      "per_page": 5,
      "current_page": 1,
      "total_pages": 2,
      "links": {
        "next": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/categories?page=2"
      }
    }
  }
}
```
