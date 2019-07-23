@extends('layouts.app')
@section('no-copyright','yes')
@push('bodyClass','fixed-header')
@push('appHeader')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="{{ asset('plugins/jquery-datatable/media/css/dataTables.bootstrap.min.css') }}" rel="stylesheet"
    type="text/css" />
<link href="{{ asset('plugins/jquery-datatable/extensions/FixedColumns/css/dataTables.fixedColumns.min.css') }}"
    rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/datatables-responsive/css/datatables.responsive.css') }}" rel="stylesheet" type="text/css"
    media="screen" />
<link href="{{ asset('plugins/jquery-menuclipper/jquery.menuclipper.css') }}" rel="stylesheet" type="text/css" />
@endpush
@prepend('appFooter')
<script src="{{ asset('plugins/jquery.number.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/jquery-datatable/media/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/jquery-datatable/extensions/TableTools/js/dataTables.tableTools.min.js') }}"
    type="text/javascript"></script>
<script src="{{ asset('plugins/jquery-datatable/media/js/dataTables.bootstrap.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/jquery-datatable/extensions/Bootstrap/jquery-datatable-bootstrap.js') }}"
    type="text/javascript"></script>
<script src="{{ asset('plugins/datatables-responsive/js/datatables.responsive.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/datatables-responsive/js/lodash.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/moment/moment-with-locales.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/moment/livestamp.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/jquery-menuclipper/jquery.menuclipper.js') }}"></script>
@endprepend
@section('appContent')
<div class="vh-100 w-100">
    <div class="h-100 w-100 d-flex flex-column">
        <div class="d-flex flex-row">
            <div class="w-50 bg-master-light">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('artist.index')}}">Artist</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('song.index')}}">Song</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('statistic.index')}}">Height Score</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('logout')}}">Logout</a>
                    </li>
                </ul>
            </div>
            <div class="w-50 bg-master-light">
                <div class="p-r-20 text-right">
                    <h5>{{ Auth::user()->user_name }}</h5>
                </div>
            </div>
        </div>
        <div class="flex-grow-1 d-flex flex-column justify-content-start align-items-center">
            @yield('baseContent')
        </div>
    </div>
</div>
@endsection
