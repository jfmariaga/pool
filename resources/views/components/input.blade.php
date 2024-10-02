@props(['model', 'type', 'label', 'required', 'placeholder', 'dataMask', 'keyup'])

@if( isset( $label ) && $label )
    <label for="">{{ $label }}
        @if ( isset( $required ) && $required )
            <b class="c_red">*</b>
        @endif
    </label>
@endif

<input type="{{ $type ?? 'text' }}" class="form-control" x-model="{{ $model }}" data-mask="{{ $dataMask ?? '' }}" placeholder="{{ $placeholder ?? '' }}" x-on:keyup="{{ $keyup ?? '' }}">
@php
    $model = str_replace('$wire.','',$model)
@endphp
@error( $model ) <span class="c_red">{{ $message }}</span> @enderror