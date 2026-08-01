<div align="center">

# Hệ Thống Đặt Vé Xem Phim

### API RESTful Cấp Doanh Nghiệp Cho Đặt Vé Xem Phim

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Redis](https://img.shields.io/badge/Redis-7.0-DC382D?style=flat-square&logo=redis&logoColor=white)](https://redis.io)
[![License](https://img.shields.io/badge/License-MIT-success.svg?style=flat-square)](LICENSE)

**API sẵn sàng production với chọn ghế thời gian thực, tích hợp cổng thanh toán và logic nghiệp vụ toàn diện**

[Tính Năng](#-tính-năng) • [Bắt Đầu Nhanh](#-bắt-đầu-nhanh) • [API Docs](#-tài-liệu-api) • [Triển Khai](#-triển-khai)

</div>

---

## 📋 Mục Lục

- [Tổng Quan](#-tổng-quan)
- [Tính Năng](#-tính-năng)
- [Công Nghệ](#-công-nghệ)
- [Bắt Đầu Nhanh](#-bắt-đầu-nhanh)
- [Cấu Hình](#-cấu-hình)
- [Tài Liệu API](#-tài-liệu-api)
- [Kiểm Thử](#-kiểm-thử)
- [Triển Khai](#-triển-khai)
- [Đóng Góp](#-đóng-góp)

---

## 🎯 Tổng Quan

API RESTful mạnh mẽ được xây dựng trên Laravel, đóng vai trò là nền tảng cho hệ thống đặt vé xem phim toàn diện. Hỗ trợ nhiều ứng dụng frontend với tính năng thời gian thực, tích hợp cổng thanh toán và bảo mật cấp doanh nghiệp.

### Điểm Nổi Bật

- 🚀 **Hiệu Năng Cao** - Redis caching, tối ưu queries, indexing hiệu quả
- 🔐 **Bảo Mật Doanh Nghiệp** - JWT/Sanctum auth, RBAC, mã hóa
- 💳 **Tích Hợp Thanh Toán** - VNPay, Momo, ZaloPay đã tích hợp
- 🔄 **Thời Gian Thực** - Socket.IO cho cập nhật ghế trực tiếp
- 📱 **Đa Nền Tảng** - Hỗ trợ web, mobile và desktop
- 🧪 **Kiểm Thử Toàn Diện** - Test coverage cao với PHPUnit

---

## ✨ Tính Năng

### 👤 Cổng Khách Hàng

<table>
<tr>
<td width="50%">

**Xác Thực & Hồ Sơ**
- Đăng ký & đăng nhập người dùng
- Quên mật khẩu 
- Xác minh email
- Quản lý hồ sơ cá nhân
- Đổi mật khẩu
- Tải ảnh đại diện

**Khám Phá Phim**
- Duyệt phim (Đang Chiếu, Sắp Chiếu)
- Tìm kiếm & bộ lọc nâng cao
- Lọc theo thể loại, đánh giá, ngôn ngữ
- Chi tiết phim & trailer
- Đánh giá & nhận xét của người dùng
- Xem suất chiếu theo rạp
- Gợi ý phim

</td>
<td width="50%">

**Đặt Vé & Thanh Toán**
- Chọn ghế thời gian thực
- Sơ đồ ghế tương tác
- Đặt nhiều ghế tối đa 10 ghế 
- Chọn loại ghế (Thường, VIP, Đôi)
- Áp dụng mã giảm giá
- Nhiều phương thức thanh toán (VNPay, Momo)
- Email xác nhận đặt vé
- Vé điện tử với mã QR
- Tải/In vé

**Quản Lý Tài Khoản**
- Xem lịch sử đặt vé
- Hủy đặt vé
- Yêu cầu hoàn tiền
- Lưu phim yêu thích
- Theo dõi điểm thưởng
- Cài đặt thông báo
- Lịch sử giao dịch

</td>
</tr>
</table>

### 👨‍💼 Bảng Điều Khiển Admin

<table>
<tr>
<td width="50%">

**Quản Lý Nội Dung**
- Tạo/Sửa/Xóa phim
- Tải poster & trailer phim
- Quản lý thể loại & danh mục
- Thiết lập đánh giá & thời lượng phim
- Quản lý trạng thái phim
- Nhập phim hàng loạt
- Lập kế hoạch lịch chiếu phim

**Quản Lý Rạp Chiếu**
- Tạo/Sửa/Xóa rạp
- Quản lý vị trí rạp
- Cấu hình phòng chiếu mỗi rạp
- Thiết lập sức chứa & bố trí phòng
- Thiết lập loại màn hình (2D, 3D, IMAX)
- Cấu hình sắp xếp ghế
- Giờ hoạt động của rạp

**Quản Lý Suất Chiếu**
- Tạo lịch chiếu phim
- Tự động tạo suất chiếu
- Thiết lập giá theo suất chiếu
- Quản lý tình trạng ghế
- Tạo lịch hàng loạt
- Phát hiện xung đột lịch chiếu
- Hủy suất chiếu

</td>
<td width="50%">

**Quản Lý Tài Chính**
- Theo dõi doanh thu & báo cáo
- Giám sát giao dịch thanh toán
- Xử lý hoàn tiền
- Tạo mã giảm giá
- Phân tích sử dụng voucher
- Thiết lập chiến lược giá
- Dashboard tài chính

**Quản Lý Người Dùng**
- Xem tất cả người dùng
- Phân quyền vai trò
- Cấm/Bỏ cấm người dùng
- Quản lý tài khoản nhân viên
- Nhật ký hoạt động người dùng
- Quản lý phân quyền
- Xuất dữ liệu người dùng

**Phân Tích & Báo Cáo**
- Báo cáo doanh số (ngày, tuần, tháng)
- Phân tích phim phổ biến
- Tỷ lệ lấp đầy rạp
- Doanh thu theo rạp/phim
- Thông tin hành vi khách hàng
- Xu hướng đặt vé
- Xuất báo cáo (PDF, Excel)

**Cấu Hình Hệ Thống**
- Cài đặt cổng thanh toán
- Mẫu email
- Thiết lập SMS gateway
- Chế độ bảo trì hệ thống
- Sao lưu & khôi phục
- Cài đặt bảo mật
- Giới hạn tốc độ API

</td>
</tr>
</table>

### 👨‍💼 Giao Diện Nhân Viên

<table>
<tr>
<td width="50%">

**Vận Hành Vé**
- Quét mã QR
- Xác minh vé
- Check-in khách hàng
- In vé giấy
- Xác thực mã đặt vé
- Quản lý cổng vào
- Hỗ trợ khách hàng

**Quản Lý Đặt Vé**
- Xem tất cả đặt vé
- Tìm kiếm đặt vé theo mã/số điện thoại
- Xử lý hoàn tiền
- Chỉnh sửa đặt vé
- Xử lý khiếu nại khách hàng
- Phát hành vé thay thế
- Xử lý đến muộn

</td>
<td width="50%">

**Vận Hành Hàng Ngày**
- Xem lịch trình hàng ngày
- Giám sát tình trạng ghế trống
- Cập nhật đặt vé thời gian thực
- Giám sát trạng thái phòng chiếu
- Xử lý khách hàng walk-in
- Phân bổ ghế khẩn cấp
- Cập nhật trạng thái suất chiếu

**Báo Cáo**
- Báo cáo doanh số hàng ngày
- Doanh số vé theo suất chiếu
- Báo cáo thu tiền
- Tóm tắt ca làm việc
- Ghi nhận phản hồi khách hàng
- Theo dõi vấn đề
- Báo cáo điểm danh

</td>
</tr>
</table>

### 🔄 Tính Năng Thời Gian Thực

- **Chọn Ghế Trực Tiếp** - Cập nhật ghế thời gian thực với Socket.IO
- **Thông Báo Đặt Vé** - Push notification tức thì
- **Trạng Thái Thanh Toán** - Xác nhận thanh toán thời gian thực
- **Khóa Ghế** - Giữ ghế tạm thời trong quá trình thanh toán
- **Dashboard Admin** - Cập nhật trực tiếp đặt vé & doanh thu

---

## 🛠️ Công Nghệ

<table>
<tr>
<td align="center" width="96">
<img src="https://laravel.com/img/logomark.min.svg" width="48" height="48" alt="Laravel" />
<br>Laravel 12
</td>
<td align="center" width="96">
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" width="48" height="48" alt="PHP" />
<br>PHP 8.2+
</td>
<td align="center" width="96">
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" width="48" height="48" alt="MySQL" />
<br>MySQL 8.0
</td>
<td align="center" width="96">
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/redis/redis-original.svg" width="48" height="48" alt="Redis" />
<br>Redis 7.0
</td>
<td align="center" width="96">
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/socketio/socketio-original.svg" width="48" height="48" alt="Socket.IO" />
<br>Socket.IO
</td>
<td align="center" width="96">
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/docker/docker-original.svg" width="48" height="48" alt="Docker" />
<br>Docker
</td>
</tr>
</table>

### Thư Viện Chính

- **Xác Thực** - Laravel Sanctum, JWT (tymon/jwt-auth)
- **Thanh Toán** - VNPay, Momo, ZaloPay SDKs
- **Thời Gian Thực** - Laravel Broadcasting, Socket.IO
- **Queue** - Laravel Queue với Redis driver
- **Kiểm Thử** - PHPUnit, Laravel Dusk
- **Chất Lượng Code** - Laravel Pint, PHPStan

---

## 🚀 Bắt Đầu Nhanh

### Yêu Cầu

- PHP >= 8.2
- Composer >= 2.0
- MySQL >= 8.0
- Redis >= 7.0 (khuyến nghị)
- Node.js >= 18.x (cho Socket.IO server)

### Cài Đặt

```bash
# 1. Clone repository
git clone 
cd cinema-booking-api

# 2. Cài đặt dependencies
composer install

# 3. Thiết lập môi trường
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# 4. Cấu hình database trong .env
DB_DATABASE=cinema_booking
DB_USERNAME=your_username
DB_PASSWORD=your_password

# 5. Chạy migrations & seeders
php artisan migrate --seed

# 6. Link storage
php artisan storage:link

# 7. Khởi động server development
php artisan serve
```

### Cài Đặt Với Docker (Khuyến Nghị)

```bash
# Khởi động containers
docker-compose up -d

# Cài đặt dependencies
docker-compose exec app composer install

# Thiết lập ứng dụng
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
```

**Truy cập:** http://localhost:8000

---

## ⚙️ Cấu Hình

### Biến Môi Trường

<details>
<summary><b>Cấu Hình Database</b></summary>

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cinema_booking
DB_USERNAME=root
DB_PASSWORD=
```

</details>

<details>
<summary><b>Redis & Cache</b></summary>

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

</details>

<details>
<summary><b>Xác Thực</b></summary>

```env
# Cấu hình JWT
JWT_SECRET=your_secret_key
JWT_TTL=60
JWT_REFRESH_TTL=20160

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000
SESSION_DOMAIN=localhost
```

</details>

<details>
<summary><b>Cổng Thanh Toán</b></summary>

```env
# VNPay
VNPAY_TMN_CODE=your_code
VNPAY_HASH_SECRET=your_secret
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html

# Momo
MOMO_PARTNER_CODE=your_code
MOMO_ACCESS_KEY=your_key
MOMO_SECRET_KEY=your_secret

# ZaloPay
ZALOPAY_APP_ID=your_app_id
ZALOPAY_KEY1=your_key1
ZALOPAY_KEY2=your_key2
```

</details>

<details>
<summary><b>Cấu Hình Email</b></summary>

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

</details>

---

## 📚 Tài Liệu API

### URL Cơ Bản

```
Development: http://localhost:8000/api/v1
Production:  https://api.yourdomain.com/api/v1
```

### Xác Thực

Tất cả requests cần xác thực phải có Bearer token:

```http
Authorization: Bearer {access_token}
Content-Type: application/json
```

### Tham Khảo Nhanh

<details>
<summary><b>Endpoints Xác Thực</b></summary>

```http
POST   /auth/register          # Đăng ký người dùng
POST   /auth/login             # Đăng nhập
POST   /auth/logout            # Đăng xuất
POST   /auth/refresh           # Làm mới token
GET    /auth/me                # Lấy thông tin người dùng hiện tại
```

</details>

<details>
<summary><b>Endpoints Phim</b></summary>

```http
GET    /movies                 # Danh sách phim
GET    /movies/{id}            # Chi tiết phim
GET    /movies/now-showing     # Phim đang chiếu
GET    /movies/coming-soon     # Phim sắp chiếu
POST   /movies                 # Tạo phim [Admin]
PUT    /movies/{id}            # Cập nhật phim [Admin]
DELETE /movies/{id}            # Xóa phim [Admin]
```

</details>

<details>
<summary><b>Endpoints Đặt Vé</b></summary>

```http
GET    /bookings               # Đặt vé của người dùng [Auth]
GET    /bookings/{id}          # Chi tiết đặt vé [Auth]
POST   /bookings               # Tạo đặt vé [Auth]
PUT    /bookings/{id}/cancel   # Hủy đặt vé [Auth]
```

</details>

<details>
<summary><b>Endpoints Thanh Toán</b></summary>

```http
POST   /payments/vnpay/create      # Tạo thanh toán VNPay
GET    /payments/vnpay/return      # VNPay callback
POST   /payments/momo/create       # Tạo thanh toán Momo
POST   /payments/zalopay/create    # Tạo thanh toán ZaloPay
```

</details>

### Ví Dụ Request

```bash
# Lấy danh sách phim
curl -X GET "http://localhost:8000/api/v1/movies?status=showing&page=1" \
  -H "Accept: application/json"

# Tạo đặt vé
curl -X POST "http://localhost:8000/api/v1/bookings" \
  -H "Authorization: Bearer your_token" \
  -H "Content-Type: application/json" \
  -d '{
    "showtime_id": 123,
    "seat_ids": [1, 2, 3],
    "payment_method": "vnpay"
  }'
```

**Tài Liệu Đầy Đủ:** [Swagger UI](http://localhost:8000/api/documentation)

---

## 🧪 Kiểm Thử

```bash
# Chạy tất cả tests
php artisan test

# Chạy với coverage
php artisan test --coverage

# Chạy test suite cụ thể
php artisan test --testsuite=Feature

# Chạy song song
php artisan test --parallel
```

### Chất Lượng Code

```bash
# Kiểm tra code style
./vendor/bin/pint

# Phân tích tĩnh
./vendor/bin/phpstan analyse

# Kiểm tra vấn đề
composer audit
```

---

## 🚀 Triển Khai

### Checklist Production

- [ ] Đặt `APP_ENV=production` và `APP_DEBUG=false`
- [ ] Cấu hình database production
- [ ] Thiết lập Redis cho caching
- [ ] Bật HTTPS/SSL
- [ ] Cấu hình queue workers
- [ ] Thiết lập task scheduler
- [ ] Cấu hình backups
- [ ] Thiết lập monitoring

### Lệnh Triển Khai

```bash
# Pull code mới nhất
git pull origin main

# Cài đặt dependencies
composer install --no-dev --optimize-autoloader

# Chạy migrations
php artisan migrate --force

# Tối ưu cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Khởi động lại services
php artisan queue:restart
sudo systemctl reload php8.2-fpm nginx
```

### Docker Production

```bash
docker-compose -f docker-compose.prod.yml up -d
```

---

## 🗂️ Cấu Trúc Dự Án

```
├── 📁 app
│   ├── 📁 Events
│   │   └── 🐘 BookingCreated.php
│   ├── 📁 Helpers
│   │   └── 🐘 FormatHelper.php
│   ├── 📁 Http
│   │   ├── 📁 Controllers
│   │   │   ├── 📁 Api
│   │   │   │   ├── 🐘 AuditlogController.php
│   │   │   │   ├── 🐘 AuthController.php
│   │   │   │   ├── 🐘 BannerController.php
│   │   │   │   ├── 🐘 CinemaController.php
│   │   │   │   ├── 🐘 ContactController.php
│   │   │   │   ├── 🐘 DashboardController.php
│   │   │   │   ├── 🐘 DistributorController.php
│   │   │   │   ├── 🐘 FoodanddrinkController.php
│   │   │   │   ├── 🐘 GenreController.php
│   │   │   │   ├── 🐘 LoginhistoryController.php
│   │   │   │   ├── 🐘 MembershipController.php
│   │   │   │   ├── 🐘 MenuController.php
│   │   │   │   ├── 🐘 MovieController.php
│   │   │   │   ├── 🐘 MoviecastController.php
│   │   │   │   ├── 🐘 MoviegenreController.php
│   │   │   │   ├── 🐘 NewsController.php
│   │   │   │   ├── 🐘 NotificationController.php
│   │   │   │   ├── 🐘 OrderController.php
│   │   │   │   ├── 🐘 OrderdetailController.php
│   │   │   │   ├── 🐘 PaymentController.php
│   │   │   │   ├── 🐘 PromotionController.php
│   │   │   │   ├── 🐘 ReviewController.php
│   │   │   │   ├── 🐘 RoleController.php
│   │   │   │   ├── 🐘 RoomController.php
│   │   │   │   ├── 🐘 ScheduleController.php
│   │   │   │   ├── 🐘 SeatController.php
│   │   │   │   ├── 🐘 ShowtimeController.php
│   │   │   │   ├── 🐘 ShowtimeseatController.php
│   │   │   │   ├── 🐘 StaffController.php
│   │   │   │   ├── 🐘 TicketController.php
│   │   │   │   ├── 🐘 UserController.php
│   │   │   │   └── 🐘 WishlistController.php
│   │   │   └── 🐘 Controller.php
│   │   ├── 📁 Middleware
│   │   │   ├── 🐘 AdminMiddleware.php
│   │   │   └── 🐘 CheckRoleMiddleware.php
│   │   ├── 📁 Requests
│   │   │   ├── 🐘 StoreAuditlogRequest.php
│   │   │   ├── 🐘 StoreBannerRequest.php
│   │   │   ├── 🐘 StoreCinemaRequest.php
│   │   │   ├── 🐘 StoreContactRequest.php
│   │   │   ├── 🐘 StoreDistributorRequest.php
│   │   │   ├── 🐘 StoreFoodanddrinkRequest.php
│   │   │   ├── 🐘 StoreGenreRequest.php
│   │   │   ├── 🐘 StoreLoginRequest.php
│   │   │   ├── 🐘 StoreLoginhistoryRequest.php
│   │   │   ├── 🐘 StoreMembershipRequest.php
│   │   │   ├── 🐘 StoreMovieRequest.php
│   │   │   ├── 🐘 StoreMoviecastRequest.php
│   │   │   ├── 🐘 StoreMoviegenreRequest.php
│   │   │   ├── 🐘 StoreNewsRequest.php
│   │   │   ├── 🐘 StoreNotificationRequest.php
│   │   │   ├── 🐘 StoreOrderRequest.php
│   │   │   ├── 🐘 StoreOrderdetailRequest.php
│   │   │   ├── 🐘 StorePaymentRequest.php
│   │   │   ├── 🐘 StorePromotionRequest.php
│   │   │   ├── 🐘 StoreRegisterRequest.php
│   │   │   ├── 🐘 StoreReviewRequest.php
│   │   │   ├── 🐘 StoreRoleRequest.php
│   │   │   ├── 🐘 StoreRoomRequest.php
│   │   │   ├── 🐘 StoreScheduleRequest.php
│   │   │   ├── 🐘 StoreSeatRequest.php
│   │   │   ├── 🐘 StoreShowtimeRequest.php
│   │   │   ├── 🐘 StoreShowtimeseatRequest.php
│   │   │   ├── 🐘 StoreStaffRequest.php
│   │   │   ├── 🐘 StoreTicketRequest.php
│   │   │   ├── 🐘 StoreUserRequest.php
│   │   │   └── 🐘 StoreWishlistRequest.php
│   │   └── 📁 Resources
│   │       ├── 🐘 AuditlogResource.php
│   │       ├── 🐘 BannerResource.php
│   │       ├── 🐘 CinemaResource.php
│   │       ├── 🐘 ContactResource.php
│   │       ├── 🐘 DistributorResource.php
│   │       ├── 🐘 FoodanddrinkResource.php
│   │       ├── 🐘 GenreResource.php
│   │       ├── 🐘 LoginhistoryResource.php
│   │       ├── 🐘 MembershipResource.php
│   │       ├── 🐘 MovieResource.php
│   │       ├── 🐘 MoviecastResource.php
│   │       ├── 🐘 MoviegenreResource.php
│   │       ├── 🐘 NewsResource.php
│   │       ├── 🐘 NotificationResource.php
│   │       ├── 🐘 OrderResource.php
│   │       ├── 🐘 OrderdetailResource.php
│   │       ├── 🐘 PaymentResource.php
│   │       ├── 🐘 PromotionResource.php
│   │       ├── 🐘 ReviewResource.php
│   │       ├── 🐘 RoleResource.php
│   │       ├── 🐘 RoomResource.php
│   │       ├── 🐘 ScheduleResource.php
│   │       ├── 🐘 SeatResource.php
│   │       ├── 🐘 ShowtimeResource.php
│   │       ├── 🐘 ShowtimeseatResource.php
│   │       ├── 🐘 StaffResource.php
│   │       ├── 🐘 TicketResource.php
│   │       ├── 🐘 UserResource.php
│   │       └── 🐘 WishlistResource.php
│   ├── 📁 Listeners
│   │   └── 🐘 SendBookingEmail.php
│   ├── 📁 Models
│   │   ├── 🐘 Auditlog.php
│   │   ├── 🐘 Banner.php
│   │   ├── 🐘 Cinema.php
│   │   ├── 🐘 Contact.php
│   │   ├── 🐘 Distributor.php
│   │   ├── 🐘 Foodanddrink.php
│   │   ├── 🐘 Genre.php
│   │   ├── 🐘 Loginhistory.php
│   │   ├── 🐘 Membership.php
│   │   ├── 🐘 Menu.php
│   │   ├── 🐘 Movie.php
│   │   ├── 🐘 Moviecast.php
│   │   ├── 🐘 Moviegenre.php
│   │   ├── 🐘 News.php
│   │   ├── 🐘 Notification.php
│   │   ├── 🐘 Order.php
│   │   ├── 🐘 Orderdetail.php
│   │   ├── 🐘 Payment.php
│   │   ├── 🐘 Promotion.php
│   │   ├── 🐘 Review.php
│   │   ├── 🐘 Role.php
│   │   ├── 🐘 Room.php
│   │   ├── 🐘 Schedule.php
│   │   ├── 🐘 Seat.php
│   │   ├── 🐘 Session.php
│   │   ├── 🐘 Showtime.php
│   │   ├── 🐘 Showtimeseat.php
│   │   ├── 🐘 Staff.php
│   │   ├── 🐘 Ticket.php
│   │   ├── 🐘 User.php
│   │   └── 🐘 Wishlist.php
│   ├── 📁 Providers
│   │   └── 🐘 AppServiceProvider.php
│   ├── 📁 Repositories
│   │   ├── 🐘 AuditlogRepository.php
│   │   ├── 🐘 AuthRepository.php
│   │   ├── 🐘 BannerRepository.php
│   │   ├── 🐘 CinemaRepository.php
│   │   ├── 🐘 ContactRepository.php
│   │   ├── 🐘 DistributorRepository.php
│   │   ├── 🐘 FoodanddrinkRepository.php
│   │   ├── 🐘 GenreRepository.php
│   │   ├── 🐘 LoginhistoryRepository.php
│   │   ├── 🐘 MembershipRepository.php
│   │   ├── 🐘 MovieRepository.php
│   │   ├── 🐘 MoviecastRepository.php
│   │   ├── 🐘 MoviegenreRepository.php
│   │   ├── 🐘 NewsRepository.php
│   │   ├── 🐘 NotificationRepository.php
│   │   ├── 🐘 OrderRepository.php
│   │   ├── 🐘 OrderdetailRepository.php
│   │   ├── 🐘 PaymentRepository.php
│   │   ├── 🐘 PromotionRepository.php
│   │   ├── 🐘 ReviewRepository.php
│   │   ├── 🐘 RoleRepository.php
│   │   ├── 🐘 RoomRepository.php
│   │   ├── 🐘 ScheduleRepository.php
│   │   ├── 🐘 SeatRepository.php
│   │   ├── 🐘 ShowtimeRepository.php
│   │   ├── 🐘 ShowtimeseatRepository.php
│   │   ├── 🐘 StaffRepository.php
│   │   ├── 🐘 TicketRepository.php
│   │   ├── 🐘 UserRepository.php
│   │   └── 🐘 WishlistRepository.php
│   └── 📁 Services
│       ├── 🐘 AuditlogService.php
│       ├── 🐘 AuthService.php
│       ├── 🐘 BannerService.php
│       ├── 🐘 CinemaService.php
│       ├── 🐘 ContactService.php
│       ├── 🐘 DistributorService.php
│       ├── 🐘 FoodanddrinkService.php
│       ├── 🐘 GenreService.php
│       ├── 🐘 LoginhistoryService.php
│       ├── 🐘 MembershipService.php
│       ├── 🐘 MovieService.php
│       ├── 🐘 MoviecastService.php
│       ├── 🐘 MoviegenreService.php
│       ├── 🐘 NewsService.php
│       ├── 🐘 NotificationService.php
│       ├── 🐘 OrderService.php
│       ├── 🐘 OrderdetailService.php
│       ├── 🐘 PaymentService.php
│       ├── 🐘 PromotionService.php
│       ├── 🐘 ReviewService.php
│       ├── 🐘 RoleService.php
│       ├── 🐘 RoomService.php
│       ├── 🐘 ScheduleService.php
│       ├── 🐘 SeatService.php
│       ├── 🐘 ShowtimeService.php
│       ├── 🐘 ShowtimeseatService.php
│       ├── 🐘 StaffService.php
│       ├── 🐘 TicketService.php
│       ├── 🐘 UserService.php
│       └── 🐘 WishlistService.php
├── 📁 bootstrap
│   ├── 🐘 app.php
│   └── 🐘 providers.php
├── 📁 config
│   ├── 🐘 app.php
│   ├── 🐘 auth.php
│   ├── 🐘 cache.php
│   ├── 🐘 database.php
│   ├── 🐘 filesystems.php
│   ├── 🐘 jwt.php
│   ├── 🐘 logging.php
│   ├── 🐘 mail.php
│   ├── 🐘 models.php
│   ├── 🐘 queue.php
│   ├── 🐘 services.php
│   └── 🐘 session.php
├── 📁 database
│   ├── 📁 factories
│   │   ├── 🐘 MovieFactory.php
│   │   └── 🐘 UserFactory.php
│   ├── 📁 migrations
│   │   ├── 🐘 2025_09_06_082320_create_auditlogs_table.php
│   │   ├── 🐘 2025_09_06_082320_create_banners_table.php
│   │   ├── 🐘 2025_09_06_082320_create_cinemas_table.php
│   │   ├── 🐘 2025_09_06_082320_create_contacts_table.php
│   │   ├── 🐘 2025_09_06_082320_create_distributors_table.php
│   │   ├── 🐘 2025_09_06_082320_create_foodanddrinks_table.php
│   │   ├── 🐘 2025_09_06_082320_create_genres_table.php
│   │   ├── 🐘 2025_09_06_082320_create_loginhistory_table.php
│   │   ├── 🐘 2025_09_06_082320_create_memberships_table.php
│   │   ├── 🐘 2025_09_06_082320_create_menus_table.php
│   │   ├── 🐘 2025_09_06_082320_create_moviecast_table.php
│   │   ├── 🐘 2025_09_06_082320_create_moviegenres_table.php
│   │   ├── 🐘 2025_09_06_082320_create_movies_table.php
│   │   ├── 🐘 2025_09_06_082320_create_news_table.php
│   │   ├── 🐘 2025_09_06_082320_create_notifications_table.php
│   │   ├── 🐘 2025_09_06_082320_create_orderdetails_table.php
│   │   ├── 🐘 2025_09_06_082320_create_orders_table.php
│   │   ├── 🐘 2025_09_06_082320_create_payments_table.php
│   │   ├── 🐘 2025_09_06_082320_create_promotions_table.php
│   │   ├── 🐘 2025_09_06_082320_create_reviews_table.php
│   │   ├── 🐘 2025_09_06_082320_create_roles_table.php
│   │   ├── 🐘 2025_09_06_082320_create_rooms_table.php
│   │   ├── 🐘 2025_09_06_082320_create_schedules_table.php
│   │   ├── 🐘 2025_09_06_082320_create_seats_table.php
│   │   ├── 🐘 2025_09_06_082320_create_sessions_table.php
│   │   ├── 🐘 2025_09_06_082320_create_showtimes_table.php
│   │   ├── 🐘 2025_09_06_082320_create_showtimeseats_table.php
│   │   ├── 🐘 2025_09_06_082320_create_staff_table.php
│   │   ├── 🐘 2025_09_06_082320_create_tickets_table.php
│   │   ├── 🐘 2025_09_06_082320_create_users_table.php
│   │   ├── 🐘 2025_09_06_082320_create_wishlists_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_auditlogs_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_banners_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_cinemas_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_contacts_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_distributors_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_foodanddrinks_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_genres_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_loginhistory_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_memberships_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_menus_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_moviecast_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_moviegenres_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_movies_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_news_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_notifications_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_orderdetails_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_orders_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_payments_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_promotions_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_reviews_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_rooms_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_schedules_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_seats_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_showtimes_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_showtimeseats_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_staff_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_tickets_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_users_table.php
│   │   ├── 🐘 2025_09_06_082323_add_foreign_keys_to_wishlists_table.php
│   │   ├── 🐘 2025_10_01_172108_update_genre_in_movies_table.php
│   │   └── 🐘 2025_10_02_103022_update_movie_in_distributors_table.php
│   ├── 📁 seeders
│   │   ├── 🐘 CinemaSeeder.php
│   │   ├── 🐘 DatabaseSeeder.php
│   │   └── 🐘 MovieSeeder.php
│   ├── ⚙️ .gitignore
│   └── 📄 database.sqlite
├── 📁 public
│   ├── ⚙️ .htaccess
│   ├── 📄 favicon.ico
│   ├── 🐘 index.php
│   └── 📄 robots.txt
├── 📁 resources
│   ├── 📁 css
│   │   └── 🎨 app.css
│   ├── 📁 js
│   │   ├── 📄 app.js
│   │   └── 📄 bootstrap.js
│   └── 📁 views
│       └── 🐘 welcome.blade.php
├── 📁 routes
│   ├── 🐘 api.php
│   ├── 🐘 console.php
│   └── 🐘 web.php
├── 📁 storage
│   ├── 📁 app
│   │   ├── 📁 private
│   │   │   └── ⚙️ .gitignore
│   │   ├── 📁 public
│   │   │   ├── 📁 uploads
│   │   │   │   ├── 📁 banners
│   │   │   │   │   ├── 🖼️ 777a1c7b5f09aa47d45b5f0873aee7aa.jpg
│   │   │   │   │   └── 🖼️ 7eec4dace25c81c9b3999c9fc6ab0b63.jpg
│   │   │   │   ├── 📁 cinemas
│   │   │   │   │   └── 🖼️ 777a1c7b5f09aa47d45b5f0873aee7aa.jpg
│   │   │   │   ├── 📁 movies
│   │   │   │   │   └── 🖼️ 1999d0f8c76fbc9b536ab8df1ff96c48.jpg
│   │   │   │   └── 📁 promotions
│   │   │   │       ├── 🖼️ 1999d0f8c76fbc9b536ab8df1ff96c48.jpg
│   │   │   │       └── 🖼️ 777a1c7b5f09aa47d45b5f0873aee7aa.jpg
│   │   │   └── ⚙️ .gitignore
│   │   └── ⚙️ .gitignore
│   ├── 📁 framework
│   │   ├── 📁 sessions
│   │   │   └── ⚙️ .gitignore
│   │   ├── 📁 testing
│   │   │   └── ⚙️ .gitignore
│   │   ├── 📁 views
│   │   │   ├── ⚙️ .gitignore
│   │   │   ├── 🐘 151296476f7b1d18daee4ef4ce05cca3.php
│   │   │   ├── 🐘 23def30f4d7f9e20c6029c1ab3431964.php
│   │   │   ├── 🐘 377b783c975e0296d37f1ebf08249feb.php
│   │   │   ├── 🐘 4a5ac6900bdc93379961a0961dc37521.php
│   │   │   ├── 🐘 529fbb8fe9e0d16e5a147a75e1eea942.php
│   │   │   ├── 🐘 591c8f7de8f96070a29144792247da65.php
│   │   │   ├── 🐘 5a9f62a0dcc5b02d62ea292ece76496c.php
│   │   │   ├── 🐘 61045b63da4a0086416c44040eda0a50.php
│   │   │   ├── 🐘 6886e9519e7211b9bad2a7425b4504fe.php
│   │   │   ├── 🐘 6e2befaafbe64d0580bcc1e6b6f9c481.php
│   │   │   ├── 🐘 7e1f4f0193349258b57fa33dcf76551e.php
│   │   │   ├── 🐘 848b5589538e13ca1fcdeb1721cc93e3.php
│   │   │   ├── 🐘 8a4fd68822f644f1b8bffc8fb749725f.php
│   │   │   ├── 🐘 993894c4926de6baf5125ad443f4a26d.php
│   │   │   ├── 🐘 c0f3dc41c26139dac52f9d793ae06d4b.php
│   │   │   ├── 🐘 cd8594a80d9a341abe8d6d7bf11f074a.php
│   │   │   ├── 🐘 ceac55400759eca12ba1b8595c772b0c.php
│   │   │   ├── 🐘 dd28426a42e2dfdb6244db1a7f957dbc.php
│   │   │   ├── 🐘 fcf06b2e34f2a76d794559f5fc17e171.php
│   │   │   └── 🐘 ff9e769eae35b91469cf9f03c91dce4a.php
│   │   └── ⚙️ .gitignore
│   └── 📁 logs
│       └── ⚙️ .gitignore
├── 📁 tests
│   ├── 📁 Feature
│   │   ├── 🐘 ExampleTest.php
│   │   └── 🐘 MovieTest.php
│   ├── 📁 Unit
│   │   ├── 🐘 BookingTest.php
│   │   └── 🐘 ExampleTest.php
│   └── 🐘 TestCase.php
├── ⚙️ .editorconfig
├── ⚙️ .env.example
├── ⚙️ .gitattributes
├── ⚙️ .gitignore
├── 📝 README.md
├── 📄 artisan
├── ⚙️ composer.json
├── ⚙️ package.json
├── ⚙️ phpunit.xml
└── 📄 vite.config.js
```


---

## 📊 Database Schema

### Bảng Chính

- **users** - Tài khoản người dùng và xác thực
- **movies** - Thông tin và metadata phim
- **cinemas** - Vị trí rạp chiếu
- **screens** - Phòng chiếu
- **seats** - Cấu hình ghế
- **showtimes** - Lịch chiếu phim
- **bookings** - Đặt vé
- **payments** - Giao dịch thanh toán
- **vouchers** - Mã giảm giá

**Schema Đầy Đủ:** [Tài Liệu Database](docs/database.md)

---

## 🔒 Bảo Mật

- ✅ Xác thực JWT/Sanctum
- ✅ Kiểm soát truy cập dựa trên vai trò (RBAC)
- ✅ Giới hạn tốc độ API
- ✅ Cấu hình CORS
- ✅ Ngăn chặn SQL injection
- ✅ Bảo vệ XSS
- ✅ Bảo vệ CSRF
- ✅ Mã hóa dữ liệu nhạy cảm

---

## 🤝 Đóng Góp

Chúng tôi hoan nghênh mọi đóng góp! Vui lòng làm theo các bước sau:

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/tinh-nang-tuyet-voi`)
3. Commit thay đổi (`git commit -m 'feat: thêm tính năng tuyệt vời'`)
4. Push lên branch (`git push origin feature/tinh-nang-tuyet-voi`)
5. Mở Pull Request

### Tiêu Chuẩn Code

- Tuân theo coding style PSR-12
- Viết tests cho tính năng mới
- Cập nhật tài liệu
- Sử dụng commit messages có ý nghĩa

---

## 📝 Giấy Phép

Dự án này được cấp phép theo Giấy phép MIT - xem file [LICENSE](LICENSE) để biết chi tiết.

---

## 📞 Hỗ Trợ

- 📧 Email: hoangdatcoder@gmail.com
- 📖 Tài Liệu: [docs.cinema.com](https://docs.cinema.com)
- 🐛 Issues: [GitHub Issues](https://github.com/yourusername/cinema-booking-api/issues)

---

<div align="center">

**Được tạo với ❤️ bởi Gấu Phim Booking Team**

⭐ Star repo này nếu bạn thấy hữu ích!

[Website](https://cinema.com) • [API Docs](https://api.cinema.com/docs) • [Blog](https://blog.cinema.com)

</div>