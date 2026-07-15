@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Branding</h1>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div style="margin-bottom:20px;">
            <h3>Current logo</h3>
            @if(!empty($logoPath))
                <img src="{{ asset('storage/' . ltrim($logoPath, '/')) }}" alt="Logo" style="max-height:120px;" />
            @else
                <p>No logo has been uploaded yet.</p>
            @endif
        </div>

        <form action="{{ route('portal.admin.branding.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="logo">Upload new logo (PNG, JPG, SVG) — max 2MB</label>
                <input type="file" name="logo" id="logo" class="form-control" accept="image/*" />
                @error('logo')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            <div style="margin-top:16px;">
                <button class="btn btn-primary" type="submit">Upload logo</button>
            </div>
        </form>
    </div>
@endsection
