@extends('layouts.auth')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h2>Worker Onboarding - Stage 4: Sign Declarations</h2>
                <span class="badge bg-info mt-2 mt-md-0">Stage 4 of 6</span>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('worker.onboarding.stage4.submit', ['token' => $token]) }}">
                @csrf

                <div class="row">
                    <div class="col-md-8">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">Important: Review and Sign All Declarations</h6>
                            <p class="mb-0">You must carefully review and agree to all declarations below before proceeding. These are binding agreements that govern your work with Allegiance Heart &amp; Home Care.</p>
                        </div>

                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Required Agreement Forms</h6>
                            </div>
                            <div class="card-body">
                                @if ($adminAssignedDocuments->isNotEmpty())
                                    <p class="text-muted mb-3">These documents were provided by Allegiance Heart &amp; Home Care and are required for onboarding.</p>
                                    <div class="list-group">
                                        @foreach ($adminAssignedDocuments as $document)
                                            <div class="list-group-item d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                                <div>
                                                    <strong>{{ $document->title }}</strong>
                                                    <div class="small text-muted">{{ $document->document_type }}</div>
                                                </div>
                                                <div class="btn-group flex-wrap w-100 justify-content-end" role="group">
                                                    <a href="{{ route('worker.onboarding.assigned_document.preview', ['token' => $token, 'document' => $document->id]) }}" class="btn btn-sm btn-outline-primary">Preview</a>
                                                    <a href="{{ route('worker.onboarding.assigned_document.download', ['token' => $token, 'document' => $document->id]) }}" class="btn btn-sm btn-outline-secondary">Download</a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-secondary mb-0">
                                        No agreement forms have been uploaded by admin yet. Once uploaded, they will appear here for review and signature.
                                    </div>
                                @endif
                            </div>
                        </div>

                        @forelse ($declarations as $declaration)
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <div class="form-check">
                                        <input type="hidden" name="declarations[{{ $declaration->declaration_type }}]" value="no">
                                        <input class="form-check-input" type="checkbox" name="declarations[{{ $declaration->declaration_type }}]" value="yes" id="decl_{{ $declaration->id }}" {{ $declaration->agreed ? 'checked' : '' }}>
                                        <label class="form-check-label" for="decl_{{ $declaration->id }}">
                                            <strong>{{ $declaration->declaration_type->label() }}</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $declaration->declaration_text }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-warning">
                                No declarations available. Contact support if this seems incorrect.
                            </div>
                        @endforelse

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Digital Signature</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Draw your signature below to electronically sign these declarations.</p>

                                <div class="mb-3">
                                    <label class="form-label">Signature *</label>
                                    <canvas id="signature-pad" class="border rounded w-100" style="height: 180px; background: #fff; cursor: crosshair;"></canvas>
                                    <input type="hidden" name="signature_image" id="signature-image-input">
                                    <small class="form-text text-muted d-block mt-2">Use your mouse or touch device to draw your signature.</small>
                                    <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="clearSignature()">Clear</button>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap onboarding-action-buttons">
                            <button type="submit" class="btn btn-primary btn-lg flex-fill">Sign & Continue to Next Stage</button>
                            <a href="#" class="btn btn-outline-secondary btn-lg flex-fill">Save as Draft</a>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Declarations Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    @forelse ($declarations as $declaration)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-0">{{ $declaration->declaration_type->label() }}</h6>
                                                </div>
                                                <span class="badge {{ $declaration->agreed ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $declaration->agreed ? '✓' : '○' }}
                                                </span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted">No declarations</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Signature Pad Library -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('signature-pad');
        const signatureInput = document.getElementById('signature-image-input');
        const form = document.querySelector('form');

        if (!canvas || !signatureInput || !form) {
            return;
        }

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            const context = canvas.getContext('2d');
            context.scale(ratio, ratio);
        }

        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)',
        });

        window.clearSignature = function () {
            signaturePad.clear();
            signatureInput.value = '';
        };

        form.addEventListener('submit', function (event) {
            if (signaturePad.isEmpty()) {
                event.preventDefault();
                alert('Please provide your signature before submitting.');
                return;
            }

            signatureInput.value = signaturePad.toDataURL('image/png');
        });
    });
</script>
@endsection
