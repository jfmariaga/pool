
@props(['name', 'label'])

{{-- el label es opcional --}}
@if ( isset( $label ) && $label )
    <label for="">{{ $label }}</label>
@endif

<input type="text" class="form-control w-100" x-model="$wire.{{ $name }}" placeholder="Hola mundo...">
@error( '{{ $name }}' ) <span class="input_error error">{{ $message }}</span>@enderror