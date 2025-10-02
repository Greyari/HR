<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Password Berhasil Diubah</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
      position: relative;
    }

    /* Animated Background Circles */
    .bg-animation {
      position: absolute; width: 100%; height: 100%; overflow: hidden; z-index: 0;
    }
    .circle { position: absolute; border-radius: 50%; background: rgba(255, 255, 255, 0.1); animation: float 15s infinite ease-in-out; }
    .circle:nth-child(1) { width: 80px; height: 80px; top: 10%; left: 20%; animation-delay: 0s; }
    .circle:nth-child(2) { width: 60px; height: 60px; top: 60%; left: 80%; animation-delay: 2s; }
    .circle:nth-child(3) { width: 100px; height: 100px; top: 80%; left: 10%; animation-delay: 4s; }
    .circle:nth-child(4) { width: 50px; height: 50px; top: 30%; left: 70%; animation-delay: 1s; }

    @keyframes float {
      0%,100% { transform: translateY(0) rotate(0deg); opacity: 0.5; }
      50% { transform: translateY(-30px) rotate(180deg); opacity: 0.8; }
    }

    /* Card */
    .card {
      background: rgba(255,255,255,0.95);
      backdrop-filter: blur(10px);
      padding: 40px 35px;
      border-radius: 25px;
      box-shadow: 0 25px 50px rgba(0,0,0,0.3);
      text-align: center;
      max-width: 420px;
      width: 90%;
      position: relative;
      z-index: 1;
      animation: slideUp 0.8s cubic-bezier(0.68,-0.55,0.265,1.55);
    }
    @keyframes slideUp {
      from { opacity: 0; transform: translateY(50px) scale(0.9); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Success Icon */
    .success-icon { position: relative; width: 100px; height: 100px; margin: 0 auto 25px; }
    .circle-border {
      width: 100px; height: 100px;
      border-radius: 50%;
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      display: flex; align-items: center; justify-content: center;
      animation: scaleIn 0.6s cubic-bezier(0.68,-0.55,0.265,1.55) 0.3s backwards, bounce 2s ease-in-out 1.2s infinite;
      position: relative;
      box-shadow: 0 10px 30px rgba(16,185,129,0.4);
    }
    @keyframes scaleIn { from { transform: scale(0); opacity:0; } to { transform: scale(1); opacity:1; } }
    @keyframes bounce { 0%,100% { transform: scale(1); } 50% { transform: scale(1.05); } }

    .checkmark { width: 50px; height: 50px; }
    .checkmark-path {
      stroke-dasharray: 48; stroke-dashoffset: 48;
      animation: draw 0.5s ease forwards 0.6s;
    }
    @keyframes draw { to { stroke-dashoffset: 0; } }

    /* Sparkle Burst Looping */
    .sparkle {
      position: absolute;
      width: 8px; height: 8px;
      background: #fbbf24;
      border-radius: 50%;
      opacity: 0;
      animation: sparkleBurst 1.5s ease-out infinite;
    }
    .sparkle:nth-child(2) { top: -10px; left: 50%; transform: translateX(-50%); animation-delay: 0.6s; }
    .sparkle:nth-child(3) { right: -10px; top: 50%; transform: translateY(-50%); animation-delay: 0.9s; }
    .sparkle:nth-child(4) { bottom: -10px; left: 50%; transform: translateX(-50%); animation-delay: 1.2s; }
    .sparkle:nth-child(5) { left: -10px; top: 50%; transform: translateY(-50%); animation-delay: 1.5s; }
    .sparkle:nth-child(6) { top: 0; left: 0; animation-delay: 0.3s; }
    .sparkle:nth-child(7) { bottom: 0; right: 0; animation-delay: 1.8s; }

    @keyframes sparkleBurst {
      0% { transform: scale(0); opacity: 0; }
      30% { transform: scale(1.6); opacity: 1; }
      60% { transform: scale(0.8); opacity: 0.6; }
      100% { transform: scale(0); opacity: 0; }
    }

    /* Text */
    .message {
      font-size: 24px; font-weight: 700; margin-bottom: 12px;
      background: linear-gradient(135deg,#667eea 0%,#764ba2 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: fadeIn 0.8s ease 0.9s backwards;
    }
    .sub-message {
      font-size: 15px; color: #6b7280; line-height: 1.6; margin-bottom: 25px;
      animation: fadeIn 0.8s ease 1.1s backwards;
    }
    @keyframes fadeIn { from { opacity:0; transform:translateY(10px);} to {opacity:1; transform:translateY(0);} }

    /* Info Box */
    .info-box {
      background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
      border-left: 4px solid #10b981;
      padding: 14px 18px;
      border-radius: 10px;
      text-align: left;
      animation: fadeIn 0.8s ease 1.3s backwards;
    }
    .info-box-title { font-size: 13px; font-weight: 600; color: #065f46; margin-bottom: 5px; display: flex; align-items: center; gap: 8px; }
    .info-box-text { font-size: 12px; color: #047857; line-height: 1.5; }
  </style>
</head>
<body>
  <!-- Animated Background -->
  <div class="bg-animation">
    <div class="circle"></div><div class="circle"></div><div class="circle"></div><div class="circle"></div>
  </div>

  <!-- Card -->
  <div class="card">
    <div class="success-icon">
      <div class="circle-border">
        <svg class="checkmark" viewBox="0 0 52 52">
          <path class="checkmark-path" fill="none" stroke="#fff" stroke-width="5" d="M14 27l7 7 17-17"/>
        </svg>
      </div>
      <!-- Sparkles (lebih banyak, looping) -->

      <div class="sparkle"></div>
      <div class="sparkle"></div>
      <div class="sparkle"></div>
      <div class="sparkle"></div>
      <div class="sparkle"></div>
      <div class="sparkle"></div>
      <div class="sparkle"></div>
    </div>

    <div class="message">Password Berhasil Diubah!</div>
    <div class="sub-message">
      Selamat! Password Anda telah berhasil diperbarui dengan aman.
    </div>

    <div class="info-box">
      <div class="info-box-title"><span>🔐</span><span>Tips Keamanan</span></div>
      <div class="info-box-text">Jangan bagikan password Anda kepada siapapun.</div>
    </div>
  </div>
</body>
</html>
