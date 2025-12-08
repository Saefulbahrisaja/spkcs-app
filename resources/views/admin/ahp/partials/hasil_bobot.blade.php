
{{-- ============================
     CONSISTENCY CHECK
============================ --}}
<div class="card mb-4">
    <div class="card-header bg-info text-white fw-bold"
         data-bs-toggle="collapse"
         data-bs-target="#consistencyInfo"
         style="cursor:pointer">
        Konsistensi Perhitungan (CI & CR)
        <span class="float-end">▼</span>
    </div>

    <div id="consistencyInfo" class="collapse show">
        <div class="card-body">

            <table class="table table-bordered w-50">
                <tr>
                    <th>λ<sub>max</sub></th>
                    <td>{{ number_format($lambda_max, 6) }}</td>
                </tr>
                <tr>
                    <th>CI (Consistency Index)</th>
                    <td>{{ number_format($CI, 6) }}</td>
                </tr>
                <tr>
                    <th>CR (Consistency Ratio)</th>
                    <td class="{{ $CR <= 0.1 ? 'text-success' : 'text-danger' }} fw-bold">
                        {{ number_format($CR, 6) }}
                        @if($CR <= 0.1)
                            ✔ Konsisten
                        @else
                            ✖ Tidak Konsisten (>0.1)
                        @endif
                    </td>
                </tr>
            </table>

        </div>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header bg-success text-white fw-bold"
         data-bs-toggle="collapse"
         data-bs-target="#scoreSection"
         style="cursor:pointer">
        Score Function (S = μ - ν - π)
        <span class="float-end">▼</span>
    </div>

    <div id="scoreSection" class="collapse show">
        <div class="card-body">

            <table class="table table-bordered w-50">
                <thead class="table-light">
                    <tr>
                        <th>Kriteria</th>
                        <th>Score (S)</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($items as $i => $it)
                    <tr>
                        <td>{{ $it->nama_kriteria }}</td>
                        <td><strong>{{ number_format($weights[$i], 6) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>


{{-- ============================
     C. NORMALIZED WEIGHTS (Master–Detail)
============================ --}}
<div class="card mb-4">
    <div class="card-header bg-dark text-white fw-bold">
        Bobot Kriteria (Normalized Score)
    </div>

    <div class="card-body">

        <div class="accordion" id="accordionWeights">

            @foreach($items as $i => $it)

            <div class="accordion-item mb-2">

                <h2 class="accordion-header" id="head{{ $it->id }}">
                    <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#col{{ $it->id }}">
                        
                        <strong>{{ $it->nama_kriteria }}</strong>
                        &nbsp; — Bobot: 
                        <span class="text-primary ms-1">
                            {{ number_format($weights[$i], 6) }}
                        </span>

                    </button>
                </h2>

                <div id="col{{ $it->id }}" class="accordion-collapse collapse"
                     data-bs-parent="#accordionWeights">

                    <div class="accordion-body">

                        @if($it->sub->count() == 0)
                            <p class="text-muted">Tidak memiliki sub-kriteria.</p>
                        @else
                        <table class="table table-bordered">
                            <thead>
                                <tr class="table-light">
                                    <th>Sub Kriteria</th>
                                    {{-- <th>Bobot Lokal</th>--}}
                                    <th>Bobot</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($it->sub as $s)
                                    <tr>
                                        <td>{{ $s->nama_kriteria }}</td>
                                        {{-- <td>{{ number_format($s->bobot, 6) }}</td> --}}
                                        <td class="fw-bold">
                                            {{ number_format($s->bobot_global, 6) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif

                    </div>

                </div>

            </div>

            @endforeach

        </div>

    </div>
</div>


