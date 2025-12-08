
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
                            // normalisasi format fuzzy (aman)
                            if (is_array($f)) {
                                $mu = $f['mu'] ?? ($f[0] ?? null);
                                $nu = $f['nu'] ?? ($f[1] ?? null);
                                $pi = $f['pi'] ?? ($f[2] ?? null);

                                // jika masih null → fallback neutral
                                if ($mu === null || $nu === null || $pi === null) {
                                    $mu = 1; $nu = 0; $pi = 0;
                                }
                            }
                            elseif (is_numeric($f)) {
                                // crisp → jadikan mu=f, nu=0, pi=0
                                $mu = $f; $nu = 0; $pi = 0;
                            }
                            else {
                                // fallback
                                $mu = 1; $nu = 0; $pi = 0;
                            }
                        @endphp

                        <td>
                            <small>μ: <strong>{{ number_format($mu,4) }}</strong></small><br>
                            <small>ν: <strong>{{ number_format($nu,4) }}</strong></small><br>
                            <small>π: <strong>{{ number_format($pi,4) }}</strong></small>
                        </td>

                    @endforeach

                </tr>
                @endforeach
            </tbody>

        </table>
        </div>

   