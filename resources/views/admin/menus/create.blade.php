@extends('layouts.admin')
@section('title', 'New menu')
@section('breadcrumb')
    <a href="{{ route('admin.menus.index') }}">Menus</a> <i class="ri-arrow-right-s-line"></i> New
@endsection
@section('page_title', 'New menu')
@section('content')
<form method="POST" action="{{ route('admin.menus.store') }}">
    @csrf
    <div class="a-card" style="max-width:760px"><div class="a-card-body">
        <x-admin.input name="name" label="Name" :value="$menu->name" required id="menuName" />
        <x-admin.input name="slug" label="Slug" :value="$menu->slug" data-slug-from="#menuName" help="Render it in a template with <span class='a-kbd'>Menus::items('slug')</span>." />
        <x-admin.input name="location" label="Location" :value="$menu->location" />
        <x-admin.textarea name="description" label="Description" :value="$menu->description" rows="2" />
        <x-admin.status :value="$menu->status" />
    </div></div>
    @include('admin.partials.savebar', ['cancel' => route('admin.menus.index'), 'label' => 'Create menu'])
</form>
@endsection
