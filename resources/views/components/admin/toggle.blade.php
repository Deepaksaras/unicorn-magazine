@props([
    'name',
    'label',
    'checked' => false,
    'help' => null,
])
@php
    $key = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $attributes->get('id', 'f_' . md5($name));
    $isOn = (bool) old($key, $checked);
    if (old('_token') !== null) { $isOn = (bool) old($key); }
@endphp
<div class="a-field">
    <input type="hidden" name="{{ $name }}" value="0">
    <div class="form-check form-switch d-flex align-items-center gap-2 ps-0 m-0">
        <input class="form-check-input ms-0" type="checkbox" role="switch" name="{{ $name }}" id="{{ $id }}" value="1" @checked($isOn) {{ $attributes->except('id') }}>
        <label class="form-check-label fw-semibold" for="{{ $id }}" style="font-size:13.5px">{{ $label }}</label>
    </div>
    @if($help)<div class="form-text ms-0 mt-1">{!! $help !!}</div>@endif
</div>
