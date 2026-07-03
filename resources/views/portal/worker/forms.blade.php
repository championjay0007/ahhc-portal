@extends('layouts.portal')

@section('title', 'Forms to Sign')

@section('content')
    <div class="portal-page-header">
        <h1>Forms to Sign</h1>
        <p>Review and sign the forms assigned to you.</p>
    </div>

    @if($documents->isEmpty())
        <div class="portal-empty-state">
            <p>There are no available documents to review at the moment.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Uploaded</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $document)
                        <tr>
                            <td>{{ $document->title }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</td>
                            <td>{{ ucfirst($document->status) }}</td>
                            <td>{{ optional($document->created_at)->format('d M Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('portal.worker.forms.show', $document) }}" class="btn btn-sm btn-primary">Open</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
