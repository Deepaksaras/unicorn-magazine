@props([
    'name',
    'label' => null,
    'value' => null,
    'rows' => 3,
    'required' => false,
    'help' => null,
    'rich' => false,
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
    <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}" {{ $required && !$rich ? 'required' : '' }}
        {{ $attributes->except('id')->merge(['class' => 'form-control' . ($rich ? ' js-richtext' : '') . ($errors->has($key) ? ' is-invalid' : '')]) }}>{{ is_array($current) ? implode("\n", $current) : $current }}</textarea>
    @error($key)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    @if($help)<div class="form-text">{!! $help !!}</div>@endif
</div>
