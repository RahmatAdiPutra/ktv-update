@extends('layouts.base')
@push('appTitle', 'Youtube')
@push('appHeader')
@endpush
@push('appFooter')
<script src="{{asset('js/youtube/index.js')}}" type="text/javascript"></script>
@endpush
@section('baseContent')
<div class="h-100 w-100 m-1 d-flex flex-column justify-content-center align-items-center bg-master-light">
    <div class="h-100 w-100 d-flex flex-column align-items-center">
        <div class="w-50 m-1">
            <input type="text" name="search" class="form-control" id="search" placeholder="Search" required>
        </div>
        <div class="h-100 w-100 d-flex flex-column align-items-center scrollable">
            <div class="result w-80 d-flex flex-wrap justify-content-between"></div>
        </div>
    </div>
</div>
@endsection
