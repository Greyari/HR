<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pengingat: {{ $pengingat->judul }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f4f6;
            color: #333;
            margin: 0;
            padding: 20px 0;
        }
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        /* Header */
        .header {
            background: #667eea;
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        /* Content */
        .content {
            padding: 30px 25px;
        }
        .reminder-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2d3748;
        }
        .reminder-desc {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
            color: #4a5568;
        }
        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .info-label {
            font-weight: bold;
            width: 40%;
            color: #4a5568;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background: #ffeeba;
            color: #856404;
        }
        .status-selesai {
            background: #c3e6cb;
            color: #155724;
        }
        .status-terlambat {
            background: #f5c6cb;
            color: #721c24;
        }
        /* Highlight Box */
        .due-date {
            background: #ff6b6b;
            color: white;
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 16px;
            font-weight: bold;
        }
        /* Alert */
        .alert {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1.5;
        }
        /* Footer */
        .footer {
            background: #f7fafc;
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>Reminder</h1>
            <p>Jangan lewatkan jadwal Anda</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="reminder-title">{{ $pengingat->judul }}</div>
            <div class="reminder-desc">{{ $pengingat->deskripsi }}</div>

            <table class="info-table">
                <tr>
                    <td class="info-label">Tanggal Jatuh Tempo</td>
                    <td>{{ $pengingat->tanggal_jatuh_tempo->format('d F Y, H:i') }} WIB</td>
                </tr>
                <tr>
                    <td class="info-label">Status</td>
                    <td>
                        <span class="status-badge status-{{ strtolower($pengingat->status) }}">
                            {{ $pengingat->status }}
                        </span>
                    </td>
                </tr>
                @if($pengingat->peran)
                <tr>
                    <td class="info-label">PIC</td>
                    <td>{{ $pengingat->peran->nama_peran }}</td>
                </tr>
                @endif
                <tr>
                    <td class="info-label">Sisa Waktu</td>
                    <td>{{ $pengingat->sisa_waktu }}</td>
                </tr>
            </table>

            <div class="alert">
                Email ini dikirim otomatis H-7 sebelum tanggal jatuh tempo.
                Jika sudah selesai, ubah status menjadi <b>"Selesai"</b> agar email tidak terkirim lagi.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <strong>PT Kreatif Sistem Indonesia</strong><br>
        </div>
    </div>
</body>
</html>
