<template>
  <div class="card mb-3" id="cl-test">
    <div class="card-header">
      <h4>
        Star Citizen Item Suche
      </h4>
    </div>
    <div class="card-body">
      <div class="alert alert-danger" v-if="hasError">Fehler bei der Suchabfrage! {{ error }}</div>
      <div class="alert alert-info" v-if="hasNoResult">Kein Item zu '{{ term }}' gefunden</div>
      <div class="row">
        <div class="col-6 mx-auto">
          <form method="POST" class="d-flex h-100 flex-column" v-on:submit.p.prevent="">
            <div class="form-group">
              <label for="keyword" aria-label="keyword">Item Name</label>
              <input type="text" id="keyword" v-on:input="startSearch" min="3" max="255" required class="form-control"/>
              <small>Suche nach ganzen Namen oder Teilwörtern. Ergebnisse werden automatisch nach Eingabe angezeigt.</small>
            </div>
          </form>
        </div>
      </div>
      <table class="table table-striped mb-0" v-if="results.length">
        <thead>
        <tr>
          <th>Name</th>
          <th>Typ</th>
          <th>Hersteller</th>
          <th>Shops</th>
          <th>Version</th>
        </tr>
        </thead>
        <tbody>
        <tr class="col-12" v-for="result in results" :key="result.id">
          <td>{{ result.name }}</td>
          <td>{{ result.type }}</td>
          <td>{{ result.manufacturer }}</td>
          <td>
            <ItemPriceTable :shops="result.shops.data"></ItemPriceTable>
          </td>
          <td>{{ result.version }}</td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import ItemPriceTable from "./ItemPriceTable";

export default {
  name: "ItemLiveSearch",
  components: {ItemPriceTable},
  props: {
    apiToken: String
  },
  data: function () {
    return {
      term: '',
      results: {},
      hasError: false,
      error: '',
      hasNoResult: false,
      searchTimeoutId: null,
    }
  },
  methods: {
    startSearch: function (e) {
      clearTimeout(this.searchTimeoutId)

      this.term = e.target.value

      this.searchTimeoutId = setTimeout(this.search, 200)
    },
    search: function () {
      this.hasNoResult = false;
      this.hasError = false;

      if (this.term === '' || this.term.length < 3) {
        this.results = {}
        return;
      }

      axios.post('/api/items/search?include=shops.items', {
        query: this.term,
        limit: 25
      }, {
        mode: 'no-cors',
        headers: this.apiToken !== null ? {
          'Authorization': 'Bearer ' + this.apiToken
        } : {}
      })
          .then((result) => {
            if (result.data.data.length > 0) {
              this.results = result.data.data;
            } else {
              this.hasNoResult = true;
            }
          })
          .catch((error) => {
            if (error.response.status === 404) {
              this.hasNoResult = true;
            } else {
              this.hasError = true;
              this.error = `${error.response.status}: ${error.message}`;
            }
          })
    }
  }
}
</script>

<style scoped>
table.table {
  margin-left: -1.25rem;
  margin-right: -1.25rem;
  width: calc(100% + 2.5rem);
}
</style>
