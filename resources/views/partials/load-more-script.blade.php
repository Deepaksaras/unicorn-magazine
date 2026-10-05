{{-- "Load more" button: fetches the next page as JSON {html, next} and appends it to the grid --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('{{ $button ?? 'loadMore' }}');
    var grid = document.getElementById('{{ $grid }}');
    if (!btn || !grid) return;
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        var url = btn.getAttribute('href');
        if (!url) return;
        btn.classList.add('disabled');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                grid.insertAdjacentHTML('beforeend', data.html);
                if (data.next) { btn.setAttribute('href', data.next); btn.classList.remove('disabled'); }
                else { btn.parentElement.remove(); }
            })
            .catch(function () { window.location.href = url; });
    });
});
</script>
@endpush
