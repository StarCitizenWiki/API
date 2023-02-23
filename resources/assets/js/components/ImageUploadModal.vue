<template>
  <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="uploadModalLabel">Upload Image</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="alert d-none"></div>
          <form id="uploadForm">
            <div class="form-group">
              <label for="filename">Dateiname</label>
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="filename-prefix"></span>
                </div>
                <input type="text" class="form-control" id="filename" aria-describedby="filename-prefix" required minlength="3">
              </div>
            </div>
            <div class="form-group">
              <label for="description">Beschreibung</label>
              <textarea class="form-control" id="description" rows="3" required minlength="10"></textarea>
              <small id="descriptionHelpBlock" class="form-text text-muted">
                Bildbeschreibung. Kann Wikitext enthalten.
              </small>
            </div>
            <div class="form-group">
              <label for="categories">Kategorien</label>
              <input type="text" class="form-control" id="categories" required minlength="3">
              <div id="category-pills">
                <span class="badge badge-secondary" role="button">Galerie</span>
              </div>
              <small id="categoriesHelpBlock" class="form-text text-muted">
                Liste von Kategorien, getrennt durch ein Komma.<br>Die Kategorie des Comm-Links wird automatisch hinzugefügt.
              </small>
            </div>
            <input type="hidden" name="image" id="image" />
            <button type="submit" class="btn btn-primary">Upload</button>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Schließen</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ImageUploadModal",
  mounted() {
    document.querySelectorAll('.upload-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        updateModal(e.target.dataset.clId, e.target.dataset.id);
        $('#uploadModal').modal('show');
      })
    });
  }
}
</script>

<style scoped>

</style>