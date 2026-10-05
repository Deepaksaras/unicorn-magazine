@extends('layouts.admin')

@php
    $editing = $post->exists;
    $seo = $post->seoMeta;
    $selectedTags = old('tags', $post->tags?->pluck('id')->map(fn ($i) => (string) $i)->all() ?? []);
@endphp

@section('title', $editing ? 'Edit article' : 'New article')
@section('breadcrumb')
    <a href="{{ route('admin.articles.index') }}">Articles</a> <i class="ri-arrow-right-s-line"></i> {{ $editing ? 'Edit' : 'New' }}
@endsection
@section('page_title', $editing ? 'Edit article' : 'New article')
@section('page_actions')
    @if($editing && $post->status == 1)
        <a href="{{ route('article', $post->slug) }}" target="_blank" class="btn btn-soft"><i class="ri-external-link-line me-1"></i> View on site</a>
    @endif
@endsection

@include('admin.partials.editor')

@section('content')
<form method="POST" enctype="multipart/form-data" data-dirty-check
      action="{{ $editing ? route('admin.articles.update', $post) : route('admin.articles.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="a-card">
                <div class="a-card-body">
                    <x-admin.input name="title" label="Title" :value="$post->title" required id="postTitle" class="form-control-lg fw-semibold" />
                    <x-admin.input name="slug" label="URL slug" :value="$post->slug" data-slug-from="#postTitle"
                        prefix="/article/" help="Leave empty to generate it from the title." />
                    <x-admin.textarea name="excerpt" label="Excerpt" :value="$post->excerpt" rows="3" maxlength="500" data-count="500"
                        help="Shown under the title on cards and at the top of the article." />
                    <x-admin.textarea name="content" label="Content" :value="$post->content" rich data-height="520" required />
                </div>
            </div>

            <x-admin.seo :model="$post" :title="$post->title" :description="$post->excerpt ?: $post->content" :url="$post->exists ? route('article', $post->slug) : null" image-help="Shown when the article is shared. Empty = the featured image." />
        </div>

        <div class="col-xl-4">
            <x-admin.short-link :item="$post" />

            <div class="a-card">
                <div class="a-card-head"><h3>Publish</h3></div>
                <div class="a-card-body">
                    <x-admin.status :value="$post->status ?? 1" :labels="['1' => 'Published', '0' => 'Draft']" />
                    <x-admin.input type="datetime-local" name="published_at" label="Publish date"
                        :value="optional($post->published_at)->format('Y-m-d\TH:i')" help="A future date schedules the article." />
                    <x-admin.select name="user_id" label="Author" :value="$post->user_id ?? auth()->id()" :options="$authors" required />
                    <div class="a-divider"></div>
                    <x-admin.toggle name="is_featured" label="Featured story" :checked="$post->is_featured" help="Home hero + “Featured” sidebars." />
                    <x-admin.toggle name="is_trending" label="Trending" :checked="$post->is_trending" />
                    <x-admin.toggle name="is_breaking" label="Breaking" :checked="$post->is_breaking" />
                </div>
            </div>

            <div class="a-card">
                <div class="a-card-head"><h3>Featured image</h3></div>
                <div class="a-card-body">
                    <x-admin.image name="featured_image" :value="$post->featured_image" help="JPG/PNG/WebP up to 5 MB — converted to WebP automatically." />
                </div>
            </div>

            <div class="a-card">
                <div class="a-card-head"><h3>Organise</h3></div>
                <div class="a-card-body">
                    <div class="a-field">
                        <label class="form-label">Category <span class="req">*</span></label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Choose…</option>
                            @foreach($categories->whereNull('parent_id') as $parent)
                                <option value="{{ $parent->id }}" @selected(old('category_id', $post->category_id) == $parent->id)>{{ $parent->name }}</option>
                                @foreach($categories->where('parent_id', $parent->id) as $child)
                                    <option value="{{ $child->id }}" @selected(old('category_id', $post->category_id) == $child->id)>&nbsp;&nbsp;— {{ $child->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <x-admin.input name="badge" label="Card label" :value="$post->badge" placeholder="e.g. INTERVIEW, Funding, Leadership"
                        help="Small label on cards. Empty = category name." />
                    <div class="a-field">
                        <label class="form-label">Tags</label>
                        <select name="tags[]" class="form-select" multiple size="6">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" @selected(in_array((string) $tag->id, $selectedTags, true))>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold Ctrl / ⌘ to pick several.</div>
                        <input type="text" class="form-control form-control-sm mt-2" placeholder="Add new tags, comma separated" id="newTags">
                        <div id="newTagInputs"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="a-savebar">
        <span class="me-auto d-none d-md-inline">Reading time is calculated automatically.</span>
        <a href="{{ route('admin.articles.index') }}" class="btn btn-soft">Cancel</a>
        @unless($editing)
            <button type="submit" name="after" value="new" class="btn btn-soft">Save & add another</button>
        @endunless
        <button type="submit" class="btn btn-ink"><i class="ri-save-3-line me-1"></i> {{ $editing ? 'Update article' : 'Save article' }}</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    (function () {
        var input = document.getElementById('newTags');
        var holder = document.getElementById('newTagInputs');
        if (!input) return;
        input.form.addEventListener('submit', function () {
            holder.innerHTML = '';
            input.value.split(',').map(function (t) { return t.trim(); }).filter(Boolean).forEach(function (t) {
                var h = document.createElement('input');
                h.type = 'hidden'; h.name = 'tags[]'; h.value = t;
                holder.appendChild(h);
            });
        });
    })();
</script>
@endpush
