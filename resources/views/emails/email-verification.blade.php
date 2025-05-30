<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác minh tài khoản Pharmacity</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            margin: -20px -20px 30px -20px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .content {
            padding: 0 20px;
        }
        .verification-code {
            background-color: #f8f9fa;
            border: 2px dashed #667eea;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            margin: 25px 0;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            margin: 30px -20px -20px -20px;
            border-radius: 0 0 10px 10px;
            color: #6c757d;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
            font-weight: bold;
        }
        .highlight {
            color: #667eea;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏥 Pharmacy Store</h1>
            <p style="margin: 10px 0 0 0; font-size: 18px;">Xác minh tài khoản của bạn</p>
        </div>
        
        <div class="content">
            <h2>Xin chào <span class="highlight">{{ $user->email }}</span>!</h2>
            
            <p>Cảm ơn bạn đã đăng ký tài khoản tại <strong>Pharmacity</strong> - nền tảng dược phẩm trực tuyến hàng đầu Việt Nam.</p>
            
            <p>Để hoàn tất quá trình đăng ký và bảo mật tài khoản của bạn, vui lòng sử dụng mã xác minh dưới đây:</p>
            
            <div class="verification-code">
                <p style="margin: 0 0 10px 0; font-size: 16px; color: #666;">Mã xác minh của bạn:</p>
                <div class="code">{{ $verificationCode }}</div>
                <p style="margin: 15px 0 0 0; color: #888; font-size: 14px;">Vui lòng nhập mã này vào ứng dụng</p>
            </div>
            
            <div class="warning">
                <strong>⚠️ Lưu ý quan trọng:</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Mã xác minh này có hiệu lực trong <strong>15 phút</strong></li>
                    <li>Không chia sẻ mã này với bất kỳ ai</li>
                    <li>Nếu bạn không yêu cầu tạo tài khoản, vui lòng bỏ qua email này</li>
                </ul>
            </div>
            
            <p>Sau khi xác minh thành công, bạn sẽ có thể:</p>
            <ul>
                <li>🛒 Mua sắm các sản phẩm dược phẩm chất lượng</li>
                <li>💬 Tư vấn trực tuyến với dược sĩ chuyên nghiệp</li>
                <li>📋 Theo dõi đơn hàng và lịch sử mua hàng</li>
                <li>🎁 Nhận các ưu đãi và khuyến mãi đặc biệt</li>
            </ul>
            
            <p>Nếu bạn gặp bất kỳ khó khăn nào trong quá trình xác minh, vui lòng liên hệ với chúng tôi qua:</p>
            <ul>
                <li>📧 Email: support@pharmacity.com</li>
                <li>📞 Hotline: 1900-xxxx</li>
                <li>💬 Chat trực tuyến trên website</li>
            </ul>
        </div>
        
        <div class="footer">
            <p><strong>Pharmacity</strong> - Sức khỏe là ưu tiên hàng đầu</p>
            <p>Email này được gửi tự động, vui lòng không trả lời trực tiếp.</p>
            <p style="margin-top: 15px;">
                <small>© {{ date('Y') }} Pharmacity. Tất cả quyền được bảo lưu.</small>
            </p>
        </div>
    </div>
</body>
</html>