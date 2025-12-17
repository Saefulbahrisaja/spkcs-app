@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Input Rekomendasi Kebijakan</h4>

    <form method="POST" action="{{ route('dinas.kebijakan.store') }}">
        @csrf

        <div class="mb-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required>
        </div>

        <div class="mb-3">
    <label class="form-label fw-bold">Wilayah Prioritas </label>

        <textarea name="wilayah_prioritas"
                    class="form-control"
                    rows="8"
                    
                    style="background:#f8f9fa">{{ $wilayahPrioritas }}</textarea>

            <small class="text-muted">
                Data diambil otomatis dari hasil evaluasi .
                Salin atau edit jika diperlukan.
            </small>
        </div>


        <div class="mb-3">
            <label>Daftar Intervensi</label>
            <textarea name="daftar_intervensi" class="form-control" rows="4"
            placeholder="• Rehabilitasi irigasi&#10;• Bantuan pupuk organik&#10;• Penguatan kelompok tani"></textarea>
        </div>

        <div class="mb-3">
            <label>Catatan</label>
            <textarea name="catatan" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-select">
                <option value="draft">Draft</option>
                <option value="ditetapkan">Ditetapkan</option>
                <option value="ditunda">Ditunda</option>
            </select>
        </div>

        <button class="btn btn-success">Simpan</button>
        <a href="{{ route('dinas.kebijakan.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
