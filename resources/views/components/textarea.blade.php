@props(['model', 'type', 'label', 'required', 'placeholder', 'rows'])

@if( isset( $label ) && $label )
    <label for="">{{ $label }}
        @if ( isset( $required ) && $required )
            <b class="c_red">*</b>
        @endif
    </label>
@endif

<textarea class="form-control" x-model="{{ $model }}" placeholder="{{ $placeholder ?? '' }}"cols="30" rows="{{ $rows ?? 5 }}"></textarea>
@php
    $model = str_replace('$wire.','',$model)
@endphp
@error( $model ) <span class="c_red">{{ $message }}</span> @enderror
