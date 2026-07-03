@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.portal')

@section('content')
    <div class="d-flex flex-column gap-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">Notifications</h4>
                <p class="text-muted mb-0">Recent alerts for your portal activity and system updates.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="badge bg-primary fs-6">{{ $notifications->total() }} total</span>
                <form method="POST" action="{{ route('portal.notifications.mark_all_read') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Mark all read</button>
                </form>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if($notifications->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-bell-slash fs-2 mb-3"></i>
                        <p class="mb-0">No notifications yet. Check back later for updates.</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notification)
                            @php
                                $data = $notification->data ?? [];
                                $title = $data['title'] ?? ucfirst($notification->type);
                                $message = $data['message'] ?? 'View details for this update.';
                                $isUnread = $notification->read_at === null;
                            @endphp
                            <a href="{{ route('portal.notifications.show', $notification) }}" class="list-group-item list-group-item-action py-3 px-4 {{ $isUnread ? 'bg-light' : '' }}">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ $title }}</div>
                                        <div class="small text-muted">{{ $message }}</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge rounded-pill {{ $isUnread ? 'bg-primary' : 'bg-secondary' }}">{{ $isUnread ? 'New' : 'Read' }}</span>
                                        <div class="small text-muted mt-2">{{ $notification->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="p-3 border-top bg-light">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
