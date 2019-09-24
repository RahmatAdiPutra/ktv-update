@extends('layouts.base')
@push('appTitle', 'Song')
@push('appHeader')
@endpush
@push('appFooter')

<script>
    window.dataSong = {!! json_encode($all) !!};
    window.ALLOW_EDIT = {{ in_array(auth()->id(), explode(',', env('ROLE_EDIT'))) ? 0 : 1 }};
    window.KTV_SERVER = 'http://<?php echo env('KTV_SERVER') ?>/';
</script>
<script src="{{asset('js/transaction-data/song/manage.js')}}" type="text/javascript"></script>
@endpush
@section('baseContent')
<div class="h-100 w-100 m-1 d-flex flex-column justify-content-center align-items-center bg-master-light">
    <div class="h-100 w-100 d-flex flex-row">
        <div class="w-50 d-flex flex-column">
            <div class="d-flex flex-row justify-content-between">
                <div class="m-1">
                    <div class="p-1">
                        <label>Jump to page</label>
                        <input type="number" id="jumppage" min="1" style="width:50px;">
                    </div>
                </div>
                <div class="m-1">
                    <div class="p-1">
                        <label>Status</label>
                        <select name="check_updated" id="check_updated" data-init-plugin="select2" required></select>
                    </div>
                </div>
                <div class="m-1">
                    <div class="p-1">
                        <label>Languages</label>
                        <select name="language_id" id="language_id" data-init-plugin="select2" required></select>
                    </div>
                </div>
            </div>
            <div class="scrollable m-1">
                <table class="table table-hover table-condensed" id="detailedTable">
                    <thead>
                        <tr>
                            <th style="width: 8%;">No</th>
                            <th>Title</th>
                            <th>Artist</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 20%;">Action</th>
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
                    <div class="d-flex flex-row">
                        <div class="w-50 form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" id="title" placeholder="Title" required>
                        </div>
                        <div class="w-50 form-group">
                            <label>Artist</label>
                            <input type="text" name="artist" class="form-control" id="artist" placeholder="Artist" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Genre</label>
                        <select name="song_genre_id" class="full-width" id="song_genre_id" data-init-plugin="select2" required>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Title Non Latin</label>
                        <input type="text" name="title_non_latin" class="form-control" id="title_non_latin" placeholder="Title Non Latin">
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
<!-- MODAL FORM -->
<div class="modal fade fill-in" id="modalFormSong" tabindex="-1" role="dialog" aria-labelledby="modalFormSongLabel"
    aria-hidden="true">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-close"></i></button>
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="text-left p-b-5"><span class="semi-bold">Song</span></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">

                                <form method="POST" id="form-song-modal" enctype="multipart/form-data" autocomplete="off">
                                    @csrf

                                    <input type="text" name="id" class="form-control" id="id" hidden>

                                    <div class="row">

                                        <div class="form-group col-md-6">
                                            <label>Title</label>
                                            <input type="text" name="title" class="form-control" id="title" placeholder="Title" required>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Title non latin</label>
                                            <input type="text" name="title_non_latin" class="form-control" id="title_non_latin" placeholder="Title non latin">
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="artist form-group col-md-6">
                                            <label>Artist</label>
                                            <select name="artist_id" multiple class="full-width" id="artist_id" data-init-plugin="select2" required></select>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Artist Label</label>
                                            <input type="text" name="artist_lab" class="form-control" id="artist_lab" placeholder="Artist Label" required>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="form-group col-md-6">
                                            <label>Genre</label>
                                            <select name="genre_id" class="full-width" id="genre_id" data-init-plugin="select2" required></select>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Language</label>
                                            <select name="song_language_id" class="full-width" id="song_language_id" data-init-plugin="select2" required></select>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="form-group col-md-6">
                                            <label>Type</label>
                                            <select name="type" class="full-width" id="type" data-init-plugin="select2" required></select>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Audio channel</label>
                                            <select name="audio_channel" class="full-width" id="audio_channel" data-init-plugin="select2" required></select>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="form-group col-md-6">
                                            <label>Volume</label>
                                            <input type="text" name="volume" class="form-control" id="volume" placeholder="Volume" value="80" required>
                                        </div>

                                        <div class="checkbox check-success col-md-6 p-t-25">
                                            <input type="checkbox" name="is_new_song" id="is_new_song">
                                            <label for="is_new_song">New Song</label>
                                        </div>

                                    </div>

                                    
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-complete">Save</button>
                                        <button class="btn btn-dark" data-dismiss="modal" aria-hidden="true">Cancel</button>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- END MODAL FORM -->
<!-- MODAL CONFIRM  -->
<div class="modal fade stick-up" id="modalConfirm" tabindex="-1" role="dialog" aria-labelledby="modalConfirmLabel" aria-hidden="true" data-id="">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header clearfix text-left">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="pg-close fs-14"></i>
                </button>
                <h5><span class="semi-bold">Are you sure, you want to delete ?</span></h5>
            </div>
            <div class="modal-body">
                <div class="form-group">
                  <button class="btn btn-dark" data-dismiss="modal" aria-hidden="true">No</button>
                  <button class="btn btn-complete">Yes</button>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- END MODAL CONFIRM  -->
@endsection
