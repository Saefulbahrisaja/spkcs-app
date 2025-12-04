@extends('layouts.app')

@section('content')

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Pengaturan Batas Kesesuaian</li>
</ol>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-sliders-h me-2"></i> Pengaturan Batas Kesesuaian Lahan
        </h5>
    </div>

    <div class="card-body">

        {{-- Notifikasi --}}
        @if ($message = Session::get('success'))
            <div class="alert alert-success">{{ $message }}</div>
        @endif

        @if ($message = Session::get('error'))
            <div class="alert alert-danger">{{ $message }}</div>
        @endif

        <form method="POST" action="{{ route('admin.batas.update') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Batas S1 (≥ nilai total)</label>
                <input type="number" step="0.01" name="batas_s1"
                       class="form-control w-25"
                       value="{{ $batas->batas_s1 }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Batas S2 (≥ nilai total)</label>
                <input type="number" step="0.01" name="batas_s2"
                       class="form-control w-25"
                       value="{{ $batas->batas_s2 }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Batas S3 (≥ nilai total)</label>
                <input type="number" step="0.01" name="batas_s3"
                       class="form-control w-25"
                       value="{{ $batas->batas_s3 }}" required>
            </div>

            <button class="btn btn-primary px-4 mt-3">
                <i class="fas fa-save me-1"></i> Simpan Pengaturan
            </button>
        </form>

    </div>
</div>

@endsection
