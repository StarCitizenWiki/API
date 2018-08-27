Star Citizen Api
================

Schnittstelle zu verschiedenen Diensten von Star Citizen ( https://robertsspaceindustries.com/ )


Crowdfund Statistiken
---------------------
API zur Abfrage des aktuellen Spendenstatuses, der Fleet sowie der Fans.

Ein Import der Daten erfolgt stündlich.


Alle Statistiken
^^^^^^^^^^^^^^^^
|get|

|api_endpoint| */api/stats*


Query Parameter:

=========  =======      =======================================================================================================================  ==============  ========  ========  ========
Parameter  Typ          Beschreibung                                                                                                             Erlaubte Werte  Optional  Beispiel  Standard
=========  =======      =======================================================================================================================  ==============  ========  ========  ========
page       integer      Seite der Ausgabe. Anzahl der Seiten sowie derzeitige Seite stehen in den Metadaten der Ausgabe                                          Ja        1         1
limit      integer      Limitiert die Anzahl der Daten auf die angegebene Zahl. Ein Limit von '0' deaktiviert das Limit und gibt alle Daten aus                  Ja        1         10
=========  =======      =======================================================================================================================  ==============  ========  ========  ========


Beispielausgabe:

.. code-block:: json

    {
      "data": [
        {
          "funds": "192674121.56",
          "fans": 2088028,
          "fleet": 1639454,
          "timestamp": "2018-08-26 20:00:03"
        },
        {
          "funds": "192564446.10",
          "fans": 2080913,
          "fleet": 1639019,
          "timestamp": "2018-08-25 20:00:05"
        },
        {
          "funds": "...",
        }
      ],
      "meta": {
        "processed_at": "2018-08-27 18:14:12",
        "pagination": {
          "total": 1957,
          "count": 10,
          "per_page": 10,
          "current_page": 1,
          "total_pages": 196,
          "links": {
            "next": "https://api.star-citizen.wiki/api/stats?page=2"
          }
        }
      }
    }



Aktuelle Statistik
^^^^^^^^^^^^^^^^^^
|get|

|api_endpoint| */api/stats/latest*

Beispielausgabe:

.. code-block:: json

    {
      "data": {
        "funds": "192674121.56",
        "fans": 2088028,
        "fleet": 1639454,
        "timestamp": "2018-08-26 20:00:03"
      },
      "meta": {
        "processed_at": "2018-08-27 18:18:01"
      }
    }



Raumschiffe
-----------
API zur Abfrage der Raumschiffe aus der Ship Matrix ( https://robertsspaceindustries.com/ship-matrix )

Ein Import der Daten erfolgt wöchentlich, oder bei der Herausgabe eines neuen Raumschiffes.

|base_endpoint| */api/vehicles*


Alle Raumschiffe
^^^^^^^^^^^^^^^^
|get|

|api_endpoint| */api/vehicles/ships*


Query Parameter:

=========  =======      =======================================================================================================================  ==============  ========  ========  ========
Parameter  Typ          Beschreibung                                                                                                             Erlaubte Werte  Optional  Beispiel  Standard
=========  =======      =======================================================================================================================  ==============  ========  ========  ========
page       integer      Seite der Ausgabe. Anzahl der Seiten sowie derzeitige Seite stehen in den Metadaten der Ausgabe                                          Ja        1         1
limit      integer      Limitiert die Anzahl der Daten auf die angegebene Zahl. Ein Limit von '0' deaktiviert das Limit und gibt alle Daten aus                  Ja        1         5
locale     string       Sprache der zurückgegebenen Daten. Ersatzsprache ist en_EN (Englisch) bei fehlender deutscher Übersetzung                de_DE en_EN     Ja        de_DE
=========  =======      =======================================================================================================================  ==============  ========  ========  ========


Beispielausgabe (Ohne Lokalisierung):

**Anfrage URL**: */api/vehicles/ships*

.. code-block:: json

    {
      "data": [
        {
          "id": 1,
          "chassis_id": 1,
          "name": "Aurora ES",
          "sizes": {
            "length": "18.00",
            "beam": "8.00",
            "height": "4.00"
          },
          "mass": 25172,
          "cargo_capacity": 0,
          "crew": {
            "min": 1,
            "max": 1
          },
          "speed": {
            "scm": 190,
            "afterburner": 1140
          },
          "agility": {
            "pitch": "70.00",
            "yaw": "70.00",
            "roll": "95.00",
            "acceleration": {
              "x_axis": "43.00",
              "y_axis": "45.70",
              "z_axis": "44.20"
            }
          },
          "foci": [
            {
              "en_EN": "Starter",
              "de_DE": "Einsteiger"
            },
            {
              "en_EN": "Pathfinder",
              "de_DE": "Pfadfinder"
            }
          ],
          "production_status": {
            "en_EN": "flight-ready",
            "de_DE": "Flugbereit"
          },
          "production_note": {
            "en_EN": "None",
            "de_DE": "Keine"
          },
          "type": {
            "en_EN": "multi",
            "de_DE": "Mehrzweck"
          },
          "description": {
            "en_EN": "The Aurora is the modern-day descendant of the Roberts Space Industries X-7 spacecraft which tested the very first jump engines. Utilitarian to a T, the Aurora Essential is the perfect choice for new ship owners: versatile enough to tackle a myriad of challenges, yet with a straightforward and intuitive design."
          },
          "size": {
            "en_EN": "small",
            "de_DE": "Klein"
          },
          "manufacturer": {
            "code": "RSI",
            "name": "Roberts Space Industries"
          }
        },
        {
          "id": 2,
          "chassis_id": 1,
          "..."
        }
      ],
      "meta": {
        "processed_at": "2018-08-27 18:40:20",
        "pagination": {
          "total": 118,
          "count": 5,
          "per_page": 5,
          "current_page": 1,
          "total_pages": 24,
          "links": {
            "next": "https://api.star-citizen.wiki/api/vehicles/ships?page=2"
          }
        }
      }
    }


Beispielausgabe (Lokalisierung Deutsch):

**Anfrage URL**: */api/vehicles/ships?locale=de_DE*

.. code-block:: json

    {
      "data": [
        {
          "id": 1,
          "chassis_id": 1,
          "name": "Aurora ES",
          "sizes": {
            "length": "18.00",
            "beam": "8.00",
            "height": "4.00"
          },
          "mass": 25172,
          "cargo_capacity": 0,
          "crew": {
            "min": 1,
            "max": 1
          },
          "speed": {
            "scm": 190,
            "afterburner": 1140
          },
          "agility": {
            "pitch": "70.00",
            "yaw": "70.00",
            "roll": "95.00",
            "acceleration": {
              "x_axis": "43.00",
              "y_axis": "45.70",
              "z_axis": "44.20"
            }
          },
          "foci": [
            "Einsteiger",
            "Pfadfinder"
          ],
          "production_status": "Flugbereit",
          "production_note": "Keine",
          "type": "Mehrzweck",
          "description": "The Aurora is the modern-day descendant of the Roberts Space Industries X-7 spacecraft which tested the very first jump engines. Utilitarian to a T, the Aurora Essential is the perfect choice for new ship owners: versatile enough to tackle a myriad of challenges, yet with a straightforward and intuitive design.",
          "size": "Klein",
          "manufacturer": {
            "code": "RSI",
            "name": "Roberts Space Industries"
          }
        },
        {
          "id": 2,
          "chassis_id": 1,
          "..."
        }
      ],
      "meta": {
        "processed_at": "2018-08-27 18:42:36",
        "pagination": {
          "total": 118,
          "count": 5,
          "per_page": 5,
          "current_page": 1,
          "total_pages": 24,
          "links": {
            "next": "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ships?page=2"
          }
        }
      }
    }


Einzelnes Raumschiff
^^^^^^^^^^^^^^^^^^^^
|get|

|api_endpoint| */api/vehicles/ships/{Raumschiff_Name}*

|url_param| Der Name des Raumschiffes in URL enkodierter Form. Zum Beispiel ``Aurora+CL``


Query Parameter:

=========  =======      =======================================================================================================================  ==============  ========  ========  ========
Parameter  Typ          Beschreibung                                                                                                             Erlaubte Werte  Optional  Beispiel  Standard
=========  =======      =======================================================================================================================  ==============  ========  ========  ========
locale     string       Sprache der zurückgegebenen Daten. Ersatzsprache ist en_EN (Englisch) bei fehlender deutscher Übersetzung                de_DE en_EN     Ja        de_DE
=========  =======      =======================================================================================================================  ==============  ========  ========  ========


Beispielausgabe (Ohne Lokalisierung):

**Anfrage URL**: */api/vehicles/ships/300i*

.. code-block:: json

    {
      "data": {
        "id": 7,
        "chassis_id": 2,
        "name": "300i",
        "sizes": {
          "length": "23.00",
          "beam": "15.50",
          "height": "7.00"
        },
        "mass": 65925,
        "cargo_capacity": 2,
        "crew": {
          "min": 1,
          "max": 1
        },
        "speed": {
          "scm": 275,
          "afterburner": 1190
        },
        "agility": {
          "pitch": "85.00",
          "yaw": "85.00",
          "roll": "120.00",
          "acceleration": {
            "x_axis": "68.00",
            "y_axis": "80.30",
            "z_axis": "71.70"
          }
        },
        "foci": [
          {
            "en_EN": "Touring",
            "de_DE": "Reisen"
          }
        ],
        "production_status": {
          "en_EN": "flight-ready",
          "de_DE": "Flugbereit"
        },
        "production_note": {
          "en_EN": "Update Pass Scheduled",
          "de_DE": "Aktualisierungsprozess geplant"
        },
        "type": {
          "en_EN": "exploration",
          "de_DE": "Transport"
        },
        "description": {
          "en_EN": "If you're going to travel the stars... why not do it in style? The 300i is Origin Jumpworks' premiere luxury spacecraft. It is a sleek, silver killer that sends as much of a message with its silhouette as it does with its weaponry."
        },
        "size": {
          "en_EN": "small",
          "de_DE": "Klein"
        },
        "manufacturer": {
          "code": "ORIG",
          "name": "Origin Jumpworks GmbH"
        }
      },
      "meta": {
        "processed_at": "2018-08-27 18:55:10"
      }
    }


Beispielausgabe (Lokalisierung Deutsch):

**Anfrage URL**: */api/vehicles/ships/300i?locale=de_DE*

.. code-block:: json

    {
      "data": {
        "id": 7,
        "chassis_id": 2,
        "name": "300i",
        "sizes": {
          "length": "23.00",
          "beam": "15.50",
          "height": "7.00"
        },
        "mass": 65925,
        "cargo_capacity": 2,
        "crew": {
          "min": 1,
          "max": 1
        },
        "speed": {
          "scm": 275,
          "afterburner": 1190
        },
        "agility": {
          "pitch": "85.00",
          "yaw": "85.00",
          "roll": "120.00",
          "acceleration": {
            "x_axis": "68.00",
            "y_axis": "80.30",
            "z_axis": "71.70"
          }
        },
        "foci": [
          "Reisen"
        ],
        "production_status": "Flugbereit",
        "production_note": "Aktualisierungsprozess geplant",
        "type": "Transport",
        "description": "If you're going to travel the stars... why not do it in style? The 300i is Origin Jumpworks' premiere luxury spacecraft. It is a sleek, silver killer that sends as much of a message with its silhouette as it does with its weaponry.",
        "size": "Klein",
        "manufacturer": {
          "code": "ORIG",
          "name": "Origin Jumpworks GmbH"
        }
      },
      "meta": {
        "processed_at": "2018-08-27 18:55:48"
      }
    }


Beispielausgabe (Fehlerhafter Schiffsname):

**Anfrage URL**: */api/vehicles/ships/300*

.. code-block:: json

    {
      "message": "No Results for Query '300'",
      "status_code": 404
    }


Suche
^^^^^
|post|

|api_endpoint| */api/vehicles/ships/search*

**Request Body**: *query*

Beispielanfrage:

.. code-block:: php

    $client = new GuzzleHttp\Client([
        'timeout' => 3.0,
        'base_uri' => 'https://api.star-citizen.wiki/api',
        'headers' => [
            'Auth' => 'Bearer <API Key>',
            'Accept' => 'application/x.StarCitizenWikiApi.v1+json',
        ]
    ]);

    $res = $client->request(
        'POST',
        '/vehicles/ships/search',
        [
            'query' => 'Aurora'
        ]
    );

Ausgabe der Anfrage:

.. code-block:: json

    {
      "data": [
        {
          "id": 1,
          "chassis_id": 1,
          "name": "Aurora ES",
          "..."
        },
        {
          "id": 5,
          "chassis_id": 1,
          "name": "Aurora CL",
          "..."
        },
        {
          "id": 6,
          "chassis_id": 1,
          "name": "Aurora LN",
          "..."
        },
        {
          "id": 3,
          "chassis_id": 1,
          "name": "Aurora LX",
          "..."
        }
      ],
      "meta": {
        "processed_at": "2018-08-27 19:04:13",
        "pagination": {
          "total": 5,
          "count": 5,
          "per_page": 5,
          "current_page": 1,
          "total_pages": 1,
          "links": []
        }
      }
    }


Ausgabe einer fehlerhaften Anfrage:

.. code-block:: json

    {
      "message": "No Results for Query 'not existent'",
      "status_code": 404
    }



Bodenfahrzeuge
--------------
API zur Abfrage der Bodenfahrzeuge aus der Ship Matrix ( https://robertsspaceindustries.com/ship-matrix )

Ein Import der Daten erfolgt wöchentlich, oder bei der Herausgabe eines neuen Fahrzeuges.

|base_endpoint| */api/vehicles*


Alle Raumschiffe
^^^^^^^^^^^^^^^^
|get|

|api_endpoint| */api/vehicles/ground_vehicles*


Query Parameter:

=========  =======      =======================================================================================================================  ==============  ========  ========  ========
Parameter  Typ          Beschreibung                                                                                                             Erlaubte Werte  Optional  Beispiel  Standard
=========  =======      =======================================================================================================================  ==============  ========  ========  ========
page       integer      Seite der Ausgabe. Anzahl der Seiten sowie derzeitige Seite stehen in den Metadaten der Ausgabe                                          Ja        1         1
limit      integer      Limitiert die Anzahl der Daten auf die angegebene Zahl. Ein Limit von '0' deaktiviert das Limit und gibt alle Daten aus                  Ja        1         5
locale     string       Sprache der zurückgegebenen Daten. Ersatzsprache ist en_EN (Englisch) bei fehlender deutscher Übersetzung                de_DE en_EN     Ja        de_DE
=========  =======      =======================================================================================================================  ==============  ========  ========  ========


Beispielausgabe (Ohne Lokalisierung):

**Anfrage URL**: */api/vehicles/ground_vehicles*

.. code-block:: json

    {
      "data": [
        {
          "id": 134,
          "chassis_id": 53,
          "name": "Cyclone",
          "sizes": {
            "length": "6.00",
            "beam": "4.00",
            "height": "2.50"
          },
          "mass": 3022,
          "cargo_capacity": 1,
          "crew": {
            "min": 1,
            "max": 2
          },
          "speed": {
            "scm": 0
          },
          "foci": [
            {
              "en_EN": "Exploration",
              "de_DE": "Erkundung"
            },
            {
              "en_EN": "Recon",
              "de_DE": "Aufkl\u00e4rung"
            }
          ],
          "production_status": {
            "en_EN": "flight-ready",
            "de_DE": "Flugbereit"
          },
          "production_note": {
            "en_EN": "None",
            "de_DE": "Keine"
          },
          "type": {
            "en_EN": "ground",
            "de_DE": "Gel\u00e4nde"
          },
          "description": {
            "en_EN": "With a potent combination of speed, maneuverability, and rugged durability, the Cyclone is a perfect choice for local deliveries and transport between planetside homesteads and outposts."
          },
          "size": {
            "en_EN": "vehicle",
            "de_DE": "Fahrzeug"
          },
          "manufacturer": {
            "code": "TMBL",
            "name": "Tumbril"
          }
        },
        {
          "id": 135,
          "chassis_id": 53,
          "name": "Cyclone-TR",
          "..."
        }
      ],
      "meta": {
        "processed_at": "2018-08-27 19:19:34",
        "pagination": {
          "total": 9,
          "count": 5,
          "per_page": 5,
          "current_page": 1,
          "total_pages": 2,
          "links": {
            "next": "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ground_vehicles?page=2"
          }
        }
      }
    }


Beispielausgabe (Lokalisierung Deutsch):

**Anfrage URL**: */api/vehicles/ground_vehicles?locale=de_DE*

.. code-block:: json

    {
      "data": [
        {
          "id": 134,
          "chassis_id": 53,
          "name": "Cyclone",
          "sizes": {
            "length": "6.00",
            "beam": "4.00",
            "height": "2.50"
          },
          "mass": 3022,
          "cargo_capacity": 1,
          "crew": {
            "min": 1,
            "max": 2
          },
          "speed": {
            "scm": 0
          },
          "foci": {
            "Erkundung",
            "Aufklärung"
          },
          "production_status": "Flugbereit",
          "production_note": "Keine",
          "type": "Gelände",
          "description": "With a potent combination of speed, maneuverability, and rugged durability, the Cyclone is a perfect choice for local deliveries and transport between planetside homesteads and outposts.",
          "size": "Fahrzeug",
          "manufacturer": {
            "code": "TMBL",
            "name": "Tumbril"
          }
        },
        {
          "id": 135,
          "chassis_id": 53,
          "name": "Cyclone-TR",
          "..."
        }
      ],
      "meta": {
        "processed_at": "2018-08-27 19:19:34",
        "pagination": {
          "total": 9,
          "count": 5,
          "per_page": 5,
          "current_page": 1,
          "total_pages": 2,
          "links": {
            "next": "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ground_vehicles?page=2"
          }
        }
      }
    }


Einzelnes Bodenfahrzeug
^^^^^^^^^^^^^^^^^^^^^^^
|get|

|api_endpoint| */api/vehicles/ground_vehicles/{Fahrzeug_Name}*

|url_param| Der Name des Fahrzeuges in URL enkodierter Form. Zum Beispiel ``Nova+Tank``


Query Parameter:

=========  =======      =======================================================================================================================  ==============  ========  ========  ========
Parameter  Typ          Beschreibung                                                                                                             Erlaubte Werte  Optional  Beispiel  Standard
=========  =======      =======================================================================================================================  ==============  ========  ========  ========
locale     string       Sprache der zurückgegebenen Daten. Ersatzsprache ist en_EN (Englisch) bei fehlender deutscher Übersetzung                de_DE en_EN     Ja        de_DE
=========  =======      =======================================================================================================================  ==============  ========  ========  ========


Beispielausgabe (Ohne Lokalisierung):

**Anfrage URL**: */api/vehicles/ground_vehicles/Cyclone*

.. code-block:: json

    {
      "data": {
        "id": 134,
        "chassis_id": 53,
        "name": "Cyclone",
        "sizes": {
          "length": "6.00",
          "beam": "4.00",
          "height": "2.50"
        },
        "mass": 3022,
        "cargo_capacity": 1,
        "crew": {
          "min": 1,
          "max": 2
        },
        "speed": {
          "scm": 0
        },
        "foci": [
          {
            "en_EN": "Exploration",
            "de_DE": "Erkundung"
          },
          {
            "en_EN": "Recon",
            "de_DE": "Aufklärung"
          }
        ],
        "production_status": {
          "en_EN": "flight-ready",
          "de_DE": "Flugbereit"
        },
        "production_note": {
          "en_EN": "None",
          "de_DE": "Keine"
        },
        "type": {
          "en_EN": "ground",
          "de_DE": "Gelände"
        },
        "description": {
          "en_EN": "With a potent combination of speed, maneuverability, and rugged durability, the Cyclone is a perfect choice for local deliveries and transport between planetside homesteads and outposts."
        },
        "size": {
          "en_EN": "vehicle",
          "de_DE": "Fahrzeug"
        },
        "manufacturer": {
          "code": "TMBL",
          "name": "Tumbril"
        }
      },
      "meta": {
        "processed_at": "2018-08-27 19:22:59"
      }
    }


Beispielausgabe (Lokalisierung Deutsch):

**Anfrage URL**: */api/vehicles/ground_vehicles/Cyclone?locale=de_DE*

.. code-block:: json

    {
      "data": {
        "id": 134,
        "chassis_id": 53,
        "name": "Cyclone",
        "sizes": {
          "length": "6.00",
          "beam": "4.00",
          "height": "2.50"
        },
        "mass": 3022,
        "cargo_capacity": 1,
        "crew": {
          "min": 1,
          "max": 2
        },
        "speed": {
          "scm": 0
        },
        "foci": [
          "Erkundung",
          "Aufklärung"
        ],
        "production_status": "Flugbereit",
        "production_note": "Keine",
        "type": "Gelände",
        "description": "With a potent combination of speed, maneuverability, and rugged durability, the Cyclone is a perfect choice for local deliveries and transport between planetside homesteads and outposts.",
        "size": "Fahrzeug",
        "manufacturer": {
          "code": "TMBL",
          "name": "Tumbril"
        }
      },
      "meta": {
        "processed_at": "2018-08-27 19:24:37"
      }
    }


Beispielausgabe (Fehlerhafter Fahrzeugname):

**Anfrage URL**: */api/vehicles/ground_vehicles/Cyclon*

.. code-block:: json

    {
      "message": "No Results for Query 'Cyclon'",
      "status_code": 404
    }


Suche
^^^^^
|post|

|api_endpoint| */api/vehicles/ground_vehicles/search*

**Request Body**: *query*

Beispielanfrage:

.. code-block:: php

    $client = new GuzzleHttp\Client([
        'timeout' => 3.0,
        'base_uri' => 'https://api.star-citizen.wiki/api',
        'headers' => [
            'Auth' => 'Bearer <API Key>',
            'Accept' => 'application/x.StarCitizenWikiApi.v1+json',
        ]
    ]);

    $res = $client->request(
        'POST',
        '/vehicles/ground_vehicles/search',
        [
            'query' => 'Cyclone'
        ]
    );

Ausgabe der Anfrage:

.. code-block:: json

    {
      "data": [
        {
          "id": 134,
          "chassis_id": 53,
          "name": "Cyclone",
          "..."
        },
        {
          "id": 135,
          "chassis_id": 53,
          "name": "Cyclone-TR",
          "..."
        },
        {
          "id": 136,
          "chassis_id": 53,
          "name": "Cyclone-RC",
          "..."
        },
        {
          "id": 137,
          "chassis_id": 53,
          "name": "Cyclone-RN",
          "..."
        },
        {
          "id": 138,
          "chassis_id": 53,
          "name": "Cyclone-AA",
          "..."
        }
      ],
      "meta": {
        "processed_at": "2018-08-27 19:26:28",
        "pagination": {
          "total": 5,
          "count": 5,
          "per_page": 5,
          "current_page": 1,
          "total_pages": 1,
          "links": []
        }
      }
    }


Ausgabe einer fehlerhaften Anfrage:

.. code-block:: json

    {
      "message": "No Results for Query 'not existent'",
      "status_code": 404
    }



Hersteller
--------------
API zur Abfrage der Hersteller


Alle Hersteller
^^^^^^^^^^^^^^^^
|get|

|api_endpoint| */api/manufacturers*


Query Parameter:

=========  =======      =======================================================================================================================  =====================  ========  ========  ========
Parameter  Typ          Beschreibung                                                                                                             Erlaubte Werte         Optional  Beispiel  Standard
=========  =======      =======================================================================================================================  =====================  ========  ========  ========
page       integer      Seite der Ausgabe. Anzahl der Seiten sowie derzeitige Seite stehen in den Metadaten der Ausgabe                                                 Ja        1         1
limit      integer      Limitiert die Anzahl der Daten auf die angegebene Zahl. Ein Limit von '0' deaktiviert das Limit und gibt alle Daten aus                         Ja        1         10
locale     string       Sprache der zurückgegebenen Daten. Ersatzsprache ist en_EN (Englisch) bei fehlender deutscher Übersetzung                de_DE en_EN            Ja        de_DE
with       string       Komma separierter String mit namen der hinzuzufügenden Relationen                                                        ships ground_vehicles  Ja        ships
=========  =======      =======================================================================================================================  =====================  ========  ========  ========


Beispielausgabe (Ohne Lokalisierung):

.. code-block:: json

    {
      "data": [
        {
          "code": "RSI",
          "name": "Roberts Space Industries",
          "known_for": {
            "en_EN": "the Aurora and the Constellation"
          },
          "description": {
            "en_EN": "..."
          }
        },
        {
          "code": "ORIG",
          "name": "Origin Jumpworks GmbH",
          "known_for": {
            "en_EN": "the 300i series"
          },
          "description": {
            "en_EN": "..."
          }
        },
        {
          "code": "ANVL",
          "name": "Anvil Aerospace",
          "..."
        },
        {
          "..."
        }
      ],
      "meta": {
        "processed_at": "2018-08-27 19:30:19",
        "valid_relations": [
          "ships",
          "ground_vehicles"
        ],
        "pagination": {
          "total": 16,
          "count": 10,
          "per_page": 10,
          "current_page": 1,
          "total_pages": 2,
          "links": {
            "next": "https:\/\/api.star-citizen.wiki\/api\/manufacturers?page=2"
          }
        }
      }
    }


Einzelner Hersteller
^^^^^^^^^^^^^^^^^^^^
|get|

|api_endpoint| */api/manufacturers/{Hersteller_Code}*


Query Parameter:

=========  =======      =======================================================================================================================  =====================  ========  ========  ========
Parameter  Typ          Beschreibung                                                                                                             Erlaubte Werte         Optional  Beispiel  Standard
=========  =======      =======================================================================================================================  =====================  ========  ========  ========
locale     string       Sprache der zurückgegebenen Daten. Ersatzsprache ist en_EN (Englisch) bei fehlender deutscher Übersetzung                de_DE en_EN            Ja        de_DE
with       string       Komma separierter String mit namen der hinzuzufügenden Relationen                                                        ships ground_vehicles  Ja        ships
=========  =======      =======================================================================================================================  =====================  ========  ========  ========


Beispielausgabe (Ohne Relationen):

.. code-block:: json

    {
      "data": {
        "code": "RSI",
        "name": "Roberts Space Industries",
        "known_for": {
          "en_EN": "the Aurora and the Constellation"
        },
        "description": {
          "en_EN": "..."
        }
      },
      "meta": {
        "processed_at": "2018-08-27 19:32:04",
        "valid_relations": [
          "ships",
          "ground_vehicles"
        ]
      }
    }


Beispielausgabe (Mit Relation Raumschiffe):

**Anfrage URL**: */api/manufacturers/CRSD?with=ships*

.. code-block:: json

    {
      "data": {
        "code": "CRSD",
        "name": "Crusader Industries",
        "known_for": {
          "en_EN": "Genesis Starliner"
        },
        "description": {
          "en_EN": "Genesis Starliner"
        },
        "ships": [
          "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ships\/Genesis+Starliner",
          "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ships\/C2+Hercules",
          "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ships\/M2+Hercules",
          "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ships\/A2+Hercules",
          "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ships\/Mercury+Star+Runner"
        ]
      },
      "meta": {
        "processed_at": "2018-08-27 19:37:31",
        "valid_relations": [
          "ships",
          "ground_vehicles"
        ]
      }
    }


Beispielausgabe (Mit Relationen Raumschiffe und Bodenfahrzeuge):

**Anfrage URL**: */api/manufacturers/CRSD?with=ships,ground_vehicles*

.. code-block:: json

    {
      "data": {
        "code": "CRSD",
        "name": "Crusader Industries",
        "known_for": {
          "en_EN": "Genesis Starliner"
        },
        "description": {
          "en_EN": "Genesis Starliner"
        },
        "ships": [
          "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ships\/Genesis+Starliner",
          "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ships\/C2+Hercules",
          "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ships\/M2+Hercules",
          "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ships\/A2+Hercules",
          "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ships\/Mercury+Star+Runner"
        ],
        "ground_vehicles": []
      },
      "meta": {
        "processed_at": "2018-08-27 19:38:26",
        "valid_relations": [
          "ships",
          "ground_vehicles"
        ]
      }
    }


Suche
^^^^^
|post|

|api_endpoint| */api/manufacturers/search*

**Request Body**: *query*

Die Suche kann sowohl nach dem Hersteller ``Code`` als auch dem Hersteller ``Namen`` erfolgen, also sowohl ``RSI`` als auch ``Roberts``

Beispielanfrage:

.. code-block:: php

    $client = new GuzzleHttp\Client([
        'timeout' => 3.0,
        'base_uri' => 'https://api.star-citizen.wiki/api',
        'headers' => [
            'Auth' => 'Bearer <API Key>',
            'Accept' => 'application/x.StarCitizenWikiApi.v1+json',
        ]
    ]);

    $res = $client->request(
        'POST',
        '/manufacturers/search',
        [
            'query' => 'Roberts'
        ]
    );

Ausgabe der Anfrage:

.. code-block:: json

    {
      "data": [
        {
          "code": "RSI",
          "name": "Roberts Space Industries",
          "known_for": {
            "en_EN": "the Aurora and the Constellation"
          },
          "description": {
            "en_EN": "..."
          }
        }
      ],
      "meta": {
        "processed_at": "2018-08-27 19:40:26",
        "valid_relations": [
          "ships",
          "ground_vehicles"
        ],
        "pagination": {
          "total": 1,
          "count": 1,
          "per_page": 10,
          "current_page": 1,
          "total_pages": 1,
          "links": []
        }
      }
    }


Ausgabe einer fehlerhaften Anfrage:

.. code-block:: json

    {
      "message": "No Results for Query 'not existent'",
      "status_code": 404
    }







.. |get| replace:: **Anfragetyp**: *GET*
.. |post| replace:: **Anfragetyp**: *POST*
.. |url_param| replace:: **URL Parameter**:
.. |api_endpoint| replace:: **API Anfragepunkt**:
.. |base_endpoint| replace:: **Basis API Anfragepunkt**: