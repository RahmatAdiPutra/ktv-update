@extends('layouts.app')
@push('bodyClass','fixed-header')
@section('appContent')
<!-- BEGIN SIDEBPANEL-->
<nav class="page-sidebar" data-pages="sidebar">

    <!-- BEGIN SIDEBAR MENU HEADER-->
    <div class="sidebar-header">
        <a href="javascript:;" class="m-brand__logo-wrapper">
            <img alt="" src="public/img/logo_white_2x.png" height="22" />
        </a>
        <div class="sidebar-header-controls">
            <button type="button"
                class="btn btn-link d-lg-inline-block d-xlg-inline-block d-md-inline-block d-sm-none d-none"
                data-toggle-pin="sidebar"><i class="fa fs-12"></i>
            </button>
        </div>
    </div>
    <!-- END SIDEBAR MENU HEADER-->
    <!-- START SIDEBAR MENU -->
    <div class="sidebar-menu">
        <!-- BEGIN SIDEBAR MENU ITEMS-->
        <ul class="menu-items">
            <li class="m-t-30">
                <a href="javascript:;">Dashboard</a>
                <span class="icon-thumbnail">
                    <i class="pg-home"></i>
                </span>
            </li>
            <li class="">
                <a href="javascript:;">
                    <span class="title">Master Data</span>
                    <span class="arrow"></span>
                </a>
                <span class="icon-thumbnail">
                    <i class="fa fa-database"></i>
                </span>
                <ul class="sub-menu">
                    <li class="">
                        <a href="{{ route('operator.index') }}">Operator Station</a>
                        <span class="icon-thumbnail">O</span>
                    </li>
                    <li class="">
                        <a href="javascript:;">
                            <span class="title">Room</span>
                            <span class="arrow"></span>
                        </a>
                        <span class="icon-thumbnail">R</span>
                        <ul class="sub-menu">
                            <li class="">
                                <a href="{{ route('room.type.index') }}">Type</a>
                                <span class="icon-thumbnail">T</span>
                            </li>
                            <li class="">
                                <a href="{{ route('room.status.index') }}">Status</a>
                                <span class="icon-thumbnail">S</span>
                            </li>
                            <li class="">
                                <a href="{{ route('room.session.type.index') }}">Session Type</a>
                                <span class="icon-thumbnail">S</span>
                            </li>        
                        </ul>
                    </li>
                    <li class="">
                        <a href="javascript:;">
                            <span class="title">Song</span>
                            <span class="arrow"></span>
                        </a>
                        <span class="icon-thumbnail">S</span>
                        <ul class="sub-menu">
                            <li class="">
                                <a href="{{ route('song.genre.index') }}">Genre</a>
                                <span class="icon-thumbnail">G</span>
                            </li>
                            <li class="">
                                <a href="{{ route('song.language.index') }}">Language</a>
                                <span class="icon-thumbnail">L</span>
                            </li>
                            <li class="">
                                <a href="{{ route('song.tag.index') }}">Tag</a>
                                <span class="icon-thumbnail">T</span>
                            </li>
                            <li class="">
                                <a href="{{ route('song.request.index') }}">Request</a>
                                <span class="icon-thumbnail">R</span>
                            </li>
                        </ul>
                    </li>
                    <li class="">
                        <a href="{{ route('album.index') }}">Album</a>
                        <span class="icon-thumbnail">A</span>
                    </li>
                    <li class="">
                        <a href="javascript:;">
                            <span class="title">Artist</span>
                            <span class="arrow"></span>
                        </a>
                        <span class="icon-thumbnail">A</span>
                        <ul class="sub-menu">
                            <li class="">
                                <a href="{{ route('artist.category.index') }}">Category</a>
                                <span class="icon-thumbnail">C</span>
                            </li>
                        </ul>
                    </li>
                    <li class="">
                        <a href="javascript:;">
                            <span class="title">Playlist</span>
                            <span class="arrow"></span>
                        </a>
                        <span class="icon-thumbnail">P</span>
                        <ul class="sub-menu">
                            <li class="">
                                <a href="{{ route('playlist.category.index') }}">Category</a>
                                <span class="icon-thumbnail">C</span>
                            </li>
                        </ul>
                    </li>
                    <li class="">
                        <a href="{{ route('promotion.index') }}">Promotion</a>
                        <span class="icon-thumbnail">P</span>
                    </li>
                </ul>
            </li>
            <li class="">
                <a href="javascript:;">
                    <span class="title">Transaction Data</span>
                    <span class="arrow"></span>
                </a>
                <span class="icon-thumbnail">
                    <i class="fa fa-database"></i>
                </span>
                <ul class="sub-menu">
                    <li class="">
                        <a href="{{ route('song.index') }}">Song</a>
                        <span class="icon-thumbnail">S</span>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    <!-- END SIDEBAR MENU -->
</nav>
<!-- END SIDEBAR -->
<!-- END SIDEBPANEL-->
<!-- START PAGE-CONTAINER -->
<div class="page-container ">
    <!-- START HEADER -->
    <div class="header ">
        <!-- START MOBILE SIDEBAR TOGGLE -->
        <a href="#" class="btn-link toggle-sidebar d-lg-none pg pg-menu" data-toggle="sidebar">
        </a>
        <!-- END MOBILE SIDEBAR TOGGLE -->
        <div class="">
            <div class="brand inline">
                <a href="javascript:;" class="m-brand__logo-wrapper" id="app-logo">
                    <img alt="" src="public/img/logo.png" height="22" />
                </a>
            </div>
            <!-- START NOTIFICATION LIST -->

            <!-- END NOTIFICATIONS LIST -->
        </div>
        <div class="d-flex align-items-center">
            @yield('appTopBar')
            <!-- START User Info-->
            <div class="pull-left p-r-10 fs-14 font-heading d-lg-block d-none">
                <span class="semi-bold"></span>
            </div>
            <div class="dropdown pull-right d-block">
                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <span class="pull-left">Logout&nbsp;{{ Auth::user()->name }}</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
            <!-- END User Info-->
        </div>
    </div>
    <!-- END HEADER -->
    <!-- BEGIN PlACE PAGE CONTENT HERE -->
    @yield('adminContent')
    <!-- END PLACE PAGE CONTENT HERE -->
    @if (empty($__env->yieldContent('no-copyright')))
    <!-- START COPYRIGHT -->
    <div class="container-fluid container-fixed-lg footer">
        <div class="copyright sm-text-center">
            <p class="small no-margin pull-left sm-pull-reset">
                <span class="hint-text">Copyright &copy; 2017&mdash;{{ date('Y') }} </span>
                <span class="font-montserrat">AX Group</span>.
                <span class="hint-text">All rights reserved. </span>
            </p>
            <p class="small no-margin pull-right sm-pull-reset">

            </p>
            <div class="clearfix"></div>
        </div>
    </div>
    <!-- END COPYRIGHT -->
    @endif
</div>
<!-- END PAGE CONTENT WRAPPER -->



@endsection
