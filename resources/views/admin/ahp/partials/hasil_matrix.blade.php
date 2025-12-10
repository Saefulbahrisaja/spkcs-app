<div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
            <tr>
                <th>Kriteria</th>
                @foreach($items as $it)
                    <th>{{ $it->nama_kriteria }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
             @foreach($sf_matrix as $i => $row)
        <tr>
            <th class="text-start">{{ $items[$i]->nama_kriteria }}</th>

            @foreach($row as $j => $f)

                @php
                    // Jika DIAGONAL -> pakai default nilai SF-AHP
                    if ($i == $j) {
                        $mu = 0.5; $nu = 0.4; $pi = 0.4;
                        $tupleText = '(0.500, 0.400, 0.400)';
                        $style = 'background:#eef4ff;font-weight:bold;';
                    }
                    else {
                        // normalisasi fuzzy tuple untuk off-diagonal
                        if (is_array($f)) {
                            $mu = $f['mu'] ?? ($f[0] ?? 0.5);
                            $nu = $f['nu'] ?? ($f[1] ?? 0.4);
                            $pi = $f['pi'] ?? ($f[2] ?? 0.4);
                        }
                        elseif (is_numeric($f)) {
                            $mu = $f; $nu = 0; $pi = 0;
                        }
                        else {
                            $mu = 0.5; $nu = 0.4; $pi = 0.4;
                        }

                        $tupleText = '(' 
                                        . number_format($mu,3) . ', '
                                        . number_format($nu,3) . ', '
                                        . number_format($pi,3) . ')';
                        $style = '';
                    }
                @endphp

                <td style="{{ $style }}">
                    <span class="small">{{ $tupleText }}</span>
                </td>

            @endforeach
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="accordion mt-4" id="subKriteriaAccordion">
    @foreach($items as $parent)
        @if(isset($sub_items[$parent->id]) && count($sub_items[$parent->id]) > 1)
            @php $collapseId = 'collapseSubKriteria' . $parent->id; @endphp
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading{{ $parent->id }}">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                        Sub-Kriteria: {{ $parent->nama_kriteria }}
                    </button>
                </h2>
                <div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $parent->id }}" data-bs-parent="#subKriteriaAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-middle">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Sub-Kriteria</th>
                                        @foreach($sub_items[$parent->id] as $sub)
                                            <th>{{ $sub->nama_kriteria }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sub_items[$parent->id] as $i => $s1)
                                    <tr>
                                        <th class="text-start">{{ $s1->nama_kriteria }}</th>
                                        @foreach($sf_submatrix[$parent->id][$i] as $j => $f)
                                            @php
                                                if ($i == $j) {
                                                    // default diagonal SF-AHP
                                                    $mu = 0.5; $nu = 0.4; $pi = 0.4;
                                                    $tuple = '(0.500, 0.400, 0.400)';
                                                    $style = 'background:#eef4ff;font-weight:bold;';
                                                } 
                                                else {
                                                    if (is_array($f)) {
                                                        $mu = $f['mu'] ?? $f[0] ?? 0.5;
                                                        $nu = $f['nu'] ?? $f[1] ?? 0.4;
                                                        $pi = $f['pi'] ?? $f[2] ?? 0.4;
                                                    }
                                                    else {
                                                        $mu = 0.5; $nu = 0.4; $pi = 0.4;
                                                    }

                                                    $tuple = '(' 
                                                                . number_format($mu,3) . ', '
                                                                . number_format($nu,3) . ', '
                                                                . number_format($pi,3) . ')';
                                                    $style = '';
                                                }
                                            @endphp

                                            <td style="{{ $style }}">
                                                <span class="small">{{ $tuple }}</span>
                                            </td>

                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>
