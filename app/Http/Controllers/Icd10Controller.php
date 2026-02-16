<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penyakit;

class Icd10Controller extends Controller
{
    /**
     * Browsable index page with search & pagination
     */
    public function index(Request $request)
    {
        $query = Penyakit::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kd_penyakit', 'like', "%{$search}%")
                  ->orWhere('nm_penyakit', 'like', "%{$search}%");
            });
        }

        $icd10 = $query->orderBy('kd_penyakit')->paginate(25)->withQueryString();

        return view('satusehat.icd10.index', compact('icd10'));
    }

    /**
     * Store a manually added ICD-10 code
     */
    public function store(Request $request)
    {
        $request->validate([
            'kd_penyakit' => 'required|string|max:15|unique:penyakit,kd_penyakit',
            'nm_penyakit' => 'required|string|max:250',
            'version'     => 'nullable|string|max:50',
        ]);

        Penyakit::create([
            'kd_penyakit' => $request->kd_penyakit,
            'nm_penyakit' => $request->nm_penyakit,
            'version'     => $request->version,
        ]);

        return redirect()->route('satusehat.icd10')->with('success', "Kode {$request->kd_penyakit} berhasil ditambahkan.");
    }

    /**
     * Update an ICD-10 code
     */
    public function update(Request $request, $kd_penyakit)
    {
        $request->validate([
            'nm_penyakit' => 'required|string|max:250',
            'version'     => 'nullable|string|max:50',
        ]);

        $penyakit = Penyakit::findOrFail($kd_penyakit);
        $penyakit->update([
            'nm_penyakit' => $request->nm_penyakit,
            'version'     => $request->version,
        ]);

        return redirect()->route('satusehat.icd10')->with('success', "Kode {$kd_penyakit} berhasil diperbarui.");
    }

    /**
     * Delete an ICD-10 code
     */
    public function destroy($kd_penyakit)
    {
        $penyakit = Penyakit::findOrFail($kd_penyakit);
        $penyakit->delete();

        return redirect()->route('satusehat.icd10')->with('success', "Kode {$kd_penyakit} berhasil dihapus.");
    }

    /**
     * Import ICD-10 codes from CSV
     * Expected CSV headers: CODE, DISPLAY, VERSION
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return redirect()->route('satusehat.icd10')->with('error', 'Gagal membuka file CSV.');
        }

        // Read header row
        $header = fgetcsv($handle, 0, ',');
        if (!$header) {
            fclose($handle);
            return redirect()->route('satusehat.icd10')->with('error', 'File CSV kosong.');
        }

        // Normalize headers (trim whitespace, uppercase)
        $header = array_map(function ($h) {
            return strtoupper(trim($h));
        }, $header);

        // Detect separator: try tab-separated if columns not found
        if (count($header) === 1 && str_contains($header[0], "\t")) {
            // Re-read as tab separated
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
            return redirect()->route('satusehat.icd10')->with('error', 'Header CSV harus mengandung kolom CODE dan DISPLAY.');
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
                'kd_penyakit' => $code,
                'nm_penyakit' => $display,
                'version'     => $version,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            // Insert in chunks of 500 for performance
            if (count($batch) >= 500) {
                Penyakit::upsert($batch, ['kd_penyakit'], ['nm_penyakit', 'version', 'updated_at']);
                $imported += count($batch);
                $batch = [];
            }
        }

        // Insert remaining
        if (!empty($batch)) {
            Penyakit::upsert($batch, ['kd_penyakit'], ['nm_penyakit', 'version', 'updated_at']);
            $imported += count($batch);
        }

        fclose($handle);

        return redirect()->route('satusehat.icd10')->with('success', "Import selesai! {$imported} kode berhasil diimpor, {$skipped} baris dilewati.");
    }

    /**
     * JSON search endpoint for Select2 autocomplete on the examination form
     * GET /api/search/diagnosis?q=...
     */
    public function apiSearch(Request $request)
    {
        $q = $request->input('q', $request->input('term', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Penyakit::where('kd_penyakit', 'like', "%{$q}%")
            ->orWhere('nm_penyakit', 'like', "%{$q}%")
            ->limit(20)
            ->get(['kd_penyakit', 'nm_penyakit']);

        return response()->json($results);
    }
}
