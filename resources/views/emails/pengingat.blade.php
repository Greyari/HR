<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat: {{ $pengingat->judul }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 20px 0;
            min-height: 100vh;
        }

        /* Email Container */
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        /* Header Section */
        .header {
            background:  #1F1F1F 0%;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="25" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></svg>') repeat;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% { transform: translateX(-50px); }
            100% { transform: translateX(50px); }
        }

        .logo {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
        }

        .logo::before {
            content: '🔔';
            font-size: 44px;
        }

        .header-title {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .header-subtitle {
            font-size: 16px;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 1;
        }

        /* Content Section */
        .content {
            padding: 40px 30px;
        }

        .reminder-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        .reminder-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.1) 0%, transparent 70%);
        }

        .reminder-title {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
        }

        .reminder-description {
            font-size: 15px;
            color: #6b7280;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .info-item {
            background: #ffffff;
            padding: 16px;
            border-radius: 8px;
            border: 0.5px solid #3d3d3d1d;

            text-align: center;
        }

        .info-label {
            font-size: 16px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .info-value {
            font-size: 13px;
            font-weight: 500;
            color: #1f2937;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-selesai {
            background: #d1fae5;
            color: #059669;
        }

        .status-terlambat {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Due Date Highlight */
        .due-date-card {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 24px;
            box-shadow: 0 8px 16px rgba(239, 68, 68, 0.3);
        }

        .due-date-label {
            font-size: 14px;
            font-weight: 500;
            opacity: 0.9;
            margin-bottom: 4px;
        }

        .due-date-value {
            font-size: 18px;
            font-weight: 700;
        }


        /* Alert Box */
.alert {
    background: #f5f5f5;
    color: #333;
    padding: 16px 20px;
    border-radius: 12px;
    font-size: 14px;
    margin-bottom: 24px;
    position: relative;
            box-shadow: 0 8px 16px rgba(164, 149, 149, 0.203);

}

.alert::before {
    content: '⚠️';
    position: absolute;
    left: 20px;
    top: 16px;
    font-size: 16px;
}

.alert-content {
    margin-left: 30px;
}


        /* Footer */
        .footer {
            background: #161616;
            padding: 32px 30px;
            text-align: center;
            border-top: 1px solid #3F3F3F;
        }

        .company-name {
            font-size: 16px;
            font-weight: 700;
            color: #E0E0E0;
            margin-bottom: 12px;
        }

        .footer-links {
            font-size: 13px;
            color: rgba(224, 224, 224, 0.7);
            margin-bottom: 16px;
        }

        .footer-links a {
            color: #FF8E53;
            text-decoration: none;
            margin: 0 12px;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .footer-links a:hover {
            color: #FFA071;
            text-decoration: underline;
        }

        .copyright {
            font-size: 12px;
            color: rgba(224, 224, 224, 0.5);
        }

        /* Responsive Design */
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px 0;
            }

            .email-wrapper {
                margin: 0 10px;
                border-radius: 12px;
            }

            .header {
                padding: 30px 20px;
            }

            .header-title {
                font-size: 24px;
            }

            .content {
                padding: 30px 20px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .cta-button {
                padding: 14px 28px;
                font-size: 15px;
            }

            .footer {
                padding: 24px 20px;
            }
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            .info-item {
                background: #f9fafb;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="header">
            <div class="logo"></div>
            <h1 class="header-title">Reminder System</h1>
            <p class="header-subtitle">Jangan lewatkan jadwal penting Anda</p>
        </div>

        <!-- Content -->
        <div class="content">

            <div class="reminder-card">
                <h2 class="reminder-title">{{ $pengingat->judul }}</h2>
                <p class="reminder-description">
                    {{ $pengingat->deskripsi }}
                </p>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Tanggal & Waktu</div>
                        <div class="info-value">{{ $pengingat->tanggal_jatuh_tempo->format('d M Y') }}<br>{{ $pengingat->tanggal_jatuh_tempo->format('H:i') }} WIB</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="status-badge status-{{ strtolower($pengingat->status) }}">{{ $pengingat->status }}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">PIC</div>
                        <div class="info-value">{{ $pengingat->peran ? $pengingat->peran->nama_peran : '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Sisa Waktu</div>
                        <div class="info-value">{{ $pengingat->sisa_waktu }}</div>
                    </div>
                </div>
            </div>

            <div class="due-date-card">
                <div class="due-date-label">DEADLINE APPROACHING</div>
                <div class="due-date-value">{{ $pengingat->tanggal_jatuh_tempo->format('d F Y - H:i') }} WIB</div>
            </div>

            <div class="alert">
                <div class="alert-content">
                    <strong>Catatan:</strong> Email ini dikirim otomatis H-7 sebelum tanggal jatuh tempo.
                    Jika sudah selesai, ubah status menjadi <strong>"Selesai"</strong> agar email tidak terkirim lagi.
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="company-name">PT Kreatif Sistem Indonesia</div>
            <div class="copyright">
                © 2025 PT Kreatif Sistem Indonesia. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>
