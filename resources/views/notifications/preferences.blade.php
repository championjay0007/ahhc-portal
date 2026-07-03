@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.portal')

@section('content')
    <div class="container-fluid">
        <h4>Notification Preferences</h4>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('portal.notifications.preferences.update') }}">
            @csrf

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="channel_email" name="channel_email" value="1" @if($pref->channel_email) checked @endif>
                <label class="form-check-label" for="channel_email">Email notifications</label>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="channel_in_app" name="channel_in_app" value="1" @if($pref->channel_in_app) checked @endif>
                <label class="form-check-label" for="channel_in_app">In-app notifications</label>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="channel_push" name="channel_push" value="1" @if($pref->channel_push) checked @endif>
                <label class="form-check-label" for="channel_push">Browser push notifications</label>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="channel_sms" name="channel_sms" value="1" @if($pref->channel_sms) checked @endif>
                <label class="form-check-label" for="channel_sms">SMS notifications</label>
            </div>

            <button class="btn btn-primary" type="submit">Save preferences</button>
        </form>
    </div>
@endsection
