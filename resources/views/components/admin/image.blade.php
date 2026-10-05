@props([
    'name',
    'label' => null,
    'value' => null,
    'help' => null,
    'allowUrl' => true,
    'contain' => false,
    'fileName' => null,
    'removeName' => null,
])
{{--
    Posts a file as "{name}_file" (or fileName), keeps/sets the stored value in "{name}"
    (hidden or URL input), and "{name}_remove" to clear it.
--}}
@php
    $fileField = $fileName ?? (\Illuminate\Support\Str::endsWith($name, ']') ? substr($name, 0, -1) . '_file]' : $name . '_file');
    $removeField = $removeName ?? (\Illuminate\Support\Str::endsWith($name, ']') ? substr($name, 0, -1) . '_remove]' : $name . '_remove');
    $key = trim(str_replace(['[', ']'], ['.', ''], $fileField), '.');
    $src = \App\Support\Media::url($value);
    $isExternal = $value && \Illuminate\Support\Str::startsWith($value, ['http://', 'https://', '//']);
@endphp
<div class="a-field">
    @if($label)<label class="form-label">{{ $label }}</label>@endif
    <div class="a-image {{ $contain ? 'is-contain' : '' }}">
        <div class="a-image-preview">
            @if($src)<img src="{{ $src }}" alt="">@else<i class="ri-image-line"></i>@endif
        </div>
        <div class="a-image-actions">
            <input type="file" name="{{ $fileField }}" accept="image/*" class="form-control form-control-sm flex-grow-1" style="min-width:0">
            @if($value)
                <label class="form-check m-0 small text-nowrap">
                    <input type="checkbox" name="{{ $removeField }}" value="1" class="form-check-input" data-image-remove> Remove
                </label>
            @endif
        </div>
        @if($allowUrl)
            <input type="text" name="{{ $name }}" value="{{ $value }}" class="form-control form-control-sm mt-2" placeholder="…or paste an image URL / path" data-image-url>
        @else
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endif
    </div>
    @error($key)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    @if($help)<div class="form-text">{!! $help !!}</div>@endif
</div>
