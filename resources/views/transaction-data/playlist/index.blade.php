@extends('layouts.base')
@push('appTitle', 'Playlist')
@push('appHeader')
@endpush
@push('appFooter')

<script>
    window.allData = {!! json_encode($all) !!};
    window.KTV_SERVER = 'http://<?php echo env('KTV_SERVER') ?>/';
</script>
<script src="{{asset('js/transaction-data/playlist/manage.js')}}" type="text/javascript"></script>
@endpush
@section('baseContent')
<div class="h-100 w-100 m-1 d-flex flex-column justify-content-center align-items-center bg-master-light">
    <div class="h-100 w-100 d-flex flex-row">
        <div class="w-40 d-flex flex-column">
            <div class="m-2 text-right">
                <button class="btn btn-complete btn-cons" id='add-playlist' data-target='#modalFormPlaylist' data-toggle='modal'>
                    New Playlist
                </button>
            </div>
            <div class="m-1 scrollable">
                <table class="table table-hover table-condensed" id="tablePlaylist">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Delete</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Song</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div class="w-30 d-flex flex-column">
            <div class="h-100 d-flex flex-column">
                <div class="m-1 notif"></div>
                <div class="m-1 bg-master-lighter">
                    <form method="POST" id="form-playlist-song" enctype="multipart/form-data" autocomplete="off">
                        @csrf

                        <input type="text" name="id" class="form-control" id="id" hidden>

                        <div class="row">

                            <div class="form-group col-md-12">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" id="name" placeholder="Name" required>
                            </div>

                        </div>

                        <div class="row">

                            <div class="form-group col-md-12">
                                <label>Category</label>
                                <select name="playlist_category_id" class="full-width" id="playlist_category_id" data-init-plugin="select2" required>
                                </select>
                            </div>

                        </div>
                        
                        <button type="submit" hidden>Save</button>

                    </form>
                </div>
                <div class="m-1 bg-master-lighter flex-grow-1 scrollable">
                    <div id="playlistSong">
                        <table class="table table-hover" id="tablePlaylistSong">
                            <thead class="text-center">
                                <tr>
                                    <th style="width: 15%;">Delete</th>
                                    <th>Title</th>
                                    <th>Artist</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="m-1">
                    <button class="btn btn-complete btn-cons" id="save-playlist-song">Save</button>
                    <button class="btn btn-dark" id="clear-form-playlist">Cancel</button>
                </div>
            </div>
        </div>
        <div class="w-30 d-flex flex-column">
            <div class="m-1 scrollable">
                <table class="table table-hover table-condensed" id="tableSong">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Add</th>
                            <th>Title</th>
                            <th>Artist</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- MODAL PRIVIEW VIDEO -->
<div class="modal fade fill-in" id="modalPriviewVideo" tabindex="-1" role="dialog" aria-labelledby="modalPriviewVideoLabel"
    aria-hidden="true">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-close"></i></button>
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="text-left p-b-5"><span class="semi-bold"></span></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div id="video">
                                    <video class="h-100 w-100" controls>
                                        <source src="" type="video/mp4">
                                    </video>
                                </div>
                                <div class="m-1">
                                    <button class="btn btn-complete btn-cons" id="add-to-playlist">Add to Playlist</button>
                                    <button class="btn btn-dark" data-dismiss="modal" aria-hidden="true">Cancel</button>
                                </div>
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
<!-- END MODAL PRIVIEW VIDEO -->
<!-- MODAL FORM -->
<div class="modal fade fill-in" id="modalFormPlaylist" tabindex="-1" role="dialog" aria-labelledby="modalFormPlaylistLabel"
    aria-hidden="true">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-close"></i></button>
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="text-left p-b-5"><span class="semi-bold">Playlist</span></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">

                                <form method="POST" id="form-playlist-modal" enctype="multipart/form-data" autocomplete="off">
                                    @csrf

                                    <div class="row">

                                        <div class="form-group col-md-12">
                                            <label>Name</label>
                                            <input type="text" name="name" class="form-control" id="name" placeholder="Name" required>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="form-group col-md-12">
                                            <label>Category</label>
                                            <select name="playlist_category_id" class="full-width" id="playlist_category_id" data-init-plugin="select2" required>
                                            </select>
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
                  <button class="btn btn-complete" id="confirm">Yes</button>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- END MODAL CONFIRM  -->
@endsection
