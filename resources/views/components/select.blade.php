@props(['model', 'label', 'required', 'no_search', 'id'])

<div wire:ignore>
    @if( isset( $label ) && $label )
        <label for="">{{ $label }}
            @if ( isset( $required ) && $required )
                <b class="c_red">*</b>
            @endif
        </label>
    @endif
    <select class="form-control select2" x-model="{{ $model }}" id="{{$id}}" data-minimum-results-for-search="{{ $no_search ?? '' }}">
        {{ $slot }}
    </select>
</div>
@php
    $model = str_replace('$wire.','',$model)
@endphp
@error( $model ) <span class="c_red">{{ $message }}</span> @enderror
