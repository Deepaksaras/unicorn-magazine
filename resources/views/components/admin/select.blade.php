@props([
    'name',
    'label' => null,
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'required' => false,
    'help' => null,
    'errorKey' => null,
])
@php
    $key = $errorKey ?? trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $attributes->get('id', 'f_' . md5($name));
    $current = old($key, $value);
    $multiple = $attributes->has('multiple');
    $selected = array_map('strval', (array) $current);
@endphp
<div class="a-field">
    @if($label)
        <label class="form-label" for="{{ $id }}">{{ $label }} @if($required)<span class="req">*</span>@endif</label>
    @endif
    <select name="{{ $name }}" id="{{ $id }}" {{ $required ? 'required' : '' }}
        {{ $attributes->except('id')->merge(['class' => 'form-select' . ($errors->has($key) ? ' is-invalid' : '')]) }}>
        @if($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $optValue => $optLabel)
            @if(is_array($optLabel))
                <optgroup label="{{ $optValue }}">
                    @foreach($optLabel as $v => $l)
                        <option value="{{ $v }}" @selected(in_array((string) $v, $selected, true))>{{ $l }}</option>
                    @endforeach
                </optgroup>
            @else
                <option value="{{ $optValue }}" @selected(in_array((string) $optValue, $selected, true))>{{ $optLabel }}</option>
            @endif
        @endforeach
    </select>
    @error($key)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    @if($help)<div class="form-text">{!! $help !!}</div>@endif
</div>
