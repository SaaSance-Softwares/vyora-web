@extends('emails.layout')

@section('content')
    <h2>Password Changed</h2>
    
    <p>Hi {{ $user->name }},</p>
    
    <p>The password for your {{ $storeName }} account was recently changed.</p>
    
    <p>If you made this change, then you're all set and can safely ignore this email.</p>
    
    <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin-top: 30px; border-radius: 4px;">
        <p style="margin: 0; color: #991b1b; font-size: 14px;"><strong>Didn't make this change?</strong><br>If you did not authorize this change, please contact our support team immediately to secure your account.</p>
    </div>
@endsection
