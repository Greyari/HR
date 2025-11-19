<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Gaji;
use App\Models\PotonganGaji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class GajiController extends Controller
{

    public function availablePeriods()
    {
        $periods = DB::table('gaji')
            ->select('bulan', 'tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        return response()->json($periods);
    }

    /**
     * Menghitung dan menyimpan data gaji untuk semua pengguna pada bulan dan tahun berjalan.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateAll()
    {
        $users = User::all();
        $potonganList = PotonganGaji::all();
        $result = [];

        $now = Carbon::now();
        $bulan = $now->month;
        $tahun = $now->year;

        $absensiCounts = Absensi::select('user_id', DB::raw('count(*) as total'))
            ->whereNotNull('checkin_date')
            ->whereMonth('checkin_date', $bulan)
            ->whereYear('checkin_date', $tahun)
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        // ⛔ MATIKAN OBSERVER SAAT PERHITUNGAN
        Gaji::withoutEvents(function () use ($users, $potonganList, $bulan, $tahun, $absensiCounts, &$result) {
            foreach ($users as $user) {

                $gajiPerHari = $user->gaji_per_hari ?? 0;
                $jumlahHariHadir = $absensiCounts->get($user->id, 0);

                $gajiPokok = $jumlahHariHadir * $gajiPerHari;
                $totalLembur = 0;

                $totalPotongan = 0;
                $detailPotongan = [];
                foreach ($potonganList as $potongan) {
                    $nilaiPotongan = ($gajiPokok * $potongan->persen / 100);
                    $totalPotongan += $nilaiPotongan;
                    $detailPotongan[] = [
                        'nama_potongan' => $potongan->nama_potongan,
                        'persen'        => $potongan->persen,
                        'nilai'         => $nilaiPotongan,
                    ];
                }

                $gajiBersih = $gajiPokok + $totalLembur - $totalPotongan;

                $gaji = Gaji::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'bulan'   => $bulan,
                        'tahun'   => $tahun,
                    ],
                    [
                        'total_kehadiran' => $jumlahHariHadir,
                        'gaji_kotor'      => $gajiPokok,
                        'total_potongan'  => $totalPotongan,
                        'detail_potongan' => $detailPotongan,
                        'gaji_bersih'     => $gajiBersih,
                    ]
                );

                $result[] = [
                    'id' => $gaji->id,
                    'user' => ['id' => $user->id, 'nama' => $user->nama],
                    'total_kehadiran' => $jumlahHariHadir,
                    'gaji_kotor' => $gajiPokok,
                    'gaji_per_hari' => $gajiPerHari,
                    'total_lembur' => $totalLembur,
                    'potongan' => $detailPotongan,
                    'total_potongan' => $totalPotongan,
                    'gaji_bersih' => $gajiBersih,
                    'status' => $gaji->status,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ];
            }
        });

        return response()->json([
            'message' => 'Data gaji bulan ini berhasil dihitung',
            'data'    => $result
        ]);
    }

    /**
     * Memperbarui status pembayaran gaji.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Gaji  $gaji
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $gaji = Gaji::find($id);
        if (!$gaji) {
            return response()->json([
                'message' => "Gaji dengan ID $id tidak ditemukan"
            ], 404);
        }

        $request->validate([
            'status' => 'required|string',
        ]);

        $gaji->status = $request->status;
        $gaji->save();

        return response()->json([
            'message' => 'Status gaji berhasil diupdate',
            'data' => $gaji
        ]);
    }

    /**
     * Ekspor data HR (gaji, absensi, cuti, lembur) ke dalam file Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);
        $namaBulan = Carbon::create()->month($bulan)->locale('id')->monthName;

        // Eager load relasi untuk optimasi
        $users = User::with([
            'absensi' => fn($q) => $q->whereMonth('checkin_date', $bulan)->whereYear('checkin_date', $tahun)->orderBy('checkin_date'),
            'cuti' => fn($q) => $q->where(function($query) use ($bulan, $tahun) {
                $query->whereMonth('tanggal_mulai', $bulan)->whereYear('tanggal_mulai', $tahun)
                      ->orWhere(fn($q2) => $q2->whereMonth('tanggal_selesai', $bulan)->whereYear('tanggal_selesai', $tahun));
            })->orderBy('tanggal_mulai'),
            'lembur' => fn($q) => $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->orderBy('tanggal')
        ])->orderBy('nama')->get();

        // Ambil data gaji dalam satu query
        $gajiData = Gaji::where('bulan', $bulan)->where('tahun', $tahun)->get()->keyBy('user_id');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('HR System')
            ->setTitle("Laporan HR - {$namaBulan} {$tahun}")
            ->setSubject('Laporan Gaji, Absensi, Cuti, dan Lembur');

        // Buat setiap sheet
        $this->createRingkasanSheet($spreadsheet, $users, $gajiData, $bulan, $tahun, $namaBulan);
        $this->createGajiSheet($spreadsheet->createSheet(), $users, $gajiData);
        $this->createAbsensiSheet($spreadsheet->createSheet(), $users);
        $this->createCutiSheet($spreadsheet->createSheet(), $users);
        $this->createLemburSheet($spreadsheet->createSheet(), $users);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $fileName = "Laporan_HR_{$namaBulan}_{$tahun}.xlsx";

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName);
    }

    // ===================================================================================
    // PRIVATE HELPER METHODS FOR EXPORT
    // ===================================================================================

    private function createRingkasanSheet($spreadsheet, $users, $gajiData, $bulan, $tahun, $namaBulan)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ringkasan');

        $sheet->mergeCells('A1:F3');
        $sheet->setCellValue('A1', "LAPORAN HR BULAN " . strtoupper($namaBulan) . " $tahun");
        $this->applyCellStyle($sheet->getStyle('A1'), [
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2E86AB']],
        ]);

        $sheet->setCellValue('A5', 'Tanggal Export:')->setCellValue('B5', now()->format('d/m/Y H:i:s'));
        $sheet->setCellValue('A6', 'Periode:')->setCellValue('B6', "{$namaBulan} {$tahun}");

        // Tabel Ringkasan
        $sheet->setCellValue('A8', 'RINGKASAN DATA');
        $this->applyCellStyle($sheet->getStyle('A8'), ['font' => ['bold' => true, 'size' => 14], 'fill' => $this->getFillStyle('E8F4FD')]);

        $ringkasanData = [
            ['Metrik', 'Jumlah'],
            ['Total Karyawan', $users->count()],
            ['Total Record Absensi', $users->sum(fn($u) => $u->absensi->count())],
            ['Total Pengajuan Cuti', $users->sum(fn($u) => $u->cuti->count())],
            ['Total Request Lembur', $users->sum(fn($u) => $u->lembur->count())],
            ['Total Gaji Bersih', 'Rp ' . number_format($gajiData->sum('gaji_bersih'), 0, ',', '.')]
        ];
        $sheet->fromArray($ringkasanData, null, 'A9');

        $this->applyCellStyle($sheet->getStyle('A9:B14'), ['borders' => ['allBorders' => ['borderStyle' => 'thin']]]);
        $this->applyCellStyle($sheet->getStyle('A9:B9'), ['font' => ['bold' => true], 'fill' => $this->getFillStyle('D4E6F1')]);

        // Top 5 Kehadiran
        $sheet->setCellValue('D8', 'TOP 5 KEHADIRAN TERBAIK');
        $this->applyCellStyle($sheet->getStyle('D8'), ['font' => ['bold' => true, 'size' => 14], 'fill' => $this->getFillStyle('E8F5E8')]);

        $topKehadiran = $users->map(fn($u) => ['nama' => $u->nama, 'total_absensi' => $u->absensi->count()])
                             ->sortByDesc('total_absensi')->take(5)->values()->toArray();
        array_unshift($topKehadiran, ['Nama', 'Total Hadir']);
        $sheet->fromArray($topKehadiran, null, 'D9');

        $this->applyCellStyle($sheet->getStyle('D9:E14'), ['borders' => ['allBorders' => ['borderStyle' => 'thin']]]);
        $this->applyCellStyle($sheet->getStyle('D9:E9'), ['font' => ['bold' => true], 'fill' => $this->getFillStyle('D5E8D4')]);

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function createGajiSheet($sheet, $users, $gajiData)
    {
        $sheet->setTitle('Gaji');
        $header = ['No', 'Nama', 'Total Kehadiran', 'Gaji Per Hari', 'Gaji Kotor', 'Total Potongan', 'Gaji Bersih', 'Status'];
        $sheet->fromArray([$header], null, 'A1');
        $this->applyHeaderStyle($sheet->getStyle('A1:H1'), '2E86AB');

        $rowData = [];
        $detailPotonganData = [];
        $no = 1;

        foreach ($users as $user) {
            $gaji = $gajiData->get($user->id);
            if (!$gaji) continue;

            $gajiPerHari = $user->gaji_per_hari ?? 0;
            $rowData[] = [
                $no++,
                $user->nama,
                $gaji->total_kehadiran,
                $gajiPerHari,
                $gaji->gaji_kotor,
                $gaji->total_potongan,
                $gaji->gaji_bersih,
                $gaji->status,
            ];

            $details = is_array($gaji->detail_potongan) ? $gaji->detail_potongan : json_decode($gaji->detail_potongan, true);
            if (!empty($details)) {
                foreach ($details as $d) {
                    $detailPotonganData[] = [$user->nama, $d['nama_potongan'], $d['persen'] . '%', $d['nilai']];
                }
            }
        }

        if (!empty($rowData)) {
            $sheet->fromArray($rowData, null, 'A2');
            $lastRow = count($rowData) + 1;
            $this->applyTableStyle($sheet, "A1:H{$lastRow}", "D2:G{$lastRow}");
        }

        // Tabel Detail Potongan
        $startRowDetail = ($lastRow ?? 1) + 3;
        $sheet->setCellValue("A{$startRowDetail}", "DETAIL POTONGAN");
        $this->applyCellStyle($sheet->getStyle("A{$startRowDetail}"), ['font' => ['bold' => true, 'size' => 14]]);

        $headerDetail = ["Nama Karyawan", "Nama Potongan", "Persen", "Nilai"];
        $sheet->fromArray([$headerDetail], null, 'A' . ($startRowDetail + 1));
        $this->applyHeaderStyle($sheet->getStyle('A'.($startRowDetail + 1).':D'.($startRowDetail + 1)), 'FDEBD0', '000000');

        if(!empty($detailPotonganData)) {
            $sheet->fromArray($detailPotonganData, null, 'A' . ($startRowDetail + 2));
            $lastRowDetail = ($startRowDetail + 1) + count($detailPotonganData);
            $this->applyTableStyle($sheet, "A".($startRowDetail + 1).":D{$lastRowDetail}", "D".($startRowDetail + 2).":D{$lastRowDetail}");
        }

        $sheet->freezePane('A2');
    }

    private function createAbsensiSheet($sheet, $users)
    {
        $sheet->setTitle('Absensi');
        $header = ['No', 'Nama', 'Tanggal', 'Hari', 'Check In', 'Check Out', 'Durasi Kerja', 'Status'];
        $sheet->fromArray([$header], null, 'A1');
        $this->applyHeaderStyle($sheet->getStyle('A1:H1'), '28A745');

        $rowData = [];
        $no = 1;
        $dayMap = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        foreach ($users as $user) {
            foreach ($user->absensi as $absensi) {
                $checkin = Carbon::parse($absensi->checkin_time);
                $checkout = Carbon::parse($absensi->checkout_time);
                $durasi = ($absensi->checkin_time && $absensi->checkout_time)
                    ? $checkin->diff($checkout)->format('%hj %im')
                    : '-';

                $rowData[] = [
                    $no++,
                    $user->nama,
                    $absensi->checkin_date,
                    $dayMap[Carbon::parse($absensi->checkin_date)->dayOfWeek],
                    $absensi->checkin_time ?? '-',
                    $absensi->checkout_time ?? '-',
                    $durasi,
                    $absensi->status,
                ];
            }
        }

        if (!empty($rowData)) {
            $sheet->fromArray($rowData, null, 'A2');
            $this->applyTableStyle($sheet, "A1:H" . (count($rowData) + 1));
        }
        $sheet->freezePane('A2');
    }

    private function createCutiSheet($sheet, $users)
    {
        $sheet->setTitle('Cuti');
        $header = ['No', 'Nama', 'Tipe Cuti', 'Tanggal Mulai', 'Tanggal Selesai', 'Durasi (Hari)', 'Alasan', 'Status'];
        $sheet->fromArray([$header], null, 'A1');
        $this->applyHeaderStyle($sheet->getStyle('A1:H1'), 'FFC107');

        $rowData = [];
        $no = 1;
        foreach ($users as $user) {
            foreach ($user->cuti as $cuti) {
                $durasi = Carbon::parse($cuti->tanggal_mulai)->diffInDays(Carbon::parse($cuti->tanggal_selesai)) + 1;
                $rowData[] = [
                    $no++, $user->nama, $cuti->tipe_cuti, $cuti->tanggal_mulai, $cuti->tanggal_selesai,
                    $durasi, $cuti->alasan, $cuti->keterangan_status
                ];
            }
        }

        if (!empty($rowData)) {
            $sheet->fromArray($rowData, null, 'A2');
            $lastRow = count($rowData) + 1;
            $this->applyTableStyle($sheet, "A1:H{$lastRow}");
            $sheet->getColumnDimension('G')->setWidth(30);
            $sheet->getStyle("G2:G{$lastRow}")->getAlignment()->setWrapText(true);
        }
        $sheet->freezePane('A2');
    }

    private function createLemburSheet($sheet, $users)
    {
        $sheet->setTitle('Lembur');
        $header = ['No', 'Nama', 'Tanggal', 'Jam Mulai', 'Jam Selesai', 'Durasi (Jam)', 'Deskripsi', 'Status'];
        $sheet->fromArray([$header], null, 'A1');
        $this->applyHeaderStyle($sheet->getStyle('A1:H1'), 'DC3545');

        $rowData = [];
        $no = 1;
        foreach ($users as $user) {
            foreach ($user->lembur as $lembur) {
                $durasi = (strtotime($lembur->jam_selesai) - strtotime($lembur->jam_mulai)) / 3600;
                $rowData[] = [
                    $no++, $user->nama, $lembur->tanggal, $lembur->jam_mulai, $lembur->jam_selesai,
                    number_format($durasi, 1), $lembur->deskripsi, $lembur->keterangan_status
                ];
            }
        }

        if (!empty($rowData)) {
            $sheet->fromArray($rowData, null, 'A2');
            $lastRow = count($rowData) + 1;
            $this->applyTableStyle($sheet, "A1:H{$lastRow}");
            $sheet->getColumnDimension('G')->setWidth(35);
            $sheet->getStyle("G2:G{$lastRow}")->getAlignment()->setWrapText(true);
        }
        $sheet->freezePane('A2');
    }

    // ===================================================================================
    // STYLING HELPERS
    // ===================================================================================

    private function applyCellStyle($style, $rules)
    {
        $style->applyFromArray($rules);
    }

    private function getFillStyle($color)
    {
        return ['fillType' => 'solid', 'startColor' => ['rgb' => $color]];
    }

    private function applyHeaderStyle($style, $bgColor, $fontColor = 'FFFFFF')
    {
        $this->applyCellStyle($style, [
            'font' => ['bold' => true, 'color' => ['rgb' => $fontColor]],
            'fill' => $this->getFillStyle($bgColor),
            'alignment' => ['horizontal' => 'center'],
        ]);
    }

    private function applyTableStyle($sheet, $range, $numberFormatRange = null)
    {
        $this->applyCellStyle($sheet->getStyle($range), [
            'borders' => ['allBorders' => ['borderStyle' => 'thin']]
        ]);

        if ($numberFormatRange) {
            $sheet->getStyle($numberFormatRange)->getNumberFormat()->setFormatCode('#,##0');
        }

        $lastCol = explode(':', $range)[1][0];
        foreach (range('A', $lastCol) as $col) {
            if (!in_array($col, ['G'])) { // Kolom yang tidak di-autosize
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }
    }
}
