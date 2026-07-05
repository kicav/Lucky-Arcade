@extends('layouts.app')
@section('title', 'Register')
@section('content')
<div class="auth-card">
    <h1>Create account</h1>
    <p>New accounts receive 10,000 virtual credits with no cash value.</p>
    <form method="post" action="{{ route('register.store') }}" class="stack">
        @csrf
        <label>Name<input type="text" name="name" value="{{ old('name') }}" required autofocus></label>
        <label>Email<input type="email" name="email" value="{{ old('email') }}" required></label>
        <label>Password<input type="password" name="password" required></label>
        <label>Confirm password<input type="password" name="password_confirmation" required></label>
        <button class="button" type="submit">Create account</button>
    </form>
</div>
@endsection
