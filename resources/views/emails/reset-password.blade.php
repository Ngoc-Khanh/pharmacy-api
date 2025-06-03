<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đặt lại mật khẩu - Pharmacity</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      line-height: 1.6;
      color: #374151;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 20px 0;
      min-height: 100vh;
    }

    .email-wrapper {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 40px 20px;
      min-height: 100vh;
    }

    .container {
      max-width: 600px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .header {
      background: linear-gradient(135deg, #065f46 0%, #047857 50%, #059669 100%);
      color: white;
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
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      animation: float 20s ease-in-out infinite;
      z-index: 1;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(1deg); }
    }

    .header-content {
      position: relative;
      z-index: 2;
    }

    .logo {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 8px;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .subtitle {
      font-size: 16px;
      opacity: 0.9;
      font-weight: 400;
    }

    .content {
      padding: 40px 30px;
      background: #ffffff;
    }

    .greeting {
      font-size: 24px;
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 20px;
    }

    .message {
      font-size: 16px;
      color: #6b7280;
      margin-bottom: 20px;
      line-height: 1.7;
    }

    .button-container {
      text-align: center;
      margin: 40px 0;
    }

    .button {
      display: inline-block;
      padding: 16px 32px;
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
      color: white;
      text-decoration: none;
      border-radius: 12px;
      font-weight: 600;
      font-size: 16px;
      transition: all 0.3s ease;
      box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.3);
      transform: translateY(0);
    }

    .button:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 35px -5px rgba(5, 150, 105, 0.4);
    }

    .warning {
      background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);
      border-left: 4px solid #f59e0b;
      padding: 24px;
      border-radius: 12px;
      margin: 30px 0;
      position: relative;
    }

    .warning::before {
      content: '⚠️';
      position: absolute;
      top: 24px;
      left: 24px;
      font-size: 20px;
    }

    .warning-title {
      font-weight: 600;
      color: #92400e;
      margin-bottom: 12px;
      margin-left: 32px;
    }

    .warning ul {
      margin-left: 32px;
      color: #92400e;
    }

    .warning li {
      margin-bottom: 8px;
    }

    .url-box {
      background: #f8fafc;
      border: 2px dashed #cbd5e1;
      border-radius: 8px;
      padding: 16px;
      margin: 20px 0;
      word-break: break-all;
      font-family: 'Monaco', 'Menlo', monospace;
      font-size: 14px;
      color: #475569;
    }

    .footer {
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      padding: 30px;
      text-align: center;
      border-top: 1px solid #e2e8f0;
    }

    .footer-logo {
      font-size: 18px;
      font-weight: 600;
      color: #059669;
      margin-bottom: 12px;
    }

    .footer-text {
      font-size: 14px;
      color: #64748b;
      margin-bottom: 8px;
    }

    .social-links {
      margin-top: 20px;
    }

    .social-link {
      display: inline-block;
      margin: 0 8px;
      color: #64748b;
      text-decoration: none;
      font-size: 14px;
      transition: color 0.3s ease;
    }

    .social-link:hover {
      color: #059669;
    }

    @media (max-width: 640px) {
      .email-wrapper {
        padding: 20px 10px;
      }
      
      .content {
        padding: 30px 20px;
      }
      
      .header {
        padding: 30px 20px;
      }
      
      .logo {
        font-size: 28px;
      }
      
      .button {
        padding: 14px 28px;
        font-size: 15px;
      }
    }
  </style>
</head>

<body>
  <div class="email-wrapper">
    <div class="container">
      <div class="header">
        <div class="header-content">
          <div class="logo">🏥 Pharmacity</div>
          <div class="subtitle">Nền tảng dược phẩm trực tuyến hàng đầu</div>
        </div>
      </div>

      <div class="content">
        <h1 class="greeting">Xin chào {{ $userName }}!</h1>

        <p class="message">
          Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại <strong>Pharmacity</strong>.
          Để đảm bảo tính bảo mật, vui lòng thực hiện theo hướng dẫn bên dưới.
        </p>

        <div class="button-container">
          <a href="{{ $resetUrl }}" class="button">
            🔐 Đặt lại mật khẩu ngay
          </a>
        </div>

        <div class="warning">
          <div class="warning-title">Thông tin bảo mật quan trọng</div>
          <ul>
            <li><strong>Thời hạn:</strong> Link này chỉ có hiệu lực trong vòng <strong>60 phút</strong></li>
            <li><strong>Sử dụng:</strong> Chỉ có thể sử dụng <strong>một lần duy nhất</strong></li>
            <li><strong>Bảo mật:</strong> Không chia sẻ link này với bất kỳ ai</li>
            <li><strong>Lưu ý:</strong> Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này</li>
          </ul>
        </div>

        <p class="message">
          <strong>Gặp khó khăn?</strong> Nếu nút bên trên không hoạt động, bạn có thể sao chép và dán link sau vào trình duyệt:
        </p>
        
        <div class="url-box">
          <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
        </div>

        <p class="message">
          Nếu bạn cần hỗ trợ thêm, đội ngũ chăm sóc khách hàng của chúng tôi luôn sẵn sàng giúp đỡ:
        </p>
        
        <ul class="message">
          <li>📧 Email: support@pharmacity.com</li>
          <li>📞 Hotline: 1900-6789</li>
          <li>💬 Chat trực tuyến: <a href="https://pharmacy.ngockhanh.me">pharmacy.ngockhanh.me</a></li>
        </ul>
      </div>

      <div class="footer">
        <div class="footer-logo">Pharmacity Store</div>
        <p class="footer-text">Sức khỏe là ưu tiên hàng đầu của chúng tôi</p>
        <p class="footer-text">© {{ date('Y') }} Pharmacity. Tất cả quyền được bảo lưu.</p>
        <p class="footer-text">Email này được gửi tự động, vui lòng không trả lời trực tiếp.</p>
        
        <div class="social-links">
          <a href="#" class="social-link">Chính sách bảo mật</a>
          <a href="#" class="social-link">Điều khoản sử dụng</a>
          <a href="#" class="social-link">Liên hệ</a>
        </div>
      </div>
    </div>
  </div>
</body>

</html>