@extends('layouts.app')
@push('appTitle', 'Login')
@section('appContent')
<div class="vh-100 w-100">
    <div class="h-100 w-100 d-flex flex-column justify-content-center align-items-center">
        <div class="h-50 w-25">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>
                <div class="card-body">
                    <form id="form-login" class="p-t-15" role="form" action="{{ route('login') }}" method="POST">

                        {{ csrf_field() }}
                        @if (session('loginError'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
                            {{ session('loginError') }}
                        </div>
                        @endif

                        <!-- START Form Control-->
                        <div
                            class="form-group form-group-default required{{ $errors->has('username') ? ' has-error' : '' }}">
                            <label>Username</label>
                            <div class="controls">
                                <input type="text" name="username" placeholder="Username" class="form-control"
                                    value="{{ old('username') }}" required autofocus autocomplete="off">
                            </div>
                        </div>
                        <!-- END Form Control-->
                        <!-- START Form Control-->
                        <div
                            class="form-group form-group-default required{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label>Password</label>
                            <div class="controls">
                                <input type="password" class="form-control" name="password" placeholder="Credentials"
                                    required>
                            </div>
                        </div>
                        <!-- START Form Control-->
                        <div class="row">
                            <div class="col-md-6 no-padding sm-p-l-10">
                                <div class="checkbox ">
                                    <input type="checkbox" value="1" name="rememberMe" id="rememberMe">
                                    <label for="rememberMe">Keep Me Signed in</label>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex align-items-center justify-content-end">
                                <a href="#" class="text-info small">Forgot password?</a>
                            </div>
                        </div>
                        <!-- END Form Control-->
                        <button class="btn btn-complete btn-cons m-t-10" type="submit">Sign in</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
