import React, { createContext, useContext, useEffect, useState, useCallback } from 'react';
import type { ReactNode } from 'react';
import { tenantDetection } from '../services/tenantDetection';

interface LanguageContextType {
  currentLanguage: string;
  availableLanguages: string[];
  hotelConfig: any;
  themeData: any;
  languageInfo: any;
  loading: boolean;
  changeLanguage: (language: string) => Promise<void>;
  getLocalizedText: (key: string, fallback?: string) => string;
}

const LanguageContext = createContext<LanguageContextType | undefined>(undefined);

interface LanguageProviderProps {
  children: ReactNode;
}

// Static text translations
const staticTexts = {
  vi: {
    'booking.step1': '1. Tìm & Chọn Phòng',
    'booking.step2': '2. Thông Tin',
    'booking.step3': '3. Xác Nhận',
    'booking.language': 'Ngôn ngữ',
    'booking.hotline': 'Hotline',
    'room.capacity': 'Tối đa {count} người',
    'room.remaining': 'Còn {count} phòng',
    'room.recommended_rooms': 'Đề xuất: {count} phòng để chứa đủ khách',
    'room.view_gallery': 'Xem gallery',
    'room.more_images': '+{count} ảnh khác',
    'room.amenities.main': 'Tiện ích chính',
    'room.amenities.room': 'Tiện ích phòng',
    'room.amenities.bathroom': 'Tiện ích phòng tắm',
    'room.book_now': 'Đặt phòng ngay',
    'room.contact': 'Liên hệ',
    'room.bed_type': 'Loại giường',
    'room.area': 'Diện tích',
    'room.capacity_label': 'Sức chứa',
    'room.view': 'View',
    'room.price_per_night': '/đêm',
    // Room defaults
    'room.bed_type.default': 'Giường đôi king size',
    'room.area.default': '30m²',
    'room.view.default': 'View thành phố',
    'room.amenities.default.wifi': 'WiFi miễn phí',
    'room.amenities.default.tv': 'TV LCD',
    'room.amenities.default.ac': 'Máy lạnh',
    'room.amenities.default.minibar': 'Minibar',
    'room.amenities.default.safe': 'Két sắt',
    'room.amenities.default.desk': 'Bàn làm việc',
    'room.amenities.default.shower': 'Vòi sen',
    'room.amenities.default.hairdryer': 'Máy sấy tóc',
    'room.amenities.default.toiletries': 'Đồ vệ sinh cá nhân',
    // Room list
    'rooms.no_rooms_found': 'Không có phòng trống',
    'rooms.no_rooms_description': 'Vui lòng thử thay đổi ngày check-in/check-out hoặc số lượng khách.',
    'rooms.unexpected_data': 'Dữ liệu phòng không đúng định dạng',
    // Guest form
    'guest.form_title': 'Thông tin khách',
    'guest.first_name': 'Tên',
    'guest.last_name': 'Họ',
    'guest.email': 'Email',
    'guest.phone': 'Số điện thoại',
    'guest.nationality': 'Quốc tịch',
    'guest.special_requests': 'Yêu cầu đặc biệt',
    'guest.required': '*',
    'guest.first_name.placeholder': 'Nhập tên của bạn',
    'guest.last_name.placeholder': 'Nhập họ của bạn',
    'guest.email.placeholder': 'email@example.com',
    'guest.phone.placeholder': '+84 123 456 789',
    'guest.special_requests.placeholder': 'Ví dụ: Phòng tầng cao, giường đôi, yêu cầu đặc biệt khác...',
    'guest.nationality.placeholder': 'Chọn quốc tịch',
    'guest.continue': 'Tiếp tục',
    'guest.booking_loading': 'Đang Đặt Phòng...',
    'guest.confirm_booking': 'Xác Nhận Đặt Phòng',
    'guest.back_to_rooms': 'Quay lại chọn phòng',
    'guest.special_requests.note': 'Khách sạn sẽ cố gắng đáp ứng yêu cầu của bạn tùy theo tình hình thực tế.',
    'guest.terms.title': 'Quy định nhận phòng và trả phòng:',
    'guest.terms.checkin_checkout': 'Check-in: 14:00 | Check-out: 12:00',
    'guest.terms.dynamic_checkin_checkout': 'Check-in: {checkIn} | Check-out: {checkOut}',
    'guest.terms.free_cancellation': 'Hủy miễn phí trước 24 giờ',
    'guest.terms.prices_include': 'Giá đã bao gồm thuế và phí dịch vụ',
    'guest.terms.id_required': 'Vui lòng mang theo CMND/Passport khi check-in',
    // Validation errors
    'validation.first_name_required': 'Vui lòng nhập tên',
    'validation.last_name_required': 'Vui lòng nhập họ',
    'validation.email_required': 'Vui lòng nhập email',
    'validation.email_invalid': 'Email không hợp lệ',
    'validation.phone_required': 'Vui lòng nhập số điện thoại',
    'validation.phone_invalid': 'Số điện thoại không hợp lệ',
    'validation.nationality_required': 'Vui lòng chọn quốc tịch',
    // Booking search
    'search.checkin_date': 'Ngày Nhận Phòng',
    'search.checkout_date': 'Ngày Trả Phòng',
    'search.adults': 'Người Lớn',
    'search.children': 'Trẻ Em',
    'search.children_ages': 'Độ Tuổi Trẻ Em',
    'search.child_age_placeholder': 'Tuổi trẻ em {index}',
    'search.child_age_required': 'Vui lòng chọn tuổi cho tất cả trẻ em',
    'search.search_rooms': 'Tìm Phòng Trống',
    'search.searching': 'Đang Tìm Kiếm...',
    // Booking summary
    'summary.title': 'Tóm Tắt Đặt Phòng',
    'summary.checkin': 'Ngày nhận phòng',
    'summary.checkout': 'Ngày trả phòng',
    'summary.guests': 'Khách',
    'summary.nights': 'đêm',
    'summary.night': 'đêm',
    'summary.adults_count': '{count} người lớn',
    'summary.children_count': '{count} trẻ em',
    'summary.total_price': 'Tổng cộng',
    'summary.proceed_booking': 'Tiến Hành Đặt Phòng',
    'summary.remove': 'Xóa',
    'summary.quantity': 'Số lượng',
    'summary.price_per_night': 'Giá/đêm',
    'summary.subtotal': 'Tạm tính',
    'summary.no_rooms_selected': 'Chưa có phòng nào được chọn',
    'summary.selected_rooms': 'Phòng Đã Chọn',
    'summary.original_price': 'Tổng giá gốc',
    'summary.savings': 'Tiết kiệm',
    'summary.total_amount': 'Tổng tiền',
    'summary.basic_price': 'Giá cơ bản',
    'summary.rooms_nights': '{rooms} phòng × {nights} đêm',
    'summary.remove_room': 'Xóa phòng này',
    'summary.processing': 'Đang Xử Lý...',
    'summary.price_includes': '* Giá đã bao gồm thuế và phí dịch vụ',
    'summary.capacity_warning': 'Cảnh báo: Tổng sức chứa phòng ({current}/{required}) chưa đủ cho số khách.',
    'summary.capacity_sufficient': 'Sức chứa phòng đủ cho {guests} khách.',
    // Promotion & Room Card
    'promotion.count': '{count} Khuyến Mãi',
    'promotion.title': 'Chương Trình Khuyến Mãi',
    'promotion.savings': 'Tiết kiệm {amount} VND ({type})',
    'promotion.percentage': '{value}%',
    'promotion.fixed': 'Giá cố định',
    'promotion.no_promotion': 'Không áp dụng khuyến mãi',
    'promotion.subtotal': 'Tiểu kế',
    // Confirmation page
    'confirmation.loading': 'Đang tải thông tin xác nhận...',
    'confirmation.success': 'Đặt Phòng Thành Công!',
    'confirmation.booking_id': 'Mã đặt phòng',
    'confirmation.total_amount': 'Tổng tiền',
    'confirmation.guest_name': 'Khách hàng',
    'confirmation.duration': 'Thời gian',
    'confirmation.email_sent': 'Chúng tôi đã gửi email xác nhận đến địa chỉ của bạn.',
    'confirmation.new_booking': 'Đặt Phòng Mới',
    // Common
    'common.loading': 'Đang tải...',
    'common.error': 'Có lỗi xảy ra',
    'common.success': 'Thành công',
    'common.cancel': 'Hủy',
    'common.confirm': 'Xác nhận',
    'common.save': 'Lưu',
    'common.edit': 'Chỉnh sửa',
    'common.delete': 'Xóa',
    'common.add': 'Thêm',
    'common.search': 'Tìm kiếm'
  },
  en: {
    'booking.step1': '1. Search & Select Rooms',
    'booking.step2': '2. Guest Info',
    'booking.step3': '3. Confirmation',
    'booking.language': 'Language',
    'booking.hotline': 'Hotline',
    'room.capacity': 'Up to {count} guests',
    'room.remaining': '{count} rooms available',
    'room.recommended_rooms': 'Recommended: {count} rooms to accommodate all guests',
    'room.view_gallery': 'View gallery',
    'room.more_images': '+{count} more photos',
    'room.amenities.main': 'Main Amenities',
    'room.amenities.room': 'Room Amenities',
    'room.amenities.bathroom': 'Bathroom Amenities',
    'room.book_now': 'Book Now',
    'room.contact': 'Contact',
    'room.bed_type': 'Bed Type',
    'room.area': 'Area',
    'room.capacity_label': 'Capacity',
    'room.view': 'View',
    'room.price_per_night': '/night',
    // Room defaults
    'room.bed_type.default': 'King size double bed',
    'room.area.default': '30m²',
    'room.view.default': 'City view',
    'room.amenities.default.wifi': 'Free WiFi',
    'room.amenities.default.tv': 'LCD TV',
    'room.amenities.default.ac': 'Air conditioning',
    'room.amenities.default.minibar': 'Minibar',
    'room.amenities.default.safe': 'Safe',
    'room.amenities.default.desk': 'Work desk',
    'room.amenities.default.shower': 'Shower',
    'room.amenities.default.hairdryer': 'Hair dryer',
    'room.amenities.default.toiletries': 'Personal toiletries',
    // Room list
    'rooms.no_rooms_found': 'No rooms available',
    'rooms.no_rooms_description': 'Please try changing check-in/check-out dates or number of guests.',
    'rooms.unexpected_data': 'Unexpected room data format',
    // Guest form
    'guest.form_title': 'Guest Information',
    'guest.first_name': 'First Name',
    'guest.last_name': 'Last Name',
    'guest.email': 'Email',
    'guest.phone': 'Phone Number',
    'guest.nationality': 'Nationality',
    'guest.special_requests': 'Special Requests',
    'guest.required': '*',
    'guest.first_name.placeholder': 'Enter your first name',
    'guest.last_name.placeholder': 'Enter your last name',
    'guest.email.placeholder': 'email@example.com',
    'guest.phone.placeholder': '+84 123 456 789',
    'guest.special_requests.placeholder': 'e.g., High floor room, double bed, other special requests...',
    'guest.nationality.placeholder': 'Select nationality',
    'guest.continue': 'Continue',
    'guest.booking_loading': 'Booking...',
    'guest.confirm_booking': 'Confirm Booking',
    'guest.back_to_rooms': 'Back to room selection',
    'guest.special_requests.note': 'The hotel will try to accommodate your requests based on availability.',
    'guest.terms.title': 'Check-in / Check-out Policy:',
    'guest.terms.checkin_checkout': 'Check-in: 2:00 PM | Check-out: 12:00 PM',
    'guest.terms.dynamic_checkin_checkout': 'Check-in: {checkIn} | Check-out: {checkOut}',
    'guest.terms.free_cancellation': 'Free cancellation before 24 hours',
    'guest.terms.prices_include': 'Prices include taxes and service fees',
    'guest.terms.id_required': 'Please bring ID/Passport for check-in',
    // Validation errors
    'validation.first_name_required': 'Please enter first name',
    'validation.last_name_required': 'Please enter last name',
    'validation.email_required': 'Please enter email',
    'validation.email_invalid': 'Invalid email',
    'validation.phone_required': 'Please enter phone number',
    'validation.phone_invalid': 'Invalid phone number',
    'validation.nationality_required': 'Please select nationality',
    // Booking search
    'search.checkin_date': 'Check-in Date',
    'search.checkout_date': 'Check-out Date',
    'search.adults': 'Adults',
    'search.children': 'Children',
    'search.children_ages': 'Children Ages',
    'search.child_age_placeholder': 'Child {index} age',
    'search.child_age_required': 'Please select age for all children',
    'search.search_rooms': 'Search Rooms',
    'search.searching': 'Searching...',
    // Booking summary
    'summary.title': 'Booking Summary',
    'summary.checkin': 'Check-in date',
    'summary.checkout': 'Check-out date',
    'summary.guests': 'Guests',
    'summary.nights': 'nights',
    'summary.night': 'night',
    'summary.adults_count': '{count} adults',
    'summary.children_count': '{count} children',
    'summary.total_price': 'Total',
    'summary.proceed_booking': 'Proceed to Booking',
    'summary.remove': 'Remove',
    'summary.quantity': 'Quantity',
    'summary.price_per_night': 'Price/night',
    'summary.subtotal': 'Subtotal',
    'summary.no_rooms_selected': 'No rooms selected yet',
    'summary.selected_rooms': 'Selected Rooms',
    'summary.original_price': 'Original Total',
    'summary.savings': 'Savings',
    'summary.total_amount': 'Total Amount',
    'summary.basic_price': 'Basic Price',
    'summary.rooms_nights': '{rooms} rooms × {nights} nights',
    'summary.remove_room': 'Remove this room',
    'summary.processing': 'Processing...',
    'summary.price_includes': '* Prices include taxes and service fees',
    'summary.capacity_warning': 'Warning: Total room capacity ({current}/{required}) is insufficient for guests.',
    'summary.capacity_sufficient': 'Room capacity is sufficient for {guests} guests.',
    // Promotion & Room Card
    'promotion.count': '{count} Promotions',
    'promotion.title': 'Promotion Programs',
    'promotion.savings': 'Save {amount} VND ({type})',
    'promotion.percentage': '{value}%',
    'promotion.fixed': 'Fixed price',
    'promotion.no_promotion': 'No promotion applied',
    'promotion.subtotal': 'Subtotal',
    // Confirmation page
    'confirmation.loading': 'Loading confirmation information...',
    'confirmation.success': 'Booking Successful!',
    'confirmation.booking_id': 'Booking ID',
    'confirmation.total_amount': 'Total Amount',
    'confirmation.guest_name': 'Guest Name',
    'confirmation.duration': 'Duration',
    'confirmation.email_sent': 'We have sent a confirmation email to your address.',
    'confirmation.new_booking': 'New Booking',
    // Common
    'common.loading': 'Loading...',
    'common.error': 'An error occurred',
    'common.success': 'Success',
    'common.cancel': 'Cancel',
    'common.confirm': 'Confirm',
    'common.save': 'Save',
    'common.edit': 'Edit',
    'common.delete': 'Delete',
    'common.add': 'Add',
    'common.search': 'Search'
  },
  ko: {
    'booking.step1': '1. 객실 검색 및 선택',
    'booking.step2': '2. 고객 정보',
    'booking.step3': '3. 확인',
    'booking.language': '언어',
    'booking.hotline': '핫라인',
    'room.capacity': '최대 {count}명',
    'room.remaining': '{count}개 객실 이용 가능',
    'room.recommended_rooms': '권장: 모든 손님을 수용하기 위해 {count}개 객실',
    'room.view_gallery': '갤러리 보기',
    'room.more_images': '+{count}장 더 보기',
    'room.amenities.main': '주요 편의시설',
    'room.amenities.room': '객실 편의시설',
    'room.amenities.bathroom': '욕실 편의시설',
    'room.book_now': '지금 예약',
    'room.contact': '문의하기',
    'room.bed_type': '침대 유형',
    'room.area': '면적',
    'room.capacity_label': '수용 인원',
    'room.view': '전망',
    'room.price_per_night': '/박',
    // Room defaults
    'room.bed_type.default': '킹사이즈 더블 베드',
    'room.area.default': '30m²',
    'room.view.default': '시티뷰',
    'room.amenities.default.wifi': '무료 WiFi',
    'room.amenities.default.tv': 'LCD TV',
    'room.amenities.default.ac': '에어컨',
    'room.amenities.default.minibar': '미니바',
    'room.amenities.default.safe': '금고',
    'room.amenities.default.desk': '업무용 책상',
    'room.amenities.default.shower': '샤워실',
    'room.amenities.default.hairdryer': '헤어드라이어',
    'room.amenities.default.toiletries': '개인 세면용품',
    // Room list
    'rooms.no_rooms_found': '이용 가능한 객실이 없습니다',
    'rooms.no_rooms_description': '체크인/체크아웃 날짜나 투숙객 수를 변경해 보세요.',
    'rooms.unexpected_data': '예상치 못한 객실 데이터 형식',
    // Guest form
    'guest.form_title': '투숙객 정보',
    'guest.first_name': '이름',
    'guest.last_name': '성',
    'guest.email': '이메일',
    'guest.phone': '전화번호',
    'guest.nationality': '국적',
    'guest.special_requests': '특별 요청사항',
    'guest.required': '*',
    'guest.first_name.placeholder': '이름을 입력하세요',
    'guest.last_name.placeholder': '성을 입력하세요',
    'guest.email.placeholder': 'email@example.com',
    'guest.phone.placeholder': '+84 123 456 789',
    'guest.special_requests.placeholder': '예: 고층 객실, 더블 베드, 기타 특별 요청사항...',
    'guest.nationality.placeholder': '국적 선택',
    'guest.continue': '계속',
    'guest.booking_loading': '예약 중...',
    'guest.confirm_booking': '예약 확인',
    'guest.back_to_rooms': '객실 선택으로 돌아가기',
    'guest.special_requests.note': '호텔에서 가능한 범위 내에서 귀하의 요청을 수용하도록 노력하겠습니다.',
    'guest.terms.title': '예약 조건:',
    'guest.terms.checkin_checkout': '체크인: 오후 2:00 | 체크아웃: 오후 12:00',
    'guest.terms.dynamic_checkin_checkout': '체크인: {checkIn} | 체크아웃: {checkOut}',
    'guest.terms.free_cancellation': '24시간 전 무료 취소',
    'guest.terms.prices_include': '세금 및 서비스 수수료 포함 가격',
    'guest.terms.id_required': '체크인 시 신분증/여권을 지참해 주세요',
    // Validation errors
    'validation.first_name_required': '이름을 입력해 주세요',
    'validation.last_name_required': '성을 입력해 주세요',
    'validation.email_required': '이메일을 입력해 주세요',
    'validation.email_invalid': '유효하지 않은 이메일',
    'validation.phone_required': '전화번호를 입력해 주세요',
    'validation.phone_invalid': '유효하지 않은 전화번호',
    'validation.nationality_required': '국적을 선택해 주세요',
    // Booking search
    'search.checkin_date': '체크인 날짜',
    'search.checkout_date': '체크아웃 날짜',
    'search.adults': '성인',
    'search.children': '어린이',
    'search.children_ages': '어린이 나이',
    'search.child_age_placeholder': '어린이 {index} 나이',
    'search.child_age_required': '모든 어린이의 나이를 선택해주세요',
    'search.search_rooms': '객실 검색',
    'search.searching': '검색 중...',
    // Booking summary
    'summary.title': '예약 요약',
    'summary.checkin': '체크인 날짜',
    'summary.checkout': '체크아웃 날짜',
    'summary.guests': '투숙객',
    'summary.nights': '박',
    'summary.night': '박',
    'summary.adults_count': '성인 {count}명',
    'summary.children_count': '어린이 {count}명',
    'summary.total_price': '총액',
    'summary.proceed_booking': '예약 진행',
    'summary.remove': '제거',
    'summary.quantity': '수량',
    'summary.price_per_night': '1박 요금',
    'summary.subtotal': '소계',
    'summary.no_rooms_selected': '선택된 객실이 없습니다',
    'summary.selected_rooms': '선택된 객실',
    'summary.original_price': '원래 총액',
    'summary.savings': '할인',
    'summary.total_amount': '총 금액',
    'summary.basic_price': '기본 요금',
    'summary.rooms_nights': '{rooms}개 객실 × {nights}박',
    'summary.remove_room': '이 객실 제거',
    'summary.processing': '처리 중...',
    'summary.price_includes': '* 세금 및 서비스 수수료 포함 가격',
    'summary.capacity_warning': '경고: 총 객실 수용 인원 ({current}/{required})이 투숙객 수에 부족합니다.',
    'summary.capacity_sufficient': '객실 수용 인원이 {guests}명에게 충분합니다.',
    // Promotion & Room Card
    'promotion.count': '{count}개 프로모션',
    'promotion.title': '프로모션 프로그램',
    'promotion.savings': '{amount} VND 절약 ({type})',
    'promotion.percentage': '{value}%',
    'promotion.fixed': '고정 가격',
    'promotion.no_promotion': '프로모션 적용 안됨',
    'promotion.subtotal': '소계',
    // Confirmation page
    'confirmation.loading': '확인 정보를 로딩 중...',
    'confirmation.success': '예약 성공!',
    'confirmation.booking_id': '예약 ID',
    'confirmation.total_amount': '총 금액',
    'confirmation.guest_name': '고객명',
    'confirmation.duration': '기간',
    'confirmation.email_sent': '확인 이메일을 고객님의 주소로 보내드렸습니다.',
    'confirmation.new_booking': '새 예약',
    // Common
    'common.loading': '로딩 중...',
    'common.error': '오류가 발생했습니다',
    'common.success': '성공',
    'common.cancel': '취소',
    'common.confirm': '확인',
    'common.save': '저장',
    'common.edit': '편집',
    'common.delete': '삭제',
    'common.add': '추가',
    'common.search': '검색'
  },
  ja: {
    'booking.step1': '1. 部屋を検索・選択',
    'booking.step2': '2. ゲスト情報',
    'booking.step3': '3. 確認',
    'booking.language': '言語',
    'booking.hotline': 'ホットライン',
    'room.capacity': '最大{count}名',
    'room.remaining': '{count}室利用可能',
    'room.recommended_rooms': '推奨：全ゲストを収容するために{count}室',
    'room.view_gallery': 'ギャラリーを見る',
    'room.more_images': '+{count}枚の写真',
    'room.amenities.main': 'メインアメニティ',
    'room.amenities.room': 'ルームアメニティ',
    'room.amenities.bathroom': 'バスルームアメニティ',
    'room.book_now': '今すぐ予約',
    'room.contact': 'お問い合わせ',
    'room.bed_type': 'ベッドタイプ',
    'room.area': '面積',
    'room.capacity_label': '定員',
    'room.view': 'ビュー',
    'room.price_per_night': '/泊',
    // Room defaults
    'room.bed_type.default': 'キングサイズダブルベッド',
    'room.area.default': '30m²',
    'room.view.default': 'シティビュー',
    'room.amenities.default.wifi': '無料WiFi',
    'room.amenities.default.tv': 'LCD TV',
    'room.amenities.default.ac': 'エアコン',
    'room.amenities.default.minibar': 'ミニバー',
    'room.amenities.default.safe': 'セーフティボックス',
    'room.amenities.default.desk': 'ワークデスク',
    'room.amenities.default.shower': 'シャワー',
    'room.amenities.default.hairdryer': 'ヘアドライヤー',
    'room.amenities.default.toiletries': 'アメニティグッズ',
    // Room list
    'rooms.no_rooms_found': '利用可能な部屋がありません',
    'rooms.no_rooms_description': 'チェックイン/チェックアウト日程またはゲスト数を変更してみてください。',
    'rooms.unexpected_data': '予期しない部屋データ形式',
    // Guest form
    'guest.form_title': 'ゲスト情報',
    'guest.first_name': '名前',
    'guest.last_name': '姓',
    'guest.email': 'メールアドレス',
    'guest.phone': '電話番号',
    'guest.nationality': '国籍',
    'guest.special_requests': '特別なご要望',
    'guest.required': '*',
    'guest.first_name.placeholder': 'お名前を入力してください',
    'guest.last_name.placeholder': '姓を入力してください',
    'guest.email.placeholder': 'email@example.com',
    'guest.phone.placeholder': '+84 123 456 789',
    'guest.special_requests.placeholder': '例：高層階、ダブルベッド、その他特別なご要望...',
    'guest.nationality.placeholder': '国籍を選択',
    'guest.continue': '続行',
    'guest.booking_loading': '予約中...',
    'guest.confirm_booking': '予約確認',
    'guest.back_to_rooms': 'お部屋選択に戻る',
    'guest.special_requests.note': 'ホテルでは可能な範囲でお客様のご要望にお応えするよう努めます。',
    'guest.terms.title': '予約条件:',
    'guest.terms.checkin_checkout': 'チェックイン: 午後2:00 | チェックアウト: 午後12:00',
    'guest.terms.dynamic_checkin_checkout': 'チェックイン: {checkIn} | チェックアウト: {checkOut}',
    'guest.terms.free_cancellation': '24時間前まで無料キャンセル',
    'guest.terms.prices_include': '税金およびサービス料金込みの価格',
    'guest.terms.id_required': 'チェックイン時に身分証明書/パスポートをお持ちください',
    // Validation errors
    'validation.first_name_required': 'お名前を入力してください',
    'validation.last_name_required': '姓を入力してください',
    'validation.email_required': 'メールアドレスを入力してください',
    'validation.email_invalid': '無効なメールアドレス',
    'validation.phone_required': '電話番号を入力してください',
    'validation.phone_invalid': '無効な電話番号',
    'validation.nationality_required': '国籍を選択してください',
    // Booking search
    'search.checkin_date': 'チェックイン日',
    'search.checkout_date': 'チェックアウト日',
    'search.adults': '大人',
    'search.children': '子供',
    'search.children_ages': '子供の年齢',
    'search.child_age_placeholder': '子供{index}の年齢',
    'search.child_age_required': 'すべての子供の年齢を選択してください',
    'search.search_rooms': 'お部屋を検索',
    'search.searching': '検索中...',
    // Booking summary
    'summary.title': '予約概要',
    'summary.checkin': 'チェックイン日',
    'summary.checkout': 'チェックアウト日',
    'summary.guests': 'ゲスト',
    'summary.nights': '泊',
    'summary.night': '泊',
    'summary.adults_count': '大人{count}名',
    'summary.children_count': '子供{count}名',
    'summary.total_price': '合計',
    'summary.proceed_booking': '予約手続きに進む',
    'summary.remove': '削除',
    'summary.quantity': '数量',
    'summary.price_per_night': '1泊料金',
    'summary.subtotal': '小計',
    'summary.no_rooms_selected': 'まだお部屋が選択されていません',
    'summary.selected_rooms': '選択されたお部屋',
    'summary.original_price': '元の合計',
    'summary.savings': '割引',
    'summary.total_amount': '合計金額',
    'summary.basic_price': '基本料金',
    'summary.rooms_nights': '{rooms}室 × {nights}泊',
    'summary.remove_room': 'このお部屋を削除',
    'summary.processing': '処理中...',
    'summary.price_includes': '* 税金およびサービス料金込みの価格',
    'summary.capacity_warning': '警告：総客室定員（{current}/{required}）がゲスト数に不足しています。',
    'summary.capacity_sufficient': '客室定員は{guests}名のゲストに十分です。',
    // Promotion & Room Card
    'promotion.count': '{count}つのプロモーション',
    'promotion.title': 'プロモーションプログラム',
    'promotion.savings': '{amount} VND節約 ({type})',
    'promotion.percentage': '{value}%',
    'promotion.fixed': '固定価格',
    'promotion.no_promotion': 'プロモーション適用なし',
    'promotion.subtotal': '小計',
    // Confirmation page
    'confirmation.loading': '確認情報を読み込み中...',
    'confirmation.success': '予約成功！',
    'confirmation.booking_id': '予約ID',
    'confirmation.total_amount': '合計金額',
    'confirmation.guest_name': 'ゲスト名',
    'confirmation.duration': '期間',
    'confirmation.email_sent': '確認メールをお客様のメールアドレスにお送りしました。',
    'confirmation.new_booking': '新しい予約',
    // Common
    'common.loading': '読み込み中...',
    'common.error': 'エラーが発生しました',
    'common.success': '成功',
    'common.cancel': 'キャンセル',
    'common.confirm': '確認',
    'common.save': '保存',
    'common.edit': '編集',
    'common.delete': '削除',
    'common.add': '追加',
    'common.search': '検索'
  }
};

export const LanguageProvider: React.FC<LanguageProviderProps> = ({ children }) => {
  const [currentLanguage, setCurrentLanguage] = useState<string>('vi');
  const [availableLanguages, setAvailableLanguages] = useState<string[]>(['vi']);
  const [hotelConfig, setHotelConfig] = useState<any>(null);
  const [themeData, setThemeData] = useState<any>(null);
  const [languageInfo, setLanguageInfo] = useState<any>(null);
  const [loading, setLoading] = useState(false);

  // Initialize language on mount
  useEffect(() => {
    initializeLanguage();
  }, []);

  const initializeLanguage = async () => {
    try {
      setLoading(true);
      console.log('🌐 Initializing language context...');

      // Try to load cached data first
      const cachedConfig = tenantDetection.getCachedHotelConfig(currentLanguage);
      const cachedTheme = tenantDetection.getCachedTheme(currentLanguage);

      if (cachedConfig) {
        setHotelConfig(cachedConfig);
        console.log('🏨 Loaded cached hotel config:', cachedConfig);
      }

      if (cachedTheme) {
        setThemeData(cachedTheme);
        console.log('🎨 Loaded cached theme:', cachedTheme);
      }

      // Load fresh data to get language info
      const hotelData = await tenantDetection.getHotelData(currentLanguage);

      if (hotelData.config) {
        setHotelConfig(hotelData.config);
        if (hotelData.config.theme) {
          setThemeData(hotelData.config.theme);
        }
      }

      if (hotelData.language_info) {
        setLanguageInfo(hotelData.language_info);
        setAvailableLanguages(hotelData.language_info.available_languages || ['vi']);
        setCurrentLanguage(hotelData.language_info.current_language || 'vi');
      }

      console.log('🌐 Language context initialized successfully');
    } catch (error) {
      console.error('❌ Error initializing language context:', error);
    } finally {
      setLoading(false);
    }
  };

  const changeLanguage = async (newLanguage: string) => {
    if (newLanguage === currentLanguage || loading) return;

    try {
      setLoading(true);
      console.log(`🌐 Changing language from ${currentLanguage} to ${newLanguage}`);

      // Get hotel data with the new language
      const hotelData = await tenantDetection.getHotelData(newLanguage);

      if (hotelData.language_info) {
        setLanguageInfo(hotelData.language_info);
        setCurrentLanguage(hotelData.language_info.current_language || newLanguage);
        console.log('✅ Language switched successfully:', hotelData.language_info);
      }

      // Update hotel configuration and theme data for new language
      if (hotelData.config) {
        setHotelConfig(hotelData.config);

        if (hotelData.config.theme) {
          setThemeData(hotelData.config.theme);
          console.log('🎨 Theme updated for new language:', hotelData.config.theme);
        }
      }

      // Trigger a re-render for all components
      console.log('🔄 Language context updated, triggering re-render...');

    } catch (error) {
      console.error('❌ Error switching language:', error);
      // Revert to previous language on error
      setCurrentLanguage(currentLanguage);
    } finally {
      setLoading(false);
    }
  };

  const getLocalizedText = useCallback((key: string, fallback?: string) => {
    const texts = staticTexts[currentLanguage as keyof typeof staticTexts] || staticTexts.vi;
    const text = texts[key as keyof typeof texts] || fallback || key;

    return text;
  }, [currentLanguage]);

  const contextValue: LanguageContextType = {
    currentLanguage,
    availableLanguages,
    hotelConfig,
    themeData,
    languageInfo,
    loading,
    changeLanguage,
    getLocalizedText
  };

  return (
    <LanguageContext.Provider value={contextValue}>
      {children}
    </LanguageContext.Provider>
  );
};

export const useLanguage = (): LanguageContextType => {
  const context = useContext(LanguageContext);
  if (context === undefined) {
    throw new Error('useLanguage must be used within a LanguageProvider');
  }
  return context;
};

// Hook để format text với placeholder
export const useLocalizedText = () => {
  const { getLocalizedText, currentLanguage } = useLanguage();

  const t = useCallback((key: string, params?: Record<string, any>, fallback?: string) => {
    let text = getLocalizedText(key, fallback);

    // Replace placeholders like {count} with actual values
    if (params) {
      Object.entries(params).forEach(([paramKey, value]) => {
        text = text.replace(new RegExp(`\\{${paramKey}\\}`, 'g'), String(value));
      });
    }

    return text;
  }, [getLocalizedText, currentLanguage]);

  return { t };
};