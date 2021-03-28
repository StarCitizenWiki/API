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
                </div>
              </div>
            </div>

            <div v-if="selectedChild">
              <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" placeholder="Objektname" v-on:input="setCode" v-model="newObj.name">
              </div>
              <div class="form-group">
                <label for="code">Code</label>
                <input type="text" class="form-control" id="code" disabled v-on:change="generate" :value="newObj.code">
              </div>

              <div class="form-group" >
                <label for="parentSelect">Typ</label>
                <select id="typ" class="form-control" v-model="newObj.type" v-on:change="generate">
                  <option selected value="ENTITY">Entität</option>
                  <option value="SINGLE_STAR">SINGLE_STAR</option>
                  <option value="BINARY">BINARY</option>
                  <option value="JUMPPOINT">JUMPPOINT</option>
                  <option value="PLANET">PLANET</option>
                  <option value="ASTEROID_BELT">ASTEROID_BELT</option>
                  <option value="ASTEROID_FIELD">ASTEROID_FIELD</option>
                  <option value="SATELLITE">SATELLITE</option>
                  <option value="MANMADE">MANMADE</option>
                  <option value="STAR">STAR</option>
                  <option value="BLACKHOLE">BLACKHOLE</option>
                  <option value="POI">POI</option>
                  <option value="TOWN">TOWN</option>
                  <option value="SETTLEMENT">SETTLEMENT</option>
                  <option value="STATE">STATE</option>
                  <option value="ENTITY">ENTITY</option>
                  <option value="STRUCTURE">STRUCTURE</option>
                  <option value="SHELTER">SHELTER</option>
                  <option value="CAVE">CAVE</option>
                </select>
              </div>

              <div class="row">
                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="id">ID</label>
                    <input type="number" class="form-control" id="id" v-model="newObj.id" v-on:change="generate">
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label for="parent">Parent</label>
                    <input type="text" class="form-control" id="parent" :value="parent" disabled>
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
              </div>
            </div>
          </form>
        </div>
        <div class="col-12 col-lg-6 mx-auto">
          <p>Generierter Wikitext:</p>
          <pre id="out" class="text-monospace">{{out}}</pre>
          <p>Weiter Informationen unter <a href="https://star-citizen.wiki/Vorlage:CelestialObject">Vorlage:CelestialObject</a>. Statt der Vorlage <code>CelestialObject</code> können auch alle weiteren Vorlagen wie <code>Planet</code>, <code>Raumstation</code>, <code>Stadt</code>, etc. verwendet werden.</p>
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
      loading: true,
      out: '',
      hasError: false,
      error: '',
      newObj: {
        type: 'ENTITY',
        control: ''
      },
      starSystems: [],
      selectedSystem: null,
      childObjects: [],
      selectedChild: null
    }
  },
  computed: {
    parent: function () {
      return this.selectedChild.parent ?? this.selectedChild.id
    },
  },
  mounted() {
    this.loading = true;
    axios.get(this.apiUrl, {
      mode: 'no-cors',
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
        mode: 'no-cors',
        params: {
          action: 'ask',
          query: `[[Sternensystemid::${this.selectedSystem.id}]]|?Sternensystemid|?Starmap Code|?Bezeichnung|?Typ|?Kontrolle|?ID|?Elternid|limit=250`,
          format: 'json',
          formatversion: 2,
        }
      })
        .then((result) => {
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
      this.out = `{{CelestialObject
| Sternensystemid = ${this.selectedSystem.id}
| Starmap Code = ${this.newObj.code}
| Bezeichnung = ${this.newObj.name}
| Typ = ${this.newObj.type}
| Kontrolle = ${this.newObj.control ?? ''}
| Elternid = ${this.selectedChild.id}
| ID = ${this.newObj.id}
| Wirtschaft = ${this.newObj.economy ?? ''}
| Bevölkerungsgröße = ${this.newObj.population ?? ''}
| Gefahrenalge = ${this.newObj.danger ?? ''}
| Habitabel = ${this.newObj.habitable ? 'Ja' : 'Nein'}
}}`;
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
    }
  }
}
</script>
