<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">@lang('Upload Image')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert d-none"></div>
                <form id="uploadForm">
                    <div class="form-group">
                        <label for="filename">@lang('Dateiname')</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="filename-prefix"></span>
                            </div>
                            <input type="text" class="form-control" id="filename" aria-describedby="filename-prefix" required minlength="3">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">@lang('Beschreibung')</label>
                        <textarea class="form-control" id="description" rows="3" required minlength="10"></textarea>
                        <small id="descriptionHelpBlock" class="form-text text-muted">
                            @lang('Bildbeschreibung. Kann Wikitext enthalten.')
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="categories">@lang('Kategorien')</label>
                        <input type="text" class="form-control" id="categories" required minlength="3">
                        <div id="category-pills">
                            <span class="badge badge-secondary" role="button">@lang('Galerie')</span>
                        </div>
                        <small id="categoriesHelpBlock" class="form-text text-muted">
                            @lang('Liste von Kategorien, getrennt durch ein Komma.<br>Die Kategorie des Comm-Links wird automatisch hinzugefügt.')
                        </small>
                    </div>
                    <input type="hidden" name="image" id="image" />
                    <button type="submit" class="btn btn-primary">@lang('Upload')</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Schließen')</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        initModalButtons();

        document.getElementById('uploadForm').addEventListener('submit', (e) => {
            e.preventDefault()

            const modal = document.getElementById('uploadModal')
            const alert = modal.querySelector('.alert')

            alert.classList.remove('alert-danger', 'alert-success', 'alert-info', 'alert-warning', 'd-none')
            alert.classList.add('alert-info')
            alert.innerHTML = 'Lade hoch...'

            modal.querySelector('.modal-body').style.pointerEvents = 'none'

            axios.post('{{ route('web.user.rsi.comm-links.images.upload-wiki') }}', {
                filename: document.getElementById('filename').value,
                image: document.getElementById('image').value,
                description: document.getElementById('description').value,
                categories: document.getElementById('categories').value
            }).then(response => {
                if (typeof response?.data?.error !== 'undefined') {
                    alert.classList.add('alert-danger')
                    alert.classList.remove('d-none')
                    alert.innerHTML = response.data.error.info
                    console.log(response.data)

                    return;
                }

                if (typeof response?.data?.upload?.warnings !== 'undefined') {
                    alert.classList.add('alert-warning')
                    alert.classList.remove('d-none')
                    alert.innerHTML = 'Konnte Datei nicht hochladen.'

                    if (typeof response?.data?.upload?.warnings?.exists !== 'undefined' || typeof response?.data?.upload?.warnings?.duplicate !== 'undefined' ) {
                        const name = (response?.data?.upload?.warnings?.exists ?? response?.data?.upload?.warnings?.duplicate)
                        const link = `<a href="{!! config('api.wiki_url') !!}/index.php?title=Image:${name}">${name}</a>`
                        alert.innerHTML = `Datei existiert bereits unter <br><code>${link}</code>.`
                    }

                    console.log(response.data)

                    return;
                }

                alert.classList.remove('alert-info')
                alert.classList.add('alert-success')
                alert.innerHTML = `Bild hochgeladen!<br><code><a href="{!! config('api.wiki_url') !!}/index.php?title=Image:${response.data.upload.filename}">${response.data.upload.filename}</a></code>`

                modal.querySelector('#image').value = 0
                //modal.querySelector('#description').value = ''
                //modal.querySelector('#filename').value = ''
                modal.querySelector('#categories').placeholder = ''

                console.log(response.data)
            }).catch(error => {
                alert.classList.add('alert-danger')
                alert.classList.remove('d-none')
                alert.innerHTML = error.message
                console.log(error)
            }).finally(() => {
                modal.querySelector('.modal-body').style.pointerEvents = 'auto'
            })
        });
    });

    /** @type HTMLInputElement */
    const categoryInput = document.getElementById('categories');
    document.querySelectorAll('#category-pills span').forEach(category => {
        category.addEventListener('click', () => {
            let prefix = '';
            if (categoryInput.value.length > 0) {
                prefix = ', ';
            }

            categoryInput.value += `${prefix}${category.textContent}`;
        });
    });

    const initModalButtons = () => {
        document.querySelectorAll('.upload-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                updateModal(e.target.dataset.clId, e.target.dataset.id);
                $('#uploadModal').modal('show');
            })
        })
    }

    const updateModal = (commLinkId, imageId) => {
        const modal = document.getElementById('uploadModal');
        const alert = modal.querySelector('.alert');

        alert.classList.add('d-none');
        alert.classList.remove('alert-danger', 'alert-success', 'alert-info', 'alert-warning');

        modal.querySelector('#image').value = imageId;
        modal.querySelector('#description').value = '';
        modal.querySelector('#filename').value = '';
        modal.querySelector('#filename-prefix').innerHTML = `Comm-Link ${commLinkId}`;
        modal.querySelector('#categories').placeholder = `Comm-Link ${commLinkId},`;
    }
</script>