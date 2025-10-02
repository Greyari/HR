<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Reminder System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reset & Base Styles */
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.5;
            color: #333333;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            font-size: 14px;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            -webkit-font-smoothing: antialiased;
            background-color: #f5f5f5;
        }

        table, td {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        /* Wrapper Utama */
        .email-wrapper {
            background-color: #ffffff;
            margin: 15px auto;
            width: 100% !important;
            max-width: 600px !important;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        /* Header */
        .header {
            background: #1F1F1F;
            padding: 20px 15px;
            text-align: center;
            color: #ffffff;
        }
        .header-logo {
            font-size: 26px;
            margin-bottom: 10px;
            line-height: 1;
        }
        .header-title {
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 4px 0;
            line-height: 1.3;
        }
        .header-subtitle {
            font-size: 13px;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            line-height: 1.4;
        }

        /* Content */
        .content {
            padding: 20px 15px;
        }
        .card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 15px;
        }
        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 8px 0;
            line-height: 1.3;
        }
        .card-text {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 12px 0;
            line-height: 1.5;
        }

        /* Info Grid */
        .info-item {
            background: #ffffff;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .info-label {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 13px;
            font-weight: 500;
            color: #1f2937;
            line-height: 1.4;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 16px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-selesai { background: #d1fae5; color: #065f46; }
        .status-terlambat { background: #fee2e2; color: #991b1b; }

        /* Highlight Card */
        .highlight-card {
            background: #dc2626;
            color: #ffffff;
            padding: 14px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 15px;
        }
        .highlight-label {
            font-size: 11px;
            font-weight: 500;
            opacity: 0.9;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .highlight-value {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.3;
        }

        /* Alert Box */
        .alert {
            background: #f3f4f6;
            padding: 12px;
            border-radius: 6px;
            font-size: 12px;
            color: #4b5563;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        /* CTA Button */
        .cta-button a {
            display: inline-block;
            background: #1F1F1F;
            color: #ffffff !important;
            text-decoration: none !important;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
        }

        /* Footer */
        .footer {
            background: #161616;
            padding: 20px;
            text-align: center;
        }
        .company-name {
            font-size: 13px;
            font-weight: 700;
            color: #E0E0E0;
            margin: 0 0 6px 0;
        }
        .copyright {
            font-size: 11px;
            color: rgba(224, 224, 224, 0.6);
            line-height: 1.4;
        }

        /* Responsive Styles */
        @media screen and (max-width: 600px) {
            .email-wrapper {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 auto !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }
            .content, .header, .footer {
                padding: 15px 12px !important;
            }
            .info-grid-col {
                display: block !important;
                width: 100% !important;
                padding: 0 0 8px 0 !important;
            }
            .info-grid-col:last-child {
                padding-bottom: 0 !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f5f5f5;">
    <center>
        <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
            <tr>
                <td class="header" align="center">
                    <div style="margin-bottom:10px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#ffffff" viewBox="0 0 24 24">
                            <path d="M12 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm6-7h-1V7a5 5 0 0 0-10 0v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2zM8 7a4 4 0 1 1 8 0v3H8V7z"/>
                        </svg>
                    </div>
                    <h1 class="header-title">Reminder System</h1>
                    <p class="header-subtitle">Reset Password Akun Anda</p>
                </td>
            </tr>

            <tr>
                <td class="content">
                    <div class="card" style="text-align: center;">
                        <h2 class="card-title">Reset Password Diperlukan</h2>
                        <p class="card-text">
                            Kami menerima permintaan untuk mengatur ulang password akun HRIS Anda. Silakan klik tombol di bawah untuk melanjutkan.
                        </p>
                        <table border="0" cellpadding="0" cellspacing="0" class="cta-button" align="center">
                           <tr>
                              <td align="center" style="border-radius: 8px;" bgcolor="#1F1F1F">
                                 <a href="{{ $url }}" target="_blank">Reset Password Sekarang</a>
                              </td>
                           </tr>
                        </table>
                    </div>
                    <div class="highlight-card">
                        <div class="highlight-label">LINK AKAN KEDALUWARSA DALAM</div>
                        <div class="highlight-value">60 Menit</div>
                    </div>
                    <div class="alert">
                        <strong>Perhatian:</strong> Jika Anda tidak meminta reset password, mohon abaikan email ini. Keamanan akun Anda tetap terjaga.
                    </div>
                    <div class="alert">
                        <strong>Link tidak berfungsi?</strong> Salin dan tempel URL berikut di browser Anda: <br>
                        <a href="{{ $url }}" style="color: #1f2937; text-decoration: underline; word-break: break-all;">{{ $url }}</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="footer" align="center">
                    <p class="company-name">PT Kreatif Sistem Indonesia</p>
                    <p class="copyright">&copy; {{ date('Y') }} PT Kreatif Sistem Indonesia.</p>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
