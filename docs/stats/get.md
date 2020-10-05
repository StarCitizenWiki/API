# Show Funding statistics

API to query the current donation status, the fleet and the fans.  
The data is imported hourly.

**URL** : `/api/stats`

**Method** : `GET`

**Auth required** : NO

**Permissions required** : None

## Success Response

**Code** : `200 OK`

**Content examples**

```json
{
  "data": [
    {
      "funds": "315325379.24",
      "fans": "2832591",
      "fleet": "2832591",
      "timestamp": "2020-10-05T10:30:00.000000Z"
    }
  ],
  "meta": {}
}
```
