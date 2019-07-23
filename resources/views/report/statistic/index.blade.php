@extends('layouts.base')
@push('appTitle', 'Leaderboard')
@push('appHeader')
@endpush
@push('appFooter')
<script src="{{ asset('js/report/statistic.js') }}" type="text/javascript"></script>
@endpush
@section('baseContent')
<div id="simple-app"></div>
<div class="h-100 w-100 m-1 d-flex flex-column justify-content-center align-items-center bg-master-light">
    <div class="h-100 w-100 d-flex flex-row">
        <div class="w-100 d-flex flex-column">
            <div class="scrollable m-1">
                <table class="table table-hover" id="statisticTable">
                    <thead class="bg-white text-center">
                        <tr>
                            <th>Rank</th>
                            <th>Nama</th>
                            <th>Artist</th>
                            <th>Song</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
