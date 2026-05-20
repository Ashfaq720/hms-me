@extends('backend.layouts.auth')

@section('title', 'Forgot Password')

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
                            <p>Enter your email to reset your password.</p>
                        </div>
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        <form action="{{ route('password.email') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label" for="resetEmail">Email address</label>
                                <input type="email" class="form-control" id="resetEmail" placeholder="info@example.com"
                                    name="email" value="{{ old('email') }}">
                            </div>
                            <div class="clearfix">
                                <button type="submit" value="Submit"
                                    class="btn btn-primary waves-effect waves-light w-100 mb-3">Send Reset Link</button>
                                <a href="{{ route('login') }}" class="btn btn-light waves-effect waves-light w-100"> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
