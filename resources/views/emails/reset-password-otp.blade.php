<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #02A0AC 0%, #018B96 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 30px;
        }

        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #02A0AC;
            letter-spacing: 8px;
            padding: 20px;
            background: #f0f9fa;
            text-align: center;
            margin: 25px 0;
            border-radius: 8px;
            border: 2px dashed #02A0AC;
        }

        .warning {
            color: #d93025;
            font-size: 13px;
            background: #fef2f2;
            padding: 12px;
            border-radius: 6px;
            margin-top: 20px;
        }

        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }

        .highlight {
            color: #02A0AC;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>e-SPPD</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Reset Password</p>
        </div>

        <div class="content">
            <p>Yth. <strong>{{ $name }}</strong>,</p>

            <p>Kami menerima permintaan reset password untuk akun Anda di sistem <strong>e-SPPD</strong>.</p>

            <p>Berikut adalah kode verifikasi Anda:</p>

            <div class="otp-code">{{ $otp }}</div>

            <p>Kode ini berlaku selama <span class="highlight">{{ $expiry }}</span>.</p>

            <p><strong>⚠️ Jangan bagikan kode ini kepada siapapun</strong>, termasuk pihak yang mengaku sebagai admin.
            </p>

            <div class="warning">
                <strong>Peringatan:</strong> Jika Anda tidak meminta reset password, segera hubungi Admin Kepegawaian.
                Akun Anda mungkin terancam keamanannya.
            </div>
        </div>

        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem e-SPPD.</p>
            <p>© {{ date('Y') }} Universitas Islam Negeri</p>
        </div>
    </div>
</body>

</html>
