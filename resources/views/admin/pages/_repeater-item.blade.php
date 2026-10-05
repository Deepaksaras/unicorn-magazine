<div class="a-repeater-item">
    <div class="a-repeater-head">
        <i class="ri-draggable handle" title="Drag to reorder"></i>
        <span class="num">#</span>
        <span class="title" data-default="{{ $itemLabel }}">{{ $itemLabel }}</span>
        <button type="button" class="btn btn-soft btn-icon btn-sm" data-repeater-move="up" title="Move up"><i class="ri-arrow-up-line"></i></button>
        <button type="button" class="btn btn-soft btn-icon btn-sm" data-repeater-move="down" title="Move down"><i class="ri-arrow-down-line"></i></button>
        <button type="button" class="btn btn-soft btn-icon btn-sm" data-repeater-toggle title="Collapse"><i class="ri-arrow-up-s-line"></i></button>
        <button type="button" class="btn btn-soft-danger btn-icon btn-sm" data-repeater-remove title="Remove"><i class="ri-close-line"></i></button>
    </div>
    <div class="a-repeater-body">
        <div class="row g-3">
            @foreach($field['fields'] as $subKey => $subField)
                @php $wide = in_array($subField['type'], ['richtext', 'textarea', 'image']) || count($field['fields']) <= 2 && $subField['type'] !== 'icon'; @endphp
                <div class="{{ $wide ? 'col-12' : 'col-md-6' }}">
                    @include('admin.pages._field', [
                        'field' => $subField,
                        'name' => $name . '[' . $index . '][' . $subKey . ']',
                        'value' => $row[$subKey] ?? null,
                    ])
                </div>
            @endforeach
        </div>
    </div>
</div>
