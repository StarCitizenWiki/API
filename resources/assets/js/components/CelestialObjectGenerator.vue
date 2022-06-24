<template>
  <div class="card mb-3" id="cl-test">
    <div class="card-header">
      <h4>
        Celestial Objekt Ersteller
      </h4>
    </div>
    <div class="card-body">
      <div class="alert alert-danger" v-if="hasError">Fehler bei der Suchabfrage! {{ error }}</div>
      <div class="alert alert-info" v-if="loading">Lade Daten...</div>
      <div class="row">
        <div class="col-12 col-lg-6 mx-auto">
          <form method="POST" class="d-flex h-100 flex-column">
            <div class="row">
              <div class="col-12 col-lg-6">
                <div class="form-group">
                  <label for="systemSelect">System</label>
                  <select id="systemSelect" class="form-control" v-model="selectedSystem" v-on:change="loadChildren">
                    <option v-for="system in starSystems" :value="system">{{ system.name }}</option>
                  </select>
                </div>
              </div>
              <div class="col-12 col-lg-6">
                <div class="form-group" v-if="childObjects.length">
                  <label for="parentSelect">Elternobjekt</label>
                  <select id="parentSelect" class="form-control" v-model="selectedChild" v-on:change="updateChildId">
                    <option selected>NONE</option>
                    <option v-for="child in childObjects" :value="child">{{ child.name }}</option>
                  </select>
                  <small v-if="dataTS" class="form-text text-muted">Daten vom {{ this.dataTS }}</small>
                </div>
              </div>
            </div>

            <div v-if="selectedChild">
              <div class="row">
                <div class="col-12">
                  <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" placeholder="Objektname" v-on:input="setCode" v-model="newObj.name">
                    <small class="form-text text-muted">Name des Objekts</small>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  <div class="form-group">
                    <label for="code">Code</label>
                    <input type="text" class="form-control" id="code" disabled v-on:change="generate" :value="newObj.code">
                    <small class="form-text text-muted">Der Code wird automatisch anhand des Namens generiert</small>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12 col-lg-6 col-xl-9">
                  <div class="form-group">
                    <label for="parentSelect">Typ</label>
                    <select id="typ" class="form-control" v-model="newObj.type" v-on:change="generate">
                      <option value="ASTEROID_FIELD">Asteroidenfeld</option>
                      <option value="ASTEROID_BELT">Asteroidengürtel</option>
                      <option value="BINARY">Binärer Stern</option>
                      <option value="SINGLE_STAR">Einzelner Stern</option>
                      <option selected value="ENTITY">Entität</option>
                      <option value="CAVE">Höhle</option>
                      <option value="LANDINGZONE">Landezone</option>
                      <option value="LAGRANGE_POINT">Lagrange Punkt</option>
                      <option value="SATELLITE">Mond</option>
                      <option value="PERSON">Person</option>
                      <option value="PLANET">Planet</option>
                      <option value="POI">POI</option>
                      <option value="MANMADE">Raumstation</option>
                      <option value="BLACKHOLE">Schwarzes Loch</option>
                      <option value="SETTLEMENT">Siedlung</option>
                      <option value="JUMPPOINT">Sprungpunkt</option>
                      <option value="STATE">Staat</option>
                      <option value="SHOP">Shop</option>
                      <option value="TOWN">Stadt</option>
                      <option value="STAR">Stern</option>
                      <option value="STRUCTURE">Struktur</option>
                      <option value="SHELTER">Unterkunft</option>
                    </select>
                  </div>
                </div>

                <div class="col-12 col-lg-6 col-xl-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="quantumbeacon" v-model="newObj.quantumbeacon" v-on:change="generate">
                    <label class="form-check-label" for="quantumbeacon">Navigationspunkt</label>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="id">Objekt ID</label>
                    <input type="number" class="form-control" id="id" v-model="newObj.id" v-on:change="generate">
                    <small class="form-text text-muted">Diese ID muss eindeutig sein. Sollte die Wikiseite auch nach mehreren Minuten einen Fehler anzeigen, dann erhöhe diese ID.</small>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="parent">Elternobjekt ID</label>
                    <input type="text" class="form-control" id="parent" :value="parent" disabled>
                    <small class="form-text text-muted">Die ID des Elternobjekts</small>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12 col-lg-4">
                  <div class="form-group">
                    <label for="economy">Wirtschaft</label>
                    <input type="number" class="form-control" id="economy" v-on:change="generate" v-model="newObj.economy" min="0" max="10">
                  </div>
                </div>
                <div class="col-12 col-lg-4">
                  <div class="form-group">
                    <label for="danger">Gefahr</label>
                    <input type="number" class="form-control" id="danger" v-on:change="generate" v-model="newObj.danger" min="0" max="10">
                  </div>
                </div>
                <div class="col-12 col-lg-4">
                  <div class="form-group">
                    <label for="population">Bevölkerung</label>
                    <input type="number" class="form-control" id="population" v-on:change="generate" v-model="newObj.population" min="0" max="10">
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group" >
                    <label for="control">Kontrolle</label>
                    <select id="control" class="form-control" v-model="newObj.control" v-on:change="generate">
                      <option selected value="">Unbekannt</option>
                      <option value="UEE">Menschen</option>
                      <option value="Xi'an">Xi'an</option>
                      <option value="Banu">Banu</option>
                      <option value="Vanduul">Vanduul</option>
                    </select>
                  </div>
                </div>

                <div class="col-12 col-lg-6 my-3">
                  <hr>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="isSystemEntity" v-model="isSystemEntity" v-on:change="generate">
                    <label class="form-check-label" for="isSystemEntity">Dies ist ein Sternensystemobjekt</label>
                  </div>
                </div>

                <div class="col-12 col-lg-6 my-3">
                  <hr>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="hideBox" v-model="hideBox" v-on:change="generate">
                    <label class="form-check-label" for="hideBox">Infobox verstecken</label>
                  </div>
                </div>
              </div>

              <div class="row" v-if="newObj.type && newObj.type === 'PERSON'">
                <div class="col-12">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="merchant" v-model="newObj.merchant" v-on:change="generate">
                    <label class="form-check-label" for="merchant">Person ist ein Händler</label>
                  </div>
                </div>
              </div>

              <div class="row" v-if="isSystemEntity">

                <div class="col-12">
                  <h4>Recht</h4>
                </div>

                <div class="col-12">
                  <div class="form-group">
                    <label for="control_type">Rechtsraum</label>
                    <select id="control_type" class="form-control" v-model="newObj.control_type" v-on:change="generate">
                      <option selected value="ArcCorp">ArcCorp</option>
                      <option value="Crusader">Crusader</option>
                      <option value="GrimHex">GrimHex</option>
                      <option value="Hurston">Hurston</option>
                      <option value="Klescher R.F.">Klescher R.F.</option>
                      <option value="Microtech">Microtech</option>
                      <option value="UEE">UEE</option>
                    </select>
                  </div>
                </div>

                  <div class="row mx-1">
                    <div class="col-12 col-lg-6 col-xl-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="weapon_blocked" v-model="newObj.weapon_blocked" v-on:change="generate">
                        <label class="form-check-label" for="weapon_blocked">Waffenblockierung</label>
                      </div>
                    </div>

                    <div class="col-12 col-lg-6 col-xl-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="weapon_zone" v-model="newObj.weapon_zone_inner" v-on:change="generate">
                        <label class="form-check-label" for="weapon_zone">Waffensperrung Innenbereich</label>
                      </div>
                    </div>

                    <div class="col-12 col-lg-6 col-xl-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="weapon_zone_outer" v-model="newObj.weapon_zone_outer" v-on:change="generate">
                        <label class="form-check-label" for="weapon_zone_outer">Waffensperrung Umgebung</label>
                      </div>
                    </div>

                    <div class="col-12 col-lg-6 col-xl-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="controlled_zone" v-model="newObj.controlled_zone" v-on:change="generate">
                        <label class="form-check-label" for="controlled_zone">Überwachung von Straftaten</label>
                      </div>
                    </div>
                </div>

                <div class="col-12"><hr></div>





                <div class="col-12">
                  <h4>Mobilität</h4>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="public_transport" v-model="newObj.public_transport" v-on:change="generate">
                    <label class="form-check-label" for="public_transport">ÖPNV</label>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="atc" v-model="newObj.atc" v-on:change="generate">
                    <label class="form-check-label" for="atc">Lande- und Startkontrolle</label>
                  </div>
                </div>

                <div class="col-12 my-2"></div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="landing_platforms">Landeplattformen</label>
                    <input type="number" class="form-control" id="landing_platforms" v-on:change="generate" v-on:input="generate" v-model="newObj.landing_platforms" min="0" max="50">
                    <small class="form-text text-muted">Anzahl Landeplattformen</small>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="hangars">Hangars</label>
                    <input type="number" class="form-control" id="hangars" v-on:change="generate" v-on:input="generate" v-model="newObj.hangars" min="0" max="50">
                    <small class="form-text text-muted">Anzahl Hangars für Raumschiffe</small>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="garages">Garagen</label>
                    <input type="number" class="form-control" id="garages" v-on:change="generate" v-on:input="generate" v-model="newObj.garages" min="0" max="50">
                    <small class="form-text text-muted">Anzahl Garagen für Bodenfahrzeuge</small>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="docking_stations">Dockingstationen</label>
                    <input type="number" class="form-control" id="docking_stations" v-on:change="generate" v-on:input="generate" v-model="newObj.docking_stations" min="0" max="50">
                    <small class="form-text text-muted">Anzahl Dockingstationen für Raumschiffe</small>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="vehicle_terminals">Konsole zum Abrufen von Bodenfahrzeugen</label>
                    <input type="text" class="form-control" id="vehicle_terminals" v-on:change="generate" v-on:input="generate" v-model="newObj.vehicle_terminals">
                    <small class="form-text text-muted">z.B. 2x Platinum Bay</small>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="ship_terminals">Konsole zum Abrufen von Raumschiffen</label>
                    <input type="number" class="form-control" id="ship_terminals" v-on:change="generate" v-on:input="generate" v-model="newObj.ship_terminals" min="0" max="50">
                    <small class="form-text text-muted">Anzahl Konsolen</small>
                  </div>
                </div>

                <div class="col-12"><hr></div>









                <div class="col-12 mb-3">
                  <h4>Ökologie</h4>
                </div>

                <div class="col-12 col-lg-6 col-xl-4 mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="habitable" v-model="newObj.habitable_inner" v-on:change="generate">
                    <label class="form-check-label" for="habitable">Habitabel Innenbereich</label>
                  </div>
                </div>

                <div class="col-12 col-lg-6 col-xl-4 mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="habitable_outer" v-model="newObj.habitable_outer" v-on:change="generate">
                    <label class="form-check-label" for="habitable_outer">Habitabel Außenbereich</label>
                  </div>
                </div>

                <div class="col-12 col-lg-6 col-xl-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="clinic" v-model="newObj.clinic" v-on:change="generate">
                    <label class="form-check-label" for="clinic">Krankenhaus / Klinik</label>
                  </div>
                </div>

                <div class="col-12 col-lg-6 col-xl-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="imprinting_possible" v-model="newObj.imprinting_possible" v-on:change="generate">
                    <label class="form-check-label" for="imprinting_possible">Prägung möglich</label>
                  </div>
                </div>

                <div class="col-12 col-lg-6 col-xl-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="accommodations" v-model="newObj.accommodations" v-on:change="generate">
                    <label class="form-check-label" for="accommodations">Unterkünfte</label>
                  </div>
                </div>

                <div class="col-12 my-2"></div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="temp_shadow">Temperatur (Schattenseite)</label>
                    <input type="number" class="form-control" id="temp_shadow" v-on:change="generate" v-on:input="generate" v-model="newObj.temp_shadow">
                    <small class="form-text text-muted">Temperatur mit Einheit, z.B.: -100°C</small>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="temp_sun">Temperatur (Sonnenseite)</label>
                    <input type="number" class="form-control" id="temp_sun" v-on:change="generate" v-on:input="generate" v-model="newObj.temp_sun">
                    <small class="form-text text-muted">Temperatur mit Einheit, z.B.: 60°C</small>
                  </div>
                </div>

                <div class="col-12"><hr></div>







                <div class="col-12 mb-3">
                  <h4>Ökonomie</h4>
                </div>

                <div class="row mx-1 mb-3 w-100">
                  <div class="col-12 col-lg-6 col-xl-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="refinery" v-model="newObj.refinery" v-on:change="generate">
                      <label class="form-check-label" for="refinery">Raffineriedeck</label>
                    </div>
                  </div>

                  <div class="col-12 col-lg-6 col-xl-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="cargo_area" v-model="newObj.cargo_area" v-on:change="generate">
                      <label class="form-check-label" for="cargo_area">Frachtbereich</label>
                    </div>
                  </div>

                  <div class="col-12 col-lg-6 col-xl-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="black_market" v-model="newObj.black_market" v-on:change="generate">
                      <label class="form-check-label" for="black_market">Schwarzmarkt vorhanden</label>
                    </div>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="merchants">Anzahl Händler</label>
                    <input type="number" class="form-control" id="merchants" v-on:change="generate" v-on:input="generate" v-model="newObj.merchants" min="0" max="20">
                    <small class="form-text text-muted">Anzahl Händler</small>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="services">Anzahl Dienstleister</label>
                    <input type="number" class="form-control" id="services" v-on:change="generate" v-on:input="generate" v-model="newObj.services" min="0" max="50">
                    <small class="form-text text-muted">Anzahl Mietservices, Versicherungen, etc.</small>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="food_services">Gastronomie</label>
                    <input type="number" class="form-control" id="food_services" v-on:change="generate" v-on:input="generate" v-model="newObj.food_services" min="0" max="50">
                    <small class="form-text text-muted">Anzahl Burito Bar, etc.</small>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="paket_stations">Anzahl Paketstation</label>
                    <input type="number" class="form-control" id="paket_stations" v-on:change="generate" v-on:input="generate" v-model="newObj.paket_stations" min="0" max="20">
                    <small class="form-text text-muted">Anzahl Paketstationen</small>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="fine_terminals">Fines & Citations Payment System</label>
                    <input type="number" class="form-control" id="fine_terminals" v-on:change="generate" v-on:input="generate" v-model="newObj.fine_terminals" min="0" max="50">
                    <small class="form-text text-muted">Anzahl Fines & Citations Payment Systems vom Office of Imperial Justice</small>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="col-12 col-lg-6 mx-auto">
          <p>Generierter Wikitext:</p>
          <pre id="out" class="text-monospace">{{out}}</pre>
          <p>Weiter Informationen unter <a href="https://star-citizen.wiki/Vorlage:CelestialObject">Vorlage:CelestialObject</a>. Statt der Vorlage <code>CelestialObject</code> können auch alle weiteren Vorlagen wie <code>Planet</code>, <code>Raumstation</code>, <code>Stadt</code>, etc. verwendet werden.</p>
          <p>Füge diesen Text auf der Seite <a :href="`${wikiPageName}?action=edit`">{{wikiPageName}}</a> ein</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "CelestialObjectGenerator",
  props: {
    apiUrl: String
  },
  data: function () {
    return {
      dataTS: null,
      loading: true,
      out: '',
      hasError: false,
      error: '',
      newObj: {
        type: 'ENTITY',
        control: '',
        code: '',
        name: '',
      },
      isSystemEntity: false,
      starSystems: [],
      selectedSystem: null,
      childObjects: [],
      selectedChild: null,
      hideBox: false,
      noZones: false,
    }
  },
  computed: {
    parent: function () {
      return this.selectedChild.parent ?? this.selectedChild.id
    },
    wikiPageName: function () {
      if (typeof this.newObj.name === 'undefined') {
        return '';
      }

      return `https://star-citizen.wiki/${this.newObj.name.trim().replace(/\s/g, '_')}`
    }
  },
  mounted() {
    this.loading = true;
    axios.get(this.apiUrl, {
      params: {
        action: 'ask',
        query: `[[Kategorie:Sternensystem]][[ID::<500]]|?Starmap Code#-=code|?ID#-=id|limit=250`,
        format: 'json',
        formatversion: 2,
      }
    })
      .then((result) => {
        Object.entries(result.data.query.results).forEach(mapping => {
          this.starSystems.push({
            name: mapping[1]?.printouts?.code[0] ?? null,
            id: mapping[1]?.printouts?.id[0] ?? null,
          })
        });

        this.loading = false;
      })
      .catch((error) => {
        this.hasError = true;
        this.error = error.message;
        console.error(error);
      })
  },
  methods: {
    loadChildren: function () {
      if (this.selectedSystem === null) {
        return;
      }

      this.loading = true;
      this.hasError = false;

      this.childObjects =  [];

      axios.get(this.apiUrl, {
        params: {
          action: 'ask',
          query: `[[Sternensystemid::${this.selectedSystem.id}]]|?Sternensystemid=system_id|?Starmap Code=code|?Bezeichnung=name|?Typ=type|?Kontrolle=control|?ID=id|?Elternid=parent_id|limit=250`,
          format: 'json',
          formatversion: 2,
          maxage: 0,
          smaxage: 0,
          curtimestamp: 1
        }
      })
        .then((result) => {
          const dataDate = (new Date(result.data.curtimestamp));
          this.dataTS = `${dataDate.toLocaleDateString()} ${dataDate.toLocaleTimeString()}`;

          const safeAccess = item => item ?? [];

          Object.entries(result.data.query.results).forEach(mapping => {
            this.childObjects.push({
              name: safeAccess(mapping[1]?.printouts?.name)[0] ?? null,
              id: safeAccess(mapping[1]?.printouts?.id)[0] ?? null,
              system: safeAccess(mapping[1]?.printouts?.system_id)[0] ?? null,
              code: safeAccess(mapping[1]?.printouts.code)[0] ?? null,
              type: safeAccess(mapping[1]?.printouts?.type)[0] ?? null,
              control: safeAccess(mapping[1]?.printouts?.control)[0] ?? null,
              parent: safeAccess(mapping[1]?.printouts?.parent_id)[0] ?? null
            })
          });

          this.childObjects = this.childObjects.sort((a, b) => {
            return a.name.localeCompare(b.name);
          })

          this.loading = false;
        })
        .catch((error) => {
          this.hasError = true;
          this.error = error.message;
          console.error(error);
        })
    },

    updateChildId: function (e) {
      let id = Number.parseInt(`${this.selectedChild.id}1`, 10)

      this.childObjects.forEach(child => {
        if (id === child.id) {
          ++id
        }
      })

      this.childObjects.forEach(child => {
        if (id === child.id) {
          ++id
        }
      })

      this.newObj.id = id;
      this.generate(e);
      this.setCode(e);
    },

    generate: function (e) {
      e.preventDefault();

      this.out = `{{${this.getTemplateType()}
| image =
| Sternensystemid = ${this.selectedSystem.id}
| Starmap Code = ${this.newObj.code}
| Bezeichnung = ${this.newObj.name}
| Typ = ${this.newObj.type}
| Quantum Beacon = ${this.newObj.quantumbeacon ? 'Ja' : 'Nein'}
| Kontrolle = ${this.newObj.control ?? ''}
| Elternid = ${this.selectedChild.id}
| ID = ${this.newObj.id}
| Wirtschaft = ${this.newObj.economy ?? ''}
| Bevölkerungsgröße = ${this.newObj.population ?? ''}
| Gefahrenlage = ${this.newObj.danger ?? ''}${this.addIsMerchant()}${this.addSystemEntityData()}
}}`;
    },
    addIsMerchant: function () {
      if (this.newObj.type !== 'PERSON') {
        return '';
      }

      let out = ''

      if (this.newObj.merchant === true) {
        out = `\n| Händler = Ja\n`
      }

      out = out + (this.hideBox === true ? `\n| Infobox = Nein` : '');

      return out;
    },
    addSystemEntityData: function () {
      if ( !this.isSystemEntity ) {
        return ''
      }

      return `${this.bools()}
| Rechtsraum = ${this.newObj.control_type ?? ''}
${this.hangars()}
| Habitabel Innenbereich = ${(typeof this.newObj.habitable_inner === 'undefined' ? '' : (this.newObj.habitable_inner ? 'Ja' : 'Nein')) }
| Habitabel Außenbereich = ${(typeof this.newObj.habitable_outer === 'undefined' ? '' : (this.newObj.habitable_outer ? 'Ja' : 'Nein')) }
| Temperatur Schattenseite = ${this.newObj.temp_shadow ? `${this.newObj.temp_shadow} °C` : ''}
| Temperatur Sonnenseite = ${this.newObj.temp_sun ? `${this.newObj.temp_sun} °C` : ''}
| Klinik = ${(typeof this.newObj.clinic === 'undefined' ? '' : (this.newObj.clinic ? 'Ja' : 'Nein')) }
| Prägung = ${(typeof this.newObj.imprinting_possible === 'undefined' ? '' : (this.newObj.imprinting_possible ? 'Ja' : 'Nein')) }
| Unterkünfte = ${(typeof this.newObj.accommodations === 'undefined' ? '' : (this.newObj.accommodations ? 'Ja' : 'Nein')) }
| Anzahl Händler = ${this.newObj.merchants ?? ''}
| Anzahl Dienstleister = ${this.newObj.services ?? ''}
| Anzahl Gastronomie = ${this.newObj.food_services ?? ''}
| Anzahl Paketstationen = ${this.newObj.paket_stations ?? ''}
| Schwarzmarkt = ${(typeof this.newObj.black_market === 'undefined' ? '' : (this.newObj.black_market ? 'Ja' : 'Nein')) }
| Frachtbereich = ${(typeof this.newObj.cargo_area === 'undefined' ? '' : (this.newObj.cargo_area ? 'Ja' : 'Nein')) }
| Raffineriestationen = ${(typeof this.newObj.refinery === 'undefined' ? '' : (this.newObj.refinery ? 'Ja' : 'Nein')) }${(this.hideBox === true ? `
| Infobox = Nein` : '')}`
    },
    setCode: function (e) {
      if (typeof this.newObj.name === 'undefined' || this.newObj.name === null) {
        return;
      }

      const str = this.newObj.name.normalize('NFD') // split an accented letter in the base letter and the acent
          .replace(/[\u0300-\u036f]/g, '') // remove all previously split accents
          .toLowerCase()
          .trim()
          .replace(/[^a-z0-9 ]/g, '') // remove all chars not letters, numbers and spaces (to be replaced)
          .replace(/\s+/g, '') // separator
          .toUpperCase();

      this.newObj.code = `${this.selectedChild.code}.${str}`

      this.generate(e);
    },
    hangars: function () {
      if (this.newObj.type === 'PERSON') {
        return ''
      }

      return `| Öffentlicher Transport = ${(typeof this.newObj.public_transport === 'undefined' ? '' : (this.newObj.public_transport ? 'Ja' : 'Nein')) }
| Anzahl Landeplattformen = ${this.newObj.landing_platforms ?? ''}
| Anzahl Hangar = ${this.newObj.hangars ?? ''}
| Anzahl Garagen = ${this.newObj.garages ?? ''}
| Anzahl Dockingstationen = ${this.newObj.docking_stations ?? ''}
| Anzahl Bodenfahrzeugkonsolen = ${this.newObj.vehicle_terminals ?? ''}
| Anzahl Raumschiffkonsolen = ${this.newObj.ship_terminals ?? ''}
| Anzahl Bußgeldzahlungskonsolen = ${this.newObj.fine_terminals ?? ''}`
    },
    bools: function () {
      if (this.noZones || this.newObj.type === 'PERSON') {
        return ''
      }

      return `\n| ATC = ${this.newObj.atc ? 'Ja' : 'Nein'}
| Waffenblockierung = ${this.newObj.weapon_blocked ? 'Ja' : 'Nein'}
| Waffensperrung Innenbereich = ${this.newObj.weapon_zone_inner ? 'Ja' : 'Nein'}
| Waffensperrung Umgebung = ${this.newObj.weapon_zone_outer ? 'Ja' : 'Nein'}
| Überwachte Zone = ${this.newObj.controlled_zone ? 'Ja' : 'Nein'}`
    },
    getTemplateType: function () {
      switch (this.newObj.type) {
        case 'ASTEROID_BELT':
          return 'Asteroidengürtel';
        case 'BINARY':
          return 'Stern';
        case 'SINGLE_STAR':
          return 'Stern';
        case 'LANDINGZONE':
          return 'Landezone';
        case 'SATELLITE':
          return 'Mond';
        case 'PERSON':
          return 'Person';
        case 'PLANET':
          return 'Planet';
        case 'SHOP':
          return 'Shop';
        case 'STAR':
          return 'Stern';
        case 'TOWN':
          return 'Stadt';
        case 'MANMADE':
          return 'Raumstation';
        default:
          return this.isSystemEntity ? 'SystemEntity' : 'CelestialObject'
      }
    },
  }
}
</script>
