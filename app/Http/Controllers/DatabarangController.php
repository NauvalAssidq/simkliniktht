<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Databarang;

class DatabarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Databarang::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kd_brng', 'like', "%{$search}%")
                  ->orWhere('nm_brng', 'like', "%{$search}%");
            });
        }

        $databarang = $query->orderBy('nm_brng')->paginate(25)->withQueryString();

        return view('satusehat.kfa.index', compact('databarang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kd_brng'   => 'required|string|max:30|unique:databarang,kd_brng',
            'nm_brng'   => 'required|string|max:250',
            'satuan'    => 'nullable|string|max:30',
            'kode_form' => 'nullable|string|max:15',
            'nm_form'   => 'nullable|string|max:100',
            'harga'     => 'nullable|numeric|min:0',
            'stok'      => 'nullable|integer|min:0',
        ]);

        Databarang::create($request->only('kd_brng', 'nm_brng', 'satuan', 'kode_form', 'nm_form', 'harga', 'stok'));

        return redirect()->route('satusehat.kfa')->with('success', "Obat {$request->nm_brng} berhasil ditambahkan.");
    }

    public function update(Request $request, $kd_brng)
    {
        $request->validate([
            'nm_brng'   => 'required|string|max:250',
            'satuan'    => 'nullable|string|max:30',
            'kode_form' => 'nullable|string|max:15',
            'nm_form'   => 'nullable|string|max:100',
            'harga'     => 'nullable|numeric|min:0',
            'stok'      => 'nullable|integer|min:0',
        ]);

        $item = Databarang::findOrFail($kd_brng);
        $item->update($request->only('nm_brng', 'satuan', 'kode_form', 'nm_form', 'harga', 'stok'));

        return redirect()->route('satusehat.kfa')->with('success', "Obat {$kd_brng} berhasil diperbarui.");
    }

    public function destroy($kd_brng)
    {
        Databarang::findOrFail($kd_brng)->delete();
        return redirect()->route('satusehat.kfa')->with('success', "Obat {$kd_brng} berhasil dihapus.");
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return redirect()->route('satusehat.kfa')->with('error', 'Gagal membuka file CSV.');
        }

        $header = fgetcsv($handle, 0, ',');
        if (!$header) {
            fclose($handle);
            return redirect()->route('satusehat.kfa')->with('error', 'File CSV kosong.');
        }

        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        // Detect separator
        if (count($header) === 1 && str_contains($header[0], "\t")) {
            rewind($handle);
            $header = fgetcsv($handle, 0, "\t");
            $header = array_map(fn($h) => strtolower(trim($h)), $header);
            $separator = "\t";
        } else {
            $separator = ',';
        }

        $codeIdx    = array_search('kd_brng', $header) !== false ? array_search('kd_brng', $header) : array_search('code', $header);
        $nameIdx    = array_search('nm_brng', $header) !== false ? array_search('nm_brng', $header) : array_search('display', $header);
        $satuanIdx  = array_search('satuan', $header);
        $formCodeIdx = array_search('kode_form', $header);
        $formNameIdx = array_search('nm_form', $header);
        $hargaIdx   = array_search('harga', $header);
        $stokIdx    = array_search('stok', $header);

        if ($codeIdx === false || $nameIdx === false) {
            fclose($handle);
            return redirect()->route('satusehat.kfa')->with('error', 'Header CSV harus mengandung kolom kd_brng/code dan nm_brng/display.');
        }

        $imported = 0;
        $skipped = 0;
        $batch = [];

        while (($row = fgetcsv($handle, 0, $separator)) !== false) {
            $code = trim($row[$codeIdx] ?? '');
            $name = trim($row[$nameIdx] ?? '');

            if (empty($code) || empty($name)) {
                $skipped++;
                continue;
            }

            $batch[] = [
                'kd_brng'    => $code,
                'nm_brng'    => $name,
                'satuan'     => ($satuanIdx !== false) ? trim($row[$satuanIdx] ?? '') : null,
                'kode_form'  => ($formCodeIdx !== false) ? trim($row[$formCodeIdx] ?? '') : null,
                'nm_form'    => ($formNameIdx !== false) ? trim($row[$formNameIdx] ?? '') : null,
                'harga'      => ($hargaIdx !== false) ? floatval($row[$hargaIdx] ?? 0) : 0,
                'stok'       => ($stokIdx !== false) ? intval($row[$stokIdx] ?? 0) : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                Databarang::upsert($batch, ['kd_brng'], ['nm_brng', 'satuan', 'kode_form', 'nm_form', 'harga', 'stok', 'updated_at']);
                $imported += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            Databarang::upsert($batch, ['kd_brng'], ['nm_brng', 'satuan', 'kode_form', 'nm_form', 'harga', 'stok', 'updated_at']);
            $imported += count($batch);
        }

        fclose($handle);

        return redirect()->route('satusehat.kfa')->with('success', "Import selesai! {$imported} obat berhasil diimpor, {$skipped} baris dilewati.");
    }

    /**
     * JSON search for Select2 on the examination form
     */
    public function apiSearch(Request $request)
    {
        $q = $request->input('q', $request->input('term', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Databarang::where('kd_brng', 'like', "%{$q}%")
            ->orWhere('nm_brng', 'like', "%{$q}%")
            ->limit(20)
            ->get(['kd_brng', 'nm_brng', 'satuan', 'harga', 'stok']);

        return response()->json($results);
    }
}
