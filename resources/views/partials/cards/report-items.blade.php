@foreach($reports as $report)
    @include('partials.cards.report', ['report' => $report])
@endforeach
