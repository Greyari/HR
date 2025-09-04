<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pengingat: {{ $pengingat->judul }}</title>
    <style>
        /* Reset basic style */
        body, p, h1, h2, h3, a {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            background-color: #ffffff;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background-color: #4CAF50;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .header h2 {
            font-size: 24px;
        }

        .content {
            padding: 20px;
        }

        .content p {
            margin-bottom: 15px;
            font-size: 16px;
        }

        .footer {
            background-color: #f0f0f0;
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #555;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff !important;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }

        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Pengingat: {{ $pengingat->judul }}</h2>
        </div>
        <div class="content">
            <p>{{ $pengingat->deskripsi }}</p>
            <p><strong>Tanggal Jatuh Tempo:</strong> {{ $pengingat->tanggal_jatuh_tempo->format('d-m-Y H:i') }}</p>
            <p><strong>Reminder ini dikirim H-7 sebelum jatuh tempo.</strong></p>
            <a href="#" class="button">Lihat Detail</a>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} PT Kreatif Sistem Indonesia. Semua hak cipta dilindungi.
        </div>
    </div>
</body>
</html>
