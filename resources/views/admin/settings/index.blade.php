@extends('layouts.admin')
@php $tab = request('tab', array_key_first($schema)); @endphp
@section('title', 'Settings')
@section('breadcrumb', 'Settings')
@section('page_title', 'Settings')
@section('page_subtitle', 'Site-wide details used in the header, footer, pop-ups, contact blocks and SEO.')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" data-dirty-check>
    @csrf
    @method('PUT')
    <input type="hidden" name="tab" value="{{ $tab }}" id="settingsTab">

    <div class="row g-4">
        <div class="col-lg-3">
            <div class="a-card p-2 a-section-nav">
                <div class="nav flex-column" role="tablist">
                    @foreach($schema as $key => $group)
                        <a href="#tab-{{ $key }}" data-bs-toggle="tab" data-tab="{{ $key }}" class="{{ $tab === $key ? 'active' : '' }}" role="tab"
                           style="{{ $tab === $key ? 'background:#f4f5f7' : '' }}">
                            <i class="{{ $group['icon'] }}" style="color:var(--gold-600)"></i> {{ $group['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="tab-content">
                @foreach($schema as $key => $group)
                    <div class="tab-pane fade {{ $tab === $key ? 'show active' : '' }}" id="tab-{{ $key }}" role="tabpanel">
                        <div class="a-card">
                            <div class="a-card-head"><h3><i class="{{ $group['icon'] }} me-2" style="color:var(--gold-600)"></i>{{ $group['label'] }}</h3></div>
                            <div class="a-card-body">
                                <div class="row g-3">
                                    @foreach($group['fields'] as $field => $def)
                                        @php [$label, $type, $default] = $def; $help = $def[3] ?? null; $value = $values[$field] ?? $default; @endphp
                                        <div class="{{ in_array($type, ['textarea', 'code']) ? 'col-12' : 'col-md-6' }}">
                                            @if($type === 'image')
                                                <x-admin.image :name="$field" :label="$label" :value="$values[$field] ?? null" contain :help="$help" />
                                            @elseif($type === 'textarea')
                                                <x-admin.textarea :name="$field" :label="$label" :value="$value" rows="3" :help="$help" />
                                            @elseif($type === 'code')
                                                <x-admin.textarea :name="$field" :label="$label" :value="$value" rows="5" class="font-monospace small" :help="$help" />
                                            @else
                                                <x-admin.input :type="$type === 'url' ? 'url' : ($type === 'email' ? 'email' : 'text')" :name="$field" :label="$label" :value="$value" :help="$help" />
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @include('admin.partials.savebar', ['cancel' => route('admin.dashboard'), 'label' => 'Save settings', 'note' => 'All tabs are saved together.'])
</form>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-tab]').forEach(function (a) {
    a.addEventListener('shown.bs.tab', function () {
        document.getElementById('settingsTab').value = a.dataset.tab;
        document.querySelectorAll('[data-tab]').forEach(function (b) { b.style.background = ''; });
        a.style.background = '#f4f5f7';
    });
});
</script>
@endpush
