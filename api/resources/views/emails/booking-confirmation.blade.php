<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đặt phòng</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .booking-info {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }

        .booking-number {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        .room-details {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin: 20px 0;
            overflow: hidden;
        }

        .room-header {
            background-color: #667eea;
            color: white;
            padding: 15px 20px;
            font-weight: 600;
        }

        .room-item {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .room-item:last-child {
            border-bottom: none;
        }

        .total-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #667eea;
        }

        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .footer {
            background-color: #343a40;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
        }

        .important-note {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }

        .important-note h4 {
            color: #155724;
            margin-bottom: 10px;
        }

        .important-note p {
            color: #155724;
            margin: 5px 0;
        }

        @media (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }

            .info-row {
                flex-direction: column;
            }

            .info-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🏨 {{ $hotel->name ?? 'Khách sạn' }}</h1>
            <p>Cảm ơn bạn đã đặt phòng tại khách sạn chúng tôi</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Xin chào {{ $booking->first_name }} {{ $booking->last_name }},</h2>
            <p>Chúng tôi rất vui mừng xác nhận đặt phòng của bạn. Dưới đây là thông tin chi tiết:</p>

            <!-- Booking Information -->
            <div class="booking-info">
                <div class="booking-number">
                    Mã đặt phòng: {{ $booking->booking_number }}
                </div>

                <div class="info-row">
                    <span class="info-label">Tên khách hàng:</span>
                    <span class="info-value">{{ $booking->first_name }} {{ $booking->last_name }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $booking->email }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Số điện thoại:</span>
                    <span class="info-value">{{ $booking->phone_number }}</span>
                </div>

                @if($booking->nationality)
                <div class="info-row">
                    <span class="info-label">Quốc tịch:</span>
                    <span class="info-value">{{ $booking->nationality }}</span>
                </div>
                @endif

                <div class="info-row">
                    <span class="info-label">Ngày nhận phòng:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($booking->check_in)->format('d/m/Y') }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Ngày trả phòng:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($booking->check_out)->format('d/m/Y') }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Số đêm:</span>
                    <span class="info-value">{{ $booking->nights }} đêm</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Số khách:</span>
                    <span class="info-value">{{ $booking->guests }} người</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Trạng thái:</span>
                    <span class="info-value">
                        <span class="status-badge status-pending">{{ ucfirst($booking->status) }}</span>
                    </span>
                </div>
            </div>

            <!-- Room Details -->
            @if(!empty($bookingDetails))
            <div class="room-details">
                <div class="room-header">
                    Chi tiết phòng đã đặt
                </div>
                @foreach($bookingDetails as $detail)
                <div class="room-item">
                    <div class="info-row">
                        <span class="info-label">Loại phòng:</span>
                        <span class="info-value">{{ $detail['roomtype_name'] ?? 'Không xác định' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số lượng:</span>
                        <span class="info-value">{{ $detail['quantity'] }} phòng</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số khách:</span>
                        <span class="info-value">{{ $detail['adults'] }} người lớn{{ $detail['children'] > 0 ? ', ' . $detail['children'] . ' trẻ em' : '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Giá mỗi đêm:</span>
                        <span class="info-value">{{ number_format($detail['price_per_night'], 0, ',', '.') }} VNĐ</span>
                    </div>
                    @if($detail['discount_amount'] > 0)
                    <div class="info-row">
                        <span class="info-label">Giảm giá:</span>
                        <span class="info-value">-{{ number_format($detail['discount_amount'], 0, ',', '.') }} VNĐ</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Tổng tiền phòng:</span>
                        <span class="info-value"><strong>{{ number_format($detail['total_amount'], 0, ',', '.') }} VNĐ</strong></span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Total Section -->
            <div class="total-section">
                <div class="info-row">
                    <span class="info-label">Tổng phụ thu:</span>
                    <span class="info-value">{{ number_format($booking->tax_amount ?? 0, 0, ',', '.') }} VNĐ</span>
                </div>
                @if($booking->discount_amount > 0)
                <div class="info-row">
                    <span class="info-label">Tổng giảm giá:</span>
                    <span class="info-value">-{{ number_format($booking->discount_amount, 0, ',', '.') }} VNĐ</span>
                </div>
                @endif
                <hr style="margin: 15px 0; border: 1px solid #667eea;">
                <div class="total-amount">
                    Tổng thanh toán: {{ number_format($booking->total_amount, 0, ',', '.') }} VNĐ
                </div>
            </div>

            <!-- Important Notes -->
            <div class="important-note">
                <h4>📋 Thông tin quan trọng:</h4>
                <p>• Vui lòng mang theo giấy tờ tùy thân khi nhận phòng</p>
                <p>• Thời gian nhận phòng: 14:00 - Thời gian trả phòng: 12:00</p>
                <p>• Để thay đổi hoặc hủy đặt phòng, vui lòng liên hệ với chúng tôi trước 24 giờ</p>
                <p>• Mã đặt phòng: <strong>{{ $booking->booking_number }}</strong></p>
            </div>

            <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua email hoặc số điện thoại được cung cấp.</p>

            <p>Cảm ơn bạn đã tin tướng và chọn chúng tôi!</p>

            <p><strong>{{ $hotel->name ?? 'Khách sạn' }}</strong></p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $hotel->name ?? 'Khách sạn' }}. Tất cả quyền được bảo lưu.</p>
            @if($hotel->email ?? false)
            <p>Email: <a href="mailto:{{ $hotel->email }}">{{ $hotel->email }}</a></p>
            @endif
            @if($hotel->phone ?? false)
            <p>Điện thoại: {{ $hotel->phone }}</p>
            @endif
            @if($hotel->address ?? false)
            <p>Địa chỉ: {{ $hotel->address }}</p>
            @endif
        </div>
    </div>
</body>
</html>