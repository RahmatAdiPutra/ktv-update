@extends('layouts.base')
@push('appTitle', 'Song')
@push('appHeader')
@endpush
@push('appFooter')
<script src="{{asset('js/transaction-data/song/manage.js')}}" type="text/javascript"></script>
@endpush
@section('baseContent')
<div class="h-100 w-100 m-1 d-flex flex-column justify-content-center align-items-center bg-master-light">
    <div class="h-100 w-100 d-flex flex-row">
        <div class="w-50 d-flex flex-column">
            <div class="scrollable m-1">
                <table class="table table-hover table-condensed" id="detailedTable">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th>Title</th>
                            <th>Artist</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div class="w-50 d-flex flex-column">
            <div class="h-20 bg-master-lighter" id="video">
                <video class="h-100 w-100" controls>
                    <source src="" type="video/mp4">
                </video>
            </div>
            <div class="h-80 d-flex flex-column">
                <div class="m-1 notif"></div>
                <div class="m-1 bg-master-lighter" id="form-song">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" id="title" placeholder="Title" required>
                    </div>
                    <div class="form-group">
                        <label>Artist</label>
                        <input type="text" name="artist" class="form-control" id="artist" placeholder="Artist" required>
                    </div>
                </div>
                <div class="m-1 bg-master-lighter flex-grow-1 scrollable">
                    <div id="spotify">
                        <table class="table table-hover" id="spotifyTable">
                            <thead class="text-center">
                                <tr>
                                    <th>Pilih</th>
                                    <th>Title</th>
                                    <th>Artist</th>
                                    <th>Cover</th>
                                    <th>Popularity</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="m-1 text-right">
                    <button class="btn btn-complete btn-cons" id="save-song">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
