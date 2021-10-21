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
          <form method="POST" class="d-flex h-100 flex-column">
            <div class="form-group">
              <label for="keyword" aria-label="keyword">Item Name</label>
              <input type="text" id="keyword" v-on:input="startSearch" min="3" max="255" required class="form-control"/>
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
          <!--            <th>Links</th>-->
        </tr>
        </thead>
        <tbody>
        <tr class="col-12" v-for="result in results" :key="result.id">
          <!--            <td><a :href="result.uuid">{{ result.name }}</a></td>-->
          <td>{{ result.name }}</td>
          <td>{{ result.type }}</td>
          <td>{{ result.manufacturer }}</td>
          <td v-html="formatShops(result)"></td>
          <td>{{ result.version }}</td>
          <!--            <td>
                        <div class="btn-group btn-group-sm" role="group" aria-label="">
                          <a target="_blank" class="btn btn-outline-secondary" rel="noreferrer noreferrer noopener" :href="`${result.uuid}`">Details</a>
                        </div>
                      </td>-->
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: "ItemLiveSearch",
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

      axios.post('/api/items/search?include=shops', {
        query: this.term,
        limit: 50
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
    },
    formatShops: function (data) {
      if (typeof data.shops.data === 'undefined') {
        return '-';
      }

      return data.shops.data.map(shop => shop.name_raw).join('<br>');
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
