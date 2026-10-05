{{-- Renders one blueprint field. Vars: $field, $name, $value, $categories --}}
@php $label = $field['label'] ?? ''; $help = $field['help'] ?? null; @endphp

@switch($field['type'])
    @case('textarea')
        <x-admin.textarea :name="$name" :label="$label" :value="$value" rows="3" :help="$help" />
        @break

    @case('richtext')
        <x-admin.textarea :name="$name" :label="$label" :value="$value" rich data-height="260" :help="$help" />
        @break

    @case('image')
        <x-admin.image :name="$name" :label="$label" :value="$value" :help="$help" />
        @break

    @case('icon')
        <x-admin.icon :name="$name" :label="$label" :value="$value" />
        @break

    @case('number')
        <x-admin.input type="number" step="any" :name="$name" :label="$label" :value="$value" :help="$help" />
        @break

    @case('email')
        <x-admin.input type="email" :name="$name" :label="$label" :value="$value" :help="$help" />
        @break

    @case('url')
        <x-admin.input :name="$name" :label="$label" :value="$value" :help="$help ?? 'A path like /about or a full https:// link.'" />
        @break

    @case('lines')
        <x-admin.textarea :name="$name" :label="$label" :value="implode(PHP_EOL, (array) $value)" rows="4" :help="$help ?? 'One item per line.'" />
        @break

    @case('category')
        <x-admin.select :name="$name" :label="$label" :value="$value" :options="$categories" placeholder="— choose a category —" :help="$help" />
        @break

    @case('toggle')
        <x-admin.toggle :name="$name" :label="$label" :checked="(bool) $value" :help="$help" />
        @break

    @case('repeater')
        @php $rows = array_values((array) $value); $itemLabel = $field['item_label'] ?? 'Item'; @endphp
        <div class="a-field" data-repeater>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label m-0">{{ $label }}</label>
                <button type="button" class="btn btn-soft btn-sm" data-repeater-add><i class="ri-add-line"></i> Add {{ strtolower($itemLabel) }}</button>
            </div>

            <div data-repeater-list>
                @foreach($rows as $i => $row)
                    @include('admin.pages._repeater-item', ['index' => $i, 'row' => (array) $row])
                @endforeach
            </div>
            <div class="text-muted small {{ count($rows) ? 'd-none' : '' }}" data-repeater-empty>No {{ strtolower(\Illuminate\Support\Str::plural($itemLabel)) }} yet.</div>

            <template>
                @include('admin.pages._repeater-item', ['index' => '__INDEX__', 'row' => []])
            </template>
        </div>
        @break

    @default
        <x-admin.input :name="$name" :label="$label" :value="$value" :help="$help" />
@endswitch
