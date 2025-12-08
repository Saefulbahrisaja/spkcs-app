<?php
namespace App\Services;

use App\Models\AlternatifLahan;
use App\Models\Kriteria;
use App\Models\NilaiAlternatif;
use Illuminate\Support\Facades\Schema;

class ShpAHPService
{
    public function normalizeAndCompute(array $altIds, array $map): array
    {
        if (empty($altIds)) return [];

        $kIds = array_unique(array_values($map));
        $kriteria = Kriteria::whereIn('id', $kIds)->get()->keyBy('id');

        $stats = [];
        foreach ($kIds as $k) {
            $stats[$k] = ['min'=>INF,'max'=>-INF,'values'=>[]];
        }

        $rows = NilaiAlternatif::whereIn('alternatif_id',$altIds)
            ->whereIn('kriteria_id',$kIds)
            ->get();

        foreach ($rows as $row) {
            $stats[$row->kriteria_id]['values'][$row->alternatif_id] = $row->nilai;
            $stats[$row->kriteria_id]['min'] =
                min($stats[$row->kriteria_id]['min'], $row->nilai);
            $stats[$row->kriteria_id]['max'] =
                max($stats[$row->kriteria_id]['max'], $row->nilai);
        }

        $hasRaw = Schema::hasColumn((new NilaiAlternatif)->getTable(), 'nilai_raw');
        $scores = array_fill_keys($altIds, 0);

        foreach ($kIds as $k) {

            $min = $stats[$k]['min'] === INF ? 0 : $stats[$k]['min'];
            $max = $stats[$k]['max'] === -INF ? 0 : $stats[$k]['max'];
            $tipe = $kriteria[$k]->tipe ?? 'benefit';
            $bobot = (float)($kriteria[$k]->bobot ?? 0);

            foreach ($altIds as $aid) {
                $raw = $stats[$k]['values'][$aid] ?? 0;

                if ($hasRaw) {
                    NilaiAlternatif::updateOrCreate(
                        ['alternatif_id'=>$aid, 'kriteria_id'=>$k],
                        ['nilai_raw'=>$raw]
                    );
                }

                if (abs($max - $min) < 1e-12) {
                    $norm = 1;
                } else {
                    if ($tipe === 'benefit') {
                        $norm = ($raw - $min) / ($max - $min);
                    } else {
                        $norm = ($max - $raw) / ($max - $min);
                    }
                    $norm = max(0,min(1,$norm));
                }

                NilaiAlternatif::updateOrCreate(
                    ['alternatif_id'=>$aid, 'kriteria_id'=>$k],
                    ['nilai'=>$norm]
                );

                $scores[$aid] += $norm * $bobot;
            }
        }

        foreach ($scores as $aid => $score) {
            AlternatifLahan::where('id',$aid)->update(['nilai_total'=>$score]);
        }

        return $scores;
    }
}
