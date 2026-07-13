@extends('backend.layouts.auth')

@section('title', 'New Password')

@section('content')
    <div class="page-layout">
        <div class="auth-wrapper min-vh-100 px-2"
            style="background-image: url({{ asset('backend/assets/images/auth/auth.webp') }}); background-size: cover; background-position: center; background-repeat: no-repeat;">
            <div class="row g-0 min-vh-100">
                <div class="col-xl-5 col-lg-6 ms-auto px-sm-4 align-self-center py-4">
                    <div class="card card-body p-4 p-sm-5 maxw-450px m-auto rounded-4">
                        <div class="mb-4 text-center">
                            <a href="{{ route('home') }}" aria-label="GXON logo">
                                <img class="visible-light" src="{{ asset('backend/assets/images/logo-full.svg') }}"
                                    alt="GXON logo">
                                <img class="visible-dark" src="{{ asset('backend/assets/images/logo-full-white.svg') }}"
                                    alt="GXON logo">
                            </a>
                        </div>
                        <div class="text-center mb-4">
                            <h5 class="mb-1">Welcome to GXON</h5>
                            <p>Enter your new password.</p>
                        </div>
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        <form action="{{ route('password.update') }}" method="POST">
                            @csrf
                            @if(isset($token))
                                <input type="hidden" name="token" value="{{ $token }}">
                            @endif
                            @if(isset($email))
                                <input type="hidden" name="email" value="{{ $email }}">
                            @endif
                            <div class="mb-4">
                                <label class="form-label" for="newPassword">New Password</label>
                                <input type="password" class="form-control" id="newPassword" placeholder="********"
                                    name="password">
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="confirmPassword">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" placeholder="********"
                                    name="password_confirmation">
                            </div>
                            <div class="mb-4">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="termsConditions" name="terms">
                                    <label class="form-check-label" for="termsConditions">
                                        I Agree & <a href="javascript:void(0);">Terms and conditions.</a>
                                    </label>
                                </div>
                            </div>
                            <div class="clearfix">
                                <button type="submit" value="Submit"
                                    class="btn btn-primary waves-effect waves-light w-100 mb-3">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
