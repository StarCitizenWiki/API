<div class="modal fade" id="whitelist-modal" tabindex="-1" role="dialog" aria-labelledby="whitelist-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="whitelist-modal-label">@lang('Erlaubte Domains')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul>
                    @foreach($whitelistedUrls as $whitelistedUrl)
                        <li>{{ $whitelistedUrl->url }}</li>
                    @endforeach
                </ul>
                <hr>
                <a href="mailto:api@star-citizen.wiki?subject=RSI.IM URL Whitelist Request&body=Whitelist Request for the following Domain(s):">@lang('Domain hinzufügen')</a>
            </div>
        </div>
    </div>
</div>