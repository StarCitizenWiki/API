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
              <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" placeholder="Objektname" v-on:input="setCode" v-model="newObj.name">
                <small class="form-text text-muted">Name des Objekts</small>
              </div>

              <div class="form-group">
                <label for="code">Code</label>
                <input type="text" class="form-control" id="code" disabled v-on:change="generate" :value="newObj.code">
                <small class="form-text text-muted">Der Code wird automatisch anhand des Namens generiert</small>
              </div>

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
                  <option value="TOWN">Stadt</option>
                  <option value="STAR">Stern</option>
                  <option value="STRUCTURE">Struktur</option>
                  <option value="SHELTER">Unterkunft</option>
                </select>
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
                <div class="col-12 col-lg-9">
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
                <div class="col-12 col-lg-3">
                  <div class="form-group">
                    <label for="habitable">Habitabel</label>
                    <input type="checkbox" class="form-control" id="habitable" v-model="newObj.habitable" v-on:change="generate">
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

              <div class="row" v-if="isSystemEntity">
                <div class="col-12">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="zones" checked v-model="noZones" v-on:change="generate">
                    <label class="form-check-label" for="zones">Zonen entfernen</label>
                  </div>
                </div>

                <div class="col-12" v-if="!noZones">
                  <div class="row">
                    <div class="col-12 col-lg-3">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="quantumbeacon" v-model="newObj.quantumbeacon" v-on:change="generate">
                        <label class="form-check-label" for="quantumbeacon">Navigationspunkt</label>
                      </div>
                    </div>

                    <div class="col-12 col-lg-3">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="weapon_zone" v-model="newObj.weapon_zone" v-on:change="generate">
                        <label class="form-check-label" for="weapon_zone">Waffenfreie Zone</label>
                      </div>
                    </div>

                    <div class="col-12 col-lg-3">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="controlled_zone" v-model="newObj.controlled_zone" v-on:change="generate">
                        <label class="form-check-label" for="controlled_zone">Überwachung von Straftaten</label>
                      </div>
                    </div>

                    <div class="col-12 col-lg-3">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="atc" v-model="newObj.atc" v-on:change="generate">
                        <label class="form-check-label" for="atc">Lande- und Startkontrolle</label>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-12 mt-3">
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

                <hr>

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
                    <label for="docking_stations">Dockingsationen</label>
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

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="merchants">Anzahl Händler</label>
                    <input type="number" class="form-control" id="merchants" v-on:change="generate" v-on:input="generate" v-model="newObj.merchants" min="0" max="20">
                    <small class="form-text text-muted">Anzahl Händler</small>
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

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="refineries">Raffineriestationen</label>
                    <input type="text" class="form-control" id="refineries" v-on:change="generate" v-on:input="generate" v-model="newObj.refineries">
                    <small class="form-text text-muted">Anzahl der Verkaufs und Raffineriestationen, z.B. "Verkauf, 2x Raffinerie" zur Angabe von einer Verkaufsstation und 2 Raffineriestationen</small>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-group">
                    <label for="merchants_terminals">Ein- und Verkauf von Handelsware</label>
                    <input type="text" class="form-control" id="merchants_terminals" v-on:change="generate" v-on:input="generate" v-model="newObj.merchants_terminals">
                    <small class="form-text text-muted">z.B. 1x Trading & Shipping Terminal, 1x Admin Office</small>
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
          query: `[[Sternensystemid::${this.selectedSystem.id}]]|?Sternensystemid|?Starmap Code|?Bezeichnung|?Typ|?Kontrolle|?ID|?Elternid|limit=250`,
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

          Object.entries(result.data.query.results).forEach(mapping => {
            this.childObjects.push({
              name: mapping[1]?.printouts['Bezeichnung'][0] ?? null,
              id: mapping[1]?.printouts['ID'][0] ?? null,
              system: mapping[1]?.printouts['Sternensystemid'][0] ?? null,
              code: mapping[1]?.printouts['Starmap Code'][0] ?? null,
              type: mapping[1]?.printouts['Typ'][0] ?? null,
              control: mapping[1]?.printouts['Kontrolle'][0] ?? null,
              parent: mapping[1]?.printouts['Elternid'][0] ?? null
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
| Kontrolle = ${this.newObj.control ?? ''}
| Elternid = ${this.selectedChild.id}
| ID = ${this.newObj.id}
| Wirtschaft = ${this.newObj.economy ?? ''}
| Bevölkerungsgröße = ${this.newObj.population ?? ''}
| Gefahrenlage = ${this.newObj.danger ?? ''}
| Habitabel = ${this.newObj.habitable ? 'Ja' : 'Nein'}${this.addSystemEntityData()}
}}`;
    },
    addSystemEntityData: function () {
      if ( !this.isSystemEntity ) {
        return ''
      }

      if (this.newObj.type === 'Person') {
        return this.hideBox === true ? `\n| Infobox = Nein` : '';
      }

      return `${this.bools()}
| Rechtsraum = ${this.newObj.control_type ?? ''}
${this.hangars()}
| Temperatur Schattenseite = ${this.newObj.temp_shadow ? `${this.newObj.temp_shadow} °C` : ''}
| Temperatur Sonnenseite = ${this.newObj.temp_sun ? `${this.newObj.temp_sun} °C` : ''}
| Anzahl Händler = ${this.newObj.merchants ?? ''}
| Handelsterminals = ${this.newObj.merchants_terminals ?? ''}
| Anzahl Paketstationen = ${this.newObj.paket_stations ?? ''}
| Raffineriestationen = ${this.newObj.refineries ?? ''}${(this.hideBox === true ? `
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

      return `| Anzahl Landeplattformen = ${this.newObj.landing_platforms ?? ''}
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
| Quantum Beacon = ${this.newObj.quantumbeacon ? 'Ja' : 'Nein'}
| Waffenfreie Zone = ${this.newObj.weapon_zone ? 'Ja' : 'Nein'}
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
        case 'PLANET':
          return 'Planet';
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
