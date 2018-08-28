Start
=====
Generelle Informationen zum Stellen einer Anfrage an die API.

Derzeit gültige API Versionen: ``v1``

Standardversion im Falle eines fehlenden Versions-Headers: ``v1``


Durchsatzratenbegrenzung (Rate-Limiting)
----------------------------------------
Ohne eine Registrierung auf der Star Citizen Wiki API werden Anfragen mit folgenden Werten limitiert:

Anfragen in einer Minute: ``10``

Nach erfolgreicher Registrierung wird dieses Limit auf ``60`` Anfragen die Minute angehoben.

Dies setzt voraus, dass ein gültiger API Schlüssel in der Anfrage vorhanden ist.

Werden mehr als ``60`` Anfragen die Minute benötigt kann unter info@star-citizen.wiki eine Aufhebung des Limits beantragt werden.


Header
------
Die API unterstützt verschiedene Anfrage-Header zum Steuern der zurückgegebenen Daten.

Auswahl der API Version::

    Accept: application/x.StarCitizenWikiApi.v1+json

Die gültigen API Versionen stehen am Anfang dieses Dokuments.

Authentifizierung::

    Auth: Bearer <API Key>

Der API Schlüssel wird nach der Anmeldung im Account-Bereich angezeigt.


Struktur der zurückgegebenen Daten
----------------------------------
Im Falle einer erfolgreichen API Anfrage mit HTTP-Code ``200`` enthalten die zurückgegebenen Daten immer ein ``data`` und ein ``meta`` Feld.

Das ``meta`` Feld enthält darüberhinaus immer das Feld ``processed_at`` mit einem Timestamp der anzeigt wann die Anfrage ausgeführt wurde.

.. code-block:: json

    {
      "data": {},
      "meta": {
        "processed_at": "2018-08-01 00:00:00"
      }
    }

Im Falle einer nicht erfolgreichen Anfrage ist die Struktur der Daten wie folgt:

.. code-block:: json

    {
      "message": "No Results for Query '{query}'",
      "status_code": 404
    }


Beispielanfrage mit Guzzle
--------------------------

.. code-block:: php

    $client = new GuzzleHttp\Client([
        'timeout' => 3.0,
        'base_uri' => 'https://api.star-citizen.wiki',
        'headers' => [
            'Auth' => 'Bearer <API Key>',
            'Accept' => 'application/x.StarCitizenWikiApi.v1+json',
        ]
    ]);

    $res = $client->request('GET', '/api/stats/latest');
    echo $res->getStatusCode();
    // "200"
    echo $res->getBody();
    // {"data":{"funds"...'

