@extends('emails.layout')

@section('content')
    <h2>Address Book Updated</h2>
    
    <p>Hi {{ $user->name }},</p>
    
    <p>We're writing to let you know that a delivery address on your {{ $storeName }} account was recently <strong>{{ $action }}</strong>.</p>
    
    <p>If you made this change, you can safely ignore this email.</p>
    
    <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin-top: 30px; border-radius: 4px;">
        <p style="margin: 0; color: #991b1b; font-size: 14px;"><strong>Didn't make this change?</strong><br>If you did not authorize this change, please log in to your account and update your password immediately, or contact our support team.</p>
    </div>
@endsection
