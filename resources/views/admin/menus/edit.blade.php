@extends('layouts.admin')
@php $isSystem = isset($system[$menu->slug]); @endphp
@section('title', $menu->name)
@section('breadcrumb')
    <a href="{{ route('admin.menus.index') }}">Menus</a> <i class="ri-arrow-right-s-line"></i> {{ $menu->name }}
@endsection
@section('page_title', $menu->name)
@section('page_subtitle', $system[$menu->slug] ?? $menu->description)
@section('page_actions')
    @unless($isSystem)
        <x-admin.delete :action="route('admin.menus.destroy', $menu)" label="Delete menu" message="Delete this menu and its links?" size="md" />
    @endunless
@endsection

@section('content')
<datalist id="linkSuggestions">
    @foreach($suggestions as $group => $links)
        @foreach($links as $label => $url)<option value="{{ $url }}">{{ $group }}: {{ $label }}</option>@endforeach
    @endforeach
</datalist>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="a-card">
            <div class="a-card-head">
                <div><h3>Links</h3><div class="a-card-sub">Drag <i class="ri-draggable"></i> to reorder — saved instantly.</div></div>
            </div>
            @if($items->count())
                <div class="table-responsive">
                    <table class="a-table">
                        <thead><tr><th style="width:40px"></th><th>Label</th><th>URL</th><th>Status</th><th></th></tr></thead>
                        <tbody data-sortable-url="{{ route('admin.menus.items.reorder', $menu) }}">
                        @foreach($items as $item)
                            <tr data-id="{{ $item->id }}">
                                <td><i class="ri-draggable handle text-muted" style="cursor:grab;font-size:18px"></i></td>
                                <td class="a-title-cell"><strong>{{ $item->title }}</strong>@if($item->target === '_blank')<small>Opens in new tab</small>@endif</td>
                                <td><span class="a-kbd">{{ $item->url }}</span></td>
                                <td><x-admin.pill :status="$item->status" on="Visible" off="Hidden" /></td>
                                <td><div class="a-row-actions">
                                    <button type="button" class="btn btn-soft btn-icon btn-sm" data-bs-toggle="collapse" data-bs-target="#editItem{{ $item->id }}" title="Edit"><i class="ri-pencil-line"></i></button>
                                    <x-admin.delete :action="route('admin.menus.items.destroy', [$menu, $item])" message="Remove “{{ $item->title }}” from this menu?" />
                                </div></td>
                            </tr>
                            <tr class="collapse" id="editItem{{ $item->id }}">
                                <td colspan="5" style="background:#fafafb">
                                    <form method="POST" action="{{ route('admin.menus.items.update', [$menu, $item]) }}" class="row g-2 align-items-end">
                                        @csrf @method('PUT')
                                        <div class="col-md-3"><label class="form-label small">Label</label><input name="title" value="{{ $item->title }}" class="form-control form-control-sm" required></div>
                                        <div class="col-md-4"><label class="form-label small">URL</label><input name="url" value="{{ $item->url }}" class="form-control form-control-sm" list="linkSuggestions" required></div>
                                        <div class="col-md-2"><label class="form-label small">Open in</label>
                                            <select name="target" class="form-select form-select-sm"><option value="_self" @selected($item->target !== '_blank')>Same tab</option><option value="_blank" @selected($item->target === '_blank')>New tab</option></select></div>
                                        <div class="col-md-2"><label class="form-label small">Status</label>
                                            <select name="status" class="form-select form-select-sm"><option value="1" @selected($item->status == 1)>Visible</option><option value="0" @selected($item->status == 0)>Hidden</option></select></div>
                                        <input type="hidden" name="css_class" value="{{ $item->css_class }}">
                                        <div class="col-md-1"><button class="btn btn-ink btn-sm w-100">Save</button></div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-admin.empty icon="ri-links-line" title="No links yet" text="Add the first link using the form." />
            @endif
        </div>
    </div>

    <div class="col-xl-4">
        <div class="a-card">
            <div class="a-card-head"><h3>Add a link</h3></div>
            <div class="a-card-body">
                <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}">
                    @csrf
                    <x-admin.input name="title" label="Label" required placeholder="About Us" />
                    <x-admin.input name="url" label="URL" required placeholder="/about" list="linkSuggestions" help="Start typing — pages and categories are suggested. External links need https://" />
                    <x-admin.select name="target" label="Open in" value="_self" :options="['_self' => 'Same tab', '_blank' => 'New tab']" />
                    <input type="hidden" name="status" value="1">
                    <button class="btn btn-ink w-100"><i class="ri-add-line me-1"></i> Add link</button>
                </form>
            </div>
        </div>

        <div class="a-card">
            <div class="a-card-head"><h3>Menu settings</h3></div>
            <div class="a-card-body">
                <form method="POST" action="{{ route('admin.menus.update', $menu) }}">
                    @csrf @method('PUT')
                    <x-admin.input name="name" label="Name / column heading" :value="$menu->name" required help="Footer columns use this as their heading." />
                    <x-admin.input name="slug" label="Slug" :value="$menu->slug" :readonly="$isSystem" />
                    <input type="hidden" name="location" value="{{ $menu->location }}">
                    <x-admin.textarea name="description" label="Description" :value="$menu->description" rows="2" />
                    <x-admin.status :value="$menu->status" :labels="['1' => 'Visible', '0' => 'Hidden']" />
                    <button class="btn btn-soft w-100">Save settings</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
