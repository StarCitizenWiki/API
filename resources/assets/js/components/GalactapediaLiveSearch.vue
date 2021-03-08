<template>
  <div class="card mb-3" id="cl-test">
    <div class="card-header">
      <h4>
        Galactapedia Titelsuche
      </h4>
    </div>
    <div class="card-body">
      <div class="alert alert-danger" v-if="hasError">Fehler bei der Suchabfrage! {{ error }}</div>
      <div class="alert alert-info" v-if="hasNoResult">Keine Artikel zu '{{ term }}' gefunden</div>
      <div class="row">
        <div class="col-6 mx-auto">
          <form method="POST" class="d-flex h-100 flex-column">
            <div class="form-group">
              <label for="keyword" aria-label="keyword">Artikel Titel</label>
              <input type="text" id="keyword" v-on:input="startSearch" min="3" max="255" required class="form-control" />
              <small>Suche nach ganzen Titeln oder Teilwörtern</small>
            </div>
          </form>
        </div>
      </div>
      <table class="table table-striped mb-0" v-if="results.length">
        <thead>
          <tr>
            <th>CIG ID</th>
            <th>Titel</th>
            <th>Typ</th>
            <th>Veröffentlichung</th>
            <th>Links</th>
          </tr>
        </thead>
        <tbody>
          <tr class="col-12" v-for="result in results" :key="result.id">
            <td>{{ result.id }} </td>
            <td>{{ result.title }} </td>
            <td>{{ result.type }} </td>
            <td>{{ (new Date(result.created_at).toLocaleDateString('de-DE', {  year: 'numeric', month: 'numeric', day: 'numeric' })) }} </td>
            <td>
              <div class="btn-group btn-group-sm" role="group" aria-label="">
                <a target="_blank" class="btn btn-outline-secondary" rel="noreferrer noreferrer noopener" :href="`/starcitizen/galactapedia/${result.id}`">API</a>
                <a target="_blank" class="btn btn-outline-secondary" rel="noreferrer noreferrer noopener" :href="result.rsi_url">RSI</a>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: "GalactapediaLiveSearch",
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

      axios.post('/api/galactapedia/search', {
        query: this.term
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
        if (error.status === 404) {
          this.hasNoResult = true;
        } else {
          this.hasError = true;
          this.error = `${error.status}: ${error.message}`;
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
