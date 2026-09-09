@extends('emails.layout')

@section('content')
    <h2>Welcome to {{ $storeName }}, {{ $user->name }}!</h2>
    
    <p>We're absolutely thrilled to have you here. Your account has been successfully created and you're now part of the {{ $storeName }} family.</p>
    
    <p>You can now log in to your account to view your order history, save delivery addresses, and speed through checkout.</p>
    
    <div class="text-center" style="margin-top: 30px;">
        <a href="{{ url('/') }}" class="btn">Start Shopping Now</a>
    </div>
@endsection
