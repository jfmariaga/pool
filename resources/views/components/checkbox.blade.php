@props(['model', 'label', 'required'])

<div class="switch-container">
    @if (isset($label) && $label)
        <label class="form-check-label label-switch" for="{{ $model }}">{{ $label }}</label>
        @if (isset($required) && $required)
            <b class="c_red">*</b>
        @endif
    @endif
    <label class="switch">
        <input class="form-check-input"  type="checkbox" x-model="{{ $model }}">
        <span class="slider round"></span>
    </label>
</div>

@php
    $model = str_replace('$wire.','',$model)
@endphp

@error($model)
    <span class="c_red">{{ $message }}</span>
@enderror

