@extends('layouts.auth')

@section('title', 'Session expired')

@section('content')
    @if(!empty($brand['logo_url']))
        <div class="auth-page__logo-wrap">
            <img src="{{ $brand['logo_url'] }}" alt="{{ $brand['school_name'] ?? '' }}" class="auth-page__logo">
        </div>
    @endif

    <div class="auth-page__hero">
        <h1>Your session expired</h1>
        <p>
            This page was left open too long, so your sign-in timed out.
            Nothing was saved from that last action. Sign in again to continue.
        </p>
    </div>

    <a href="{{ route('login') }}" class="auth-page__btn auth-page__btn--primary">Sign in again</a>
    <a href="{{ url('/') }}" class="auth-page__btn auth-page__btn--outline">Go to home</a>
@endsection

@section('footer')
    <p class="auth-page__footer-note">
        For security, the library console signs you out after a period of inactivity.
    </p>
@endsection
