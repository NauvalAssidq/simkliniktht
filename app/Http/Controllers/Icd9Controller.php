<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Icd9;

class Icd9Controller extends Controller
{
    public function index(Request $request)
    {
        $query = Icd9::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('display', 'like', "%{$search}%");
            });
        }

        $icd9 = $query->orderBy('code')->paginate(25)->withQueryString();

        return view('satusehat.icd9.index', compact('icd9'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'    => 'required|string|max:15|unique:icd9,code',
            'display' => 'required|string|max:250',
            'version' => 'nullable|string|max:50',
        ]);

        Icd9::create($request->only('code', 'display', 'version'));

        return redirect()->route('satusehat.icd9')->with('success', "Kode {$request->code} berhasil ditambahkan.");
    }

    public function update(Request $request, $code)
    {
        $request->validate([
            'display' => 'required|string|max:250',
            'version' => 'nullable|string|max:50',
        ]);

        $icd9 = Icd9::findOrFail($code);
        $icd9->update($request->only('display', 'version'));

        return redirect()->route('satusehat.icd9')->with('success', "Kode {$code} berhasil diperbarui.");
    }

    public function destroy($code)
    {
        Icd9::findOrFail($code)->delete();
        return redirect()->route('satusehat.icd9')->with('success', "Kode {$code} berhasil dihapus.");
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return redirect()->route('satusehat.icd9')->with('error', 'Gagal membuka file CSV.');
        }

        $header = fgetcsv($handle, 0, ',');
        if (!$header) {
            fclose($handle);
            return redirect()->route('satusehat.icd9')->with('error', 'File CSV kosong.');
        }

        $header = array_map(fn($h) => strtoupper(trim($h)), $header);

        // Detect tab separator
        if (count($header) === 1 && str_contains($header[0], "\t")) {
            rewind($handle);
            $header = fgetcsv($handle, 0, "\t");
            $header = array_map(fn($h) => strtoupper(trim($h)), $header);
            $separator = "\t";
        } else {
            $separator = ',';
        }

        $codeIdx    = array_search('CODE', $header);
        $displayIdx = array_search('DISPLAY', $header);
        $versionIdx = array_search('VERSION', $header);

        if ($codeIdx === false || $displayIdx === false) {
            fclose($handle);
            return redirect()->route('satusehat.icd9')->with('error', 'Header CSV harus mengandung kolom CODE dan DISPLAY.');
        }

        $imported = 0;
        $skipped = 0;
        $batch = [];

        while (($row = fgetcsv($handle, 0, $separator)) !== false) {
            $code    = trim($row[$codeIdx] ?? '');
            $display = trim($row[$displayIdx] ?? '');
            $version = ($versionIdx !== false) ? trim($row[$versionIdx] ?? '') : null;

            if (empty($code) || empty($display)) {
                $skipped++;
                continue;
            }

            $batch[] = [
                'code'       => $code,
                'display'    => $display,
                'version'    => $version,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                Icd9::upsert($batch, ['code'], ['display', 'version', 'updated_at']);
                $imported += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            Icd9::upsert($batch, ['code'], ['display', 'version', 'updated_at']);
            $imported += count($batch);
        }

        fclose($handle);

        return redirect()->route('satusehat.icd9')->with('success', "Import selesai! {$imported} kode berhasil diimpor, {$skipped} baris dilewati.");
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

        $results = Icd9::where('code', 'like', "%{$q}%")
            ->orWhere('display', 'like', "%{$q}%")
            ->limit(20)
            ->get(['code', 'display']);

        return response()->json($results);
    }
}
