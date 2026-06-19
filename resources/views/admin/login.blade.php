@extends('layouts.app')

@section('title', 'Admin Login')

@section('content')
<div class="login-container">
    <div class="login-box">
        <h2 style="text-align: center; margin-bottom: 8px; color: #1a1a1a;">Restricted Administration Portal</h2>

        <div class="info-box warning" style="margin-bottom: 24px;">
            Please choose your system entity assignment to authenticate access.
        </div>

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 16px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.login.post') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="role">Select Role</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="">-- Select System Entity --</option>
                    <option value="Super User">Mutale</option>
                    <option value="Sarah">Sarah</option>
                    <option value="Bracious">Bracious</option>
                    <option value="Blessing">Blessing</option>
                    <option value="Mwiinga">Mwiinga</option>
                    <option value="Jacqueline">Jacqueline</option>
                    <option value="Florence">Florence</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control"
                       placeholder="Enter your authentication password" required>
            </div>

            <div class="form-group" style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary btn-full">
                    Unlock Dashboard Session
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
