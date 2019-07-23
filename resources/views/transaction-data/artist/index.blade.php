@extends('layouts.base')
@push('appTitle', 'Artist')
@push('appHeader')
@endpush
@push('appFooter')
<script src="{{ asset('js/transaction-data/artist/manage.js') }}" type="text/javascript"></script>
@endpush
@section('baseContent')
<div id="simple-app"></div>
<div class="h-100 w-100 m-1 d-flex flex-column justify-content-center align-items-center bg-master-light">
    <div class="h-100 w-90 bg-master-lighter">
        <div class="h-100 w-100 d-flex flex-column">
            <div class="m-1">
                <h4>Artist :</h4>
                <h5 id="artist-name"></h5>
                <h4>Songs :</h4>
                <h5 id="song-name"></h5>
            </div>
            <div class="m-1">
                <div class="m-1 bg-master-lighter" id="form-song">
                    <label>Cari artist</label>
                    <div class="form-group">
                        <input type="text" name="artist" class="form-control" id="artist" placeholder="Artist" required>
                    </div>
                </div>
                <h4>Mana yang paling sesuai ???</h4>
            </div>
            <div class="notif"></div>
            <div class="flex-grow-1 scrollable">
                <table class="table table-hover" id="spotifyTable">
                    <thead class="bg-white text-center">
                        <tr>
                            <th>Pilih</th>
                            <th>Nama Artist</th>
                            <th>Foto</th>
                            <th>Popularitas</th>
                            <th>Follower</th>
                            <th>Genre</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="m-1">
                <button class="btn btn-complete btn-cons" id="next">Berikutnya</button>
                <button class="btn btn-complete btn-cons" id="skip">Lewati</button>
            </div>
        </div>
    </div>
</div>
@endsection
