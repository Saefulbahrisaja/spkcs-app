<?php
namespace App\Http\Controllers\Admin;
use App\Models\BatasKesesuaian;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BatasController extends Controller
{
    public function index()
    {
        $batas = BatasKesesuaian::first() ?? BatasKesesuaian::create([]);
        return view('admin.batas.index', compact('batas'));
    }

    public function update(Request $r)
    {
        $batas = BatasKesesuaian::first();

        $batas->update([
            'batas_s1' => $r->batas_s1,
            'batas_s2' => $r->batas_s2,
            'batas_s3' => $r->batas_s3,
        ]);

        return back()->with('success', 'Batas kesesuaian berhasil diupdate.');
    }
}
