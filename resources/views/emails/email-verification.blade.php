<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác minh tài khoản Pharmacity</title>
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
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .header {
            background: linear-gradient(135deg, #065f46 0%, #047857 50%, #059669 100%);
            color: white;
            padding: 50px 30px;
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
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            animation: float 25s linear infinite;
            z-index: 1;
        }

        @keyframes float {
            0% { transform: translateX(-50px) translateY(-50px); }
            100% { transform: translateX(0px) translateY(0px); }
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .logo {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 12px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header-subtitle {
            font-size: 18px;
            opacity: 0.95;
            font-weight: 400;
            margin-bottom: 8px;
        }

        .header-description {
            font-size: 16px;
            opacity: 0.8;
            font-weight: 300;
        }

        .content {
            padding: 50px 30px;
            background: #ffffff;
        }

        .greeting {
            font-size: 26px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 24px;
        }

        .highlight {
            color: #059669;
            font-weight: 600;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            padding: 2px 8px;
            border-radius: 6px;
        }

        .message {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 24px;
            line-height: 1.8;
        }

        .verification-section {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #059669;
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            margin: 40px 0;
            position: relative;
            overflow: hidden;
        }

        .verification-section::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #059669, #10b981, #059669);
            border-radius: 16px;
            z-index: -1;
            animation: borderGlow 3s ease-in-out infinite alternate;
        }

        @keyframes borderGlow {
            0% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .verification-label {
            font-size: 16px;
            color: #065f46;
            font-weight: 500;
            margin-bottom: 16px;
        }

        .verification-code {
            font-size: 48px;
            font-weight: 900;
            color: #065f46;
            letter-spacing: 12px;
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
            margin: 20px 0;
            text-shadow: 0 2px 4px rgba(6, 95, 70, 0.2);
            position: relative;
        }

        .verification-instruction {
            font-size: 14px;
            color: #047857;
            font-weight: 500;
            margin-top: 16px;
        }

        .warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);
            border-left: 4px solid #f59e0b;
            border-radius: 12px;
            padding: 28px;
            margin: 35px 0;
            position: relative;
        }

        .warning::before {
            content: '⚠️';
            position: absolute;
            top: 28px;
            left: 28px;
            font-size: 22px;
        }

        .warning-title {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 16px;
            margin-left: 40px;
            font-size: 16px;
        }

        .warning ul {
            margin-left: 40px;
            color: #92400e;
        }

        .warning li {
            margin-bottom: 10px;
            font-weight: 500;
        }

        .benefits {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
        }

        .benefits-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 20px;
            text-align: center;
        }

        .benefits-list {
            list-style: none;
            padding: 0;
        }

        .benefits-list li {
            background: white;
            padding: 16px 20px;
            margin-bottom: 12px;
            border-radius: 10px;
            border-left: 4px solid #3b82f6;
            font-weight: 500;
            color: #1e40af;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);
            transition: transform 0.2s ease;
        }

        .benefits-list li:hover {
            transform: translateX(4px);
        }

        .contact-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
        }

        .contact-title {
            font-size: 18px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 20px;
            text-align: center;
        }

        .contact-list {
            list-style: none;
            padding: 0;
        }

        .contact-list li {
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 500;
            color: #6b7280;
        }

        .contact-list li:last-child {
            border-bottom: none;
        }

        .footer {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .footer-logo {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #10b981;
        }

        .footer-tagline {
            font-size: 16px;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .footer-text {
            font-size: 14px;
            opacity: 0.7;
            margin-bottom: 8px;
        }

        .footer-copyright {
            font-size: 12px;
            opacity: 0.6;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 640px) {
            .email-wrapper {
                padding: 20px 10px;
            }
            
            .content {
                padding: 40px 20px;
            }
            
            .header {
                padding: 40px 20px;
            }
            
            .logo {
                font-size: 30px;
            }
            
            .verification-code {
                font-size: 36px;
                letter-spacing: 8px;
            }
            
            .verification-section {
                padding: 30px 20px;
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
                    <div class="header-subtitle">Xác minh tài khoản của bạn</div>
                    <div class="header-description">Nền tảng dược phẩm trực tuyến hàng đầu Việt Nam</div>
                </div>
            </div>

            <div class="content">
                <h1 class="greeting">Xin chào <span class="highlight">{{ $user->email }}</span>!</h1>

                <p class="message">
                    🎉 Chúc mừng bạn đã đăng ký thành công tài khoản tại <strong>Pharmacity</strong>! 
                    Để đảm bảo tính bảo mật và hoàn tất quá trình đăng ký, chúng tôi cần xác minh địa chỉ email của bạn.
                </p>

                <div class="verification-section">
                    <div class="verification-label">Mã xác minh của bạn</div>
                    <div class="verification-code">{{ $verificationCode }}</div>
                    <div class="verification-instruction">
                        Vui lòng nhập mã này trong ứng dụng để hoàn tất việc xác minh
                    </div>
                </div>

                <div class="warning">
                    <div class="warning-title">Thông tin bảo mật quan trọng</div>
                    <ul>
                        <li><strong>Thời hạn:</strong> Mã xác minh này có hiệu lực trong vòng <strong>15 phút</strong></li>
                        <li><strong>Bảo mật:</strong> Tuyệt đối không chia sẻ mã này với bất kỳ ai</li>
                        <li><strong>Lưu ý:</strong> Nếu bạn không yêu cầu tạo tài khoản, vui lòng bỏ qua email này</li>
                        <li><strong>Hỗ trợ:</strong> Liên hệ ngay với chúng tôi nếu gặp bất kỳ vấn đề nào</li>
                    </ul>
                </div>

                <div class="benefits">
                    <div class="benefits-title">✨ Quyền lợi sau khi xác minh thành công</div>
                    <ul class="benefits-list">
                        <li>🛒 Mua sắm hàng nghìn sản phẩm dược phẩm chính hãng</li>
                        <li>💬 Tư vấn miễn phí với đội ngũ dược sĩ chuyên nghiệp 24/7</li>
                        <li>📦 Giao hàng nhanh chóng và đảm bảo chất lượng</li>
                        <li>🎁 Nhận ngay ưu đãi và khuyến mãi độc quyền</li>
                        <li>📱 Theo dõi đơn hàng và lịch sử mua sắm dễ dàng</li>
                        <li>🔒 Thông tin cá nhân được bảo mật tuyệt đối</li>
                    </ul>
                </div>

                <div class="contact-section">
                    <div class="contact-title">🤝 Cần hỗ trợ? Chúng tôi luôn sẵn sàng!</div>
                    <ul class="contact-list">
                        <li>📧 <strong>Email:</strong> support@pharmacity.com</li>
                        <li>📞 <strong>Hotline:</strong> 1900-6789 (Miễn phí, 24/7)</li>
                        <li>💬 <strong>Chat trực tuyến:</strong> <a href="https://pharmacy.ngockhanh.me" style="color: #059669;">pharmacy.ngockhanh.me</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer">
                <div class="footer-logo">Pharmacity</div>
                <div class="footer-tagline">Sức khỏe là ưu tiên hàng đầu của chúng tôi</div>
                <p class="footer-text">Cảm ơn bạn đã tin tưởng và lựa chọn Pharmacity</p>
                <p class="footer-text">Email này được gửi tự động, vui lòng không trả lời trực tiếp.</p>
                <div class="footer-copyright">
                    © {{ date('Y') }} Pharmacity. Tất cả quyền được bảo lưu.
                </div>
            </div>
        </div>
    </div>
</body>

</html>