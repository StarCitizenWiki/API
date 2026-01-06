@props([
    'id',
    'config' => [],
    'initial' => null, // full API response for the first paint
])

<div
    id="{{ $id }}"
    data-tabulator
    data-tabulator-id="{{ $id }}"
    class="w-full"
></div>

<script type="application/json" id="{{ $id }}-config">@json($config)</script>

@if($initial !== null)
    <script type="application/json" id="{{ $id }}-initial">@json($initial)</script>
@endif
