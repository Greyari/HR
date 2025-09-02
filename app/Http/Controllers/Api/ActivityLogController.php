<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('user:id,nama')
            ->latest()
            ->get();

        $formatted = $logs->map(function ($log) {
            $changesArray = null;
            $description  = $log->description;

            // kalau description ada kata "Perubahan:"
            if (str_contains($log->description, 'Perubahan:')) {
                // ambil bagian sebelum "Perubahan:"
                $beforeChanges = explode('Perubahan:', $log->description, 2)[0];

                // ambil bagian setelah "Perubahan:"
                $changesPart = explode('Perubahan:', $log->description, 2)[1] ?? '';

                // pecah perubahannya berdasarkan koma
                $changesList = array_map('trim', explode(',', $changesPart));

                // parsing tiap perubahan "field: 'old' → 'new'"
                $changesArray = array_map(function ($change) {
                    if (preg_match("/(\w+): '([^']*)' → '([^']*)'/u", $change, $matches)) {
                        // cek apakah format tanggal
                        $old = $matches[2];
                        $new = $matches[3];

                        // coba parse tanggal
                        try {
                            $oldDate = Carbon::parse($old);
                            $old     = $oldDate->format('d-m-Y');
                        } catch (\Exception $e) {}

                        try {
                            $newDate = Carbon::parse($new);
                            $new     = $newDate->format('d-m-Y');
                        } catch (\Exception $e) {}

                        return [
                            'field' => $matches[1],
                            'old'   => $old,
                            'new'   => $new,
                        ];
                    }
                    return null;
                }, $changesList);

                $changesArray = array_filter($changesArray);

                // kalau changes sudah ada, jangan munculkan detail panjang di description
                if (!empty($changesArray)) {
                    $description = trim($beforeChanges);
                }
            }

            return [
                'id'          => $log->id,
                'user'        => $log->user->nama,
                'action'      => $log->action,
                'module'      => $log->module,
                'description' => $description,
                'changes'     => $changesArray,
                'created_at'  => $log->created_at->format('d-m-Y H:i:s'),
            ];
        });

        return response()->json([
            'message' => 'Daftar Activity Log',
            'data'    => $formatted->values(),
        ]);
    }
}
