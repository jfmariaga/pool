@props(['id', 'extra'])

<div wire:ignore class="card-content collapse show">
    <div class="card-body card-dashboard">
        <table class="table table-striped w-100 {{ $extra ?? '' }}" id="{{ $id }}">
            <thead>
                {{ $slot }}
            </thead>
            <tbody id="body_{{ $id }}">
            </tbody>
        </table>
    </div>
</div>