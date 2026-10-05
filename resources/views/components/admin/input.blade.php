@props([
    'name',
    'label' => null,
    'value' => null,
    'type' => 'text',
    'required' => false,
    'help' => null,
    'prefix' => null,
    'errorKey' => null,
])
@php
    $key = $errorKey ?? trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $attributes->get('id', 'f_' . md5($name));
    $current = old($key, $value);
@endphp
<div class="a-field">
    @if($label)
        <label class="form-label" for="{{ $id }}">{{ $label }} @if($required)<span class="req">*</span>@endif</label>
    @endif
    @if($prefix)<div class="input-group">@endif
        @if($prefix)<span class="input-group-text">{!! $prefix !!}</span>@endif
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}"
               value="{{ is_array($current) ? '' : $current }}"
               {{ $required ? 'required' : '' }}
               {{ $attributes->except('id')->merge(['class' => 'form-control' . ($errors->has($key) ? ' is-invalid' : '')]) }}>
    @if($prefix)</div>@endif
    @error($key)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    @if($help)<div class="form-text">{!! $help !!}</div>@endif
</div>
