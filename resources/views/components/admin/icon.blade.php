@props(['name', 'label' => 'Icon', 'value' => null])
<div class="a-field">
    <label class="form-label">{{ $label }}</label>
    <div class="input-group">
        <span class="input-group-text a-icon-preview"><i class="{{ $value ?: 'ri-question-line' }}"></i></span>
        <input type="text" name="{{ $name }}" value="{{ $value }}" class="form-control" placeholder="ri-rocket-2-line" data-icon-input>
    </div>
    <div class="form-text">Any <a href="https://remixicon.com" target="_blank" rel="noopener">Remix Icon</a> class, e.g. <span class="a-kbd">ri-team-line</span></div>
</div>
