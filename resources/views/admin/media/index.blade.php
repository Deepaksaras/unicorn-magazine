@extends('layouts.admin')
@section('title', 'Media Library')
@section('breadcrumb', 'Media Library')
@section('page_title', 'Media Library')
@section('page_subtitle', 'Upload images or PDFs and copy their link into any field. Images are resized and converted to WebP.')
@section('content')
<div class="a-card mb-4">
    <div class="a-card-body">
        <form method="POST" action="{{ route('admin.media.upload') }}" enctype="multipart/form-data" id="mediaUpload">
            @csrf
            <label class="a-dropzone d-block" id="dropzone">
                <i class="ri-upload-cloud-2-line"></i>
                <div class="fw-semibold mt-2">Drop files here or click to browse</div>
                <div class="small text-muted">JPG, PNG, WebP, GIF, SVG or PDF · up to 10 MB each</div>
                <input type="file" name="files[]" multiple accept="image/*,.pdf" class="d-none" id="mediaFiles">
            </label>
        </form>
    </div>
</div>

<div class="a-card">
    <x-admin.toolbar placeholder="Search file names…" />
    <div class="a-card-body">
        @if($media->count())
            <div class="a-media-grid">
                @foreach($media as $m)
                    <div class="a-media-item">
                        @if($m->file_type === 'image')
                            <div class="img" style="background-image:url('{{ \App\Support\Media::thumb($m->file_path) }}')"></div>
                        @else
                            <div class="img"><i class="ri-file-pdf-2-line"></i></div>
                        @endif
                        <div class="meta">
                            <strong title="{{ $m->file_name }}">{{ $m->file_name }}</strong>
                            <div class="text-muted">{{ $m->width ? $m->width . '×' . $m->height . ' · ' : '' }}{{ number_format(($m->file_size ?? 0) / 1024) }} KB</div>
                            <div class="d-flex gap-1 mt-2">
                                <button type="button" class="btn btn-soft btn-sm flex-grow-1" data-copy="{{ $m->url }}"><i class="ri-link"></i> Copy URL</button>
                                <x-admin.delete :action="route('admin.media.destroy', $m)" message="Delete {{ $m->file_name }}? Pages using it will show a broken image." />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-admin.empty icon="ri-image-2-line" title="No files yet" text="Upload your first image above." />
        @endif
    </div>
    @include('admin.partials.pagination', ['items' => $media])
</div>
@endsection

@push('scripts')
<script>
(function () {
    var zone = document.getElementById('dropzone'), input = document.getElementById('mediaFiles'), form = document.getElementById('mediaUpload');
    input.addEventListener('change', function () { if (input.files.length) form.submit(); });
    ['dragenter', 'dragover'].forEach(function (ev) { zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('drag'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.remove('drag'); }); });
    zone.addEventListener('drop', function (e) { input.files = e.dataTransfer.files; form.submit(); });
})();
</script>
@endpush
