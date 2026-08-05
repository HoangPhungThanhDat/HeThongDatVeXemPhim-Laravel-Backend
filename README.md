<div align="center">

# Gấu Phim — Hệ Thống Đặt Vé Xem Phim

### API Backend Cấp Doanh Nghiệp Cho Nền Tảng Quản Lý & Đặt Vé Rạp Chiếu Phim

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Redis](https://img.shields.io/badge/Redis-Predis-DC382D?style=flat-square&logo=redis&logoColor=white)](https://redis.io)
[![Cloudinary](https://img.shields.io/badge/Cloudinary-Media-3448C5?style=flat-square&logo=cloudinary&logoColor=white)](https://cloudinary.com)
[![Scribe](https://img.shields.io/badge/API%20Docs-Scribe-6E5494?style=flat-square)](https://scribe.knuckles.wtf)
[![License](https://img.shields.io/badge/License-MIT-success.svg?style=flat-square)](LICENSE)

**API sẵn sàng production cho nền tảng rạp chiếu phim: chọn ghế thời gian thực, đăng nhập Google, thanh toán VNPay/Momo và bộ máy quản trị toàn diện**

[Tính Năng](#-tính-năng) • [Bắt Đầu Nhanh](#-bắt-đầu-nhanh) • [Tài Liệu API](#-tài-liệu-api-scribe) • [Cấu Trúc Dự Án](#️-cấu-trúc-dự-án) • [Triển Khai](#-triển-khai)

</div>

---

## 📋 Mục Lục

- [Tổng Quan](#-tổng-quan)
- [Tính Năng](#-tính-năng)
- [Công Nghệ](#-công-nghệ)
- [Bắt Đầu Nhanh](#-bắt-đầu-nhanh)
- [Cấu Hình](#-cấu-hình)
- [Tài Liệu API (Scribe)](#-tài-liệu-api-scribe)
- [Kiểm Thử](#-kiểm-thử)
- [Cấu Trúc Dự Án](#️-cấu-trúc-dự-án)
- [Triển Khai](#-triển-khai)
- [Bảo Mật](#-bảo-mật)
- [Đóng Góp](#-đóng-góp)

---

## 🎯 Tổng Quan

**Gấu Phim** là API RESTful được xây dựng trên **Laravel 12**, đóng vai trò backend cho một hệ thống quản lý & đặt vé xem phim toàn diện, phục vụ đồng thời 3 nhóm người dùng: **Khách hàng**, **Quản trị viên (Admin)** và **Nhân viên rạp (Staff)**.

Dự án tuân theo kiến trúc **Repository – Service – Controller**, tách bạch rõ ràng logic nghiệp vụ khỏi tầng điều phối request, giúp dễ bảo trì và mở rộng khi hệ thống phát triển thêm module mới.

### Điểm Nổi Bật

- 🔐 **Xác Thực Đa Phương Thức** — JWT (tymon/jwt-auth) kết hợp đăng nhập Google (OAuth)
- 💳 **Tích Hợp Thanh Toán** — VNPay & Momo
- 🔄 **Thời Gian Thực** — Cập nhật trạng thái ghế và thông báo trực tiếp qua Socket.IO/Pusher
- ☁️ **Lưu Trữ Media Trên Cloud** — Cloudinary cho poster phim, banner, ảnh đại diện
- 🧱 **Kiến Trúc Rõ Ràng** — Repository – Service – Controller cho từng module nghiệp vụ
- 📖 **Tài Liệu API Tự Sinh** — Scribe, có Try-It-Out ngay trên trình duyệt
- 🛡️ **Phân Quyền Theo Vai Trò** — Admin, Nhân viên Back-office, Nhân viên Rạp
- 🧪 **Kiểm Thử** — PHPUnit (Feature & Unit tests)

---

## ✨ Tính Năng

### 👤 Cổng Khách Hàng

<table>
<tr>
<td width="50%">

**Xác Thực & Hồ Sơ**
- Đăng ký & đăng nhập bằng email/mật khẩu (JWT)
- Đăng nhập bằng **Google (OAuth)**
- Quên mật khẩu / đặt lại mật khẩu
- Quản lý hồ sơ cá nhân, đổi mật khẩu
- Lịch sử đăng nhập (`Loginhistory`)

**Khám Phá Phim**
- Duyệt phim, thể loại, diễn viên (`Movie`, `Genre`, `Moviecast`)
- Xem tin tức, khuyến mãi (`News`, `Promotion`)
- Đánh giá & nhận xét phim (`Review`)
- Xem lịch chiếu theo rạp/phòng (`Schedule`, `Showtime`)
- Danh sách phim yêu thích (`Wishlist`)

</td>
<td width="50%">

**Đặt Vé & Thanh Toán**
- Sơ đồ ghế thời gian thực (`Showtimeseat`) qua Socket.IO/Pusher
- Chọn nhiều ghế, nhiều loại ghế
- Gọi món ăn/thức uống kèm vé (`Foodanddrink`)
- Áp dụng mã giảm giá (`Promotion`)
- Thanh toán qua **VNPay** / **Momo**
- Vé điện tử kèm mã QR (`Ticket`)
- Đơn hàng & chi tiết đơn hàng (`Order`, `Orderdetail`)

**Khác**
- Nhận thông báo (`Notification`)
- Gửi liên hệ/hỗ trợ (`Contact`)
- Thành viên & điểm thưởng (`Membership`)

</td>
</tr>
</table>

### 👨‍💼 Bảng Điều Khiển Admin

<table>
<tr>
<td width="50%">

**Quản Lý Nội Dung**
- Quản lý phim, thể loại, diễn viên, nhà phát hành (`Movie`, `Genre`, `Moviecast`, `Moviegenre`, `Distributor`)
- Quản lý tin tức & banner trang chủ (`News`, `Banner`)
- Quản lý menu, đồ ăn/thức uống (`Menu`, `Foodanddrink`)

**Quản Lý Rạp & Phòng Chiếu**
- Quản lý rạp chiếu (`Cinema`)
- Quản lý phòng chiếu & bố cục ghế (`Room`, `RoomManagement`, `RoomSeat`)
- Quản lý ghế theo phòng (`Seat`, `SeatManagement`)

**Quản Lý Suất Chiếu**
- Tạo & quản lý lịch chiếu (`Schedule`, `Showtime`)
- Quản lý tình trạng ghế theo suất chiếu (`Showtimeseat`)

</td>
<td width="50%">

**Quản Lý Vé & Đơn Hàng**
- Quản lý vé, chỉnh sửa/hủy vé (`Ticket`, `TicketManagement`)
- Quản lý đơn hàng & thanh toán (`Order`, `Orderdetail`, `Payment`)
- Quản lý mã giảm giá/khuyến mãi (`Promotion`)

**Quản Lý Người Dùng & Phân Quyền**
- Quản lý người dùng, nhân viên (`User`, `Staff`)
- Phân quyền theo vai trò (`Role`) qua `AdminMiddleware` / `CheckRoleMiddleware`
- Nhật ký thao tác hệ thống (`Auditlog`)
- Lịch sử đăng nhập người dùng (`Loginhistory`)

**Vận Hành Khác**
- Dashboard tổng quan số liệu (`Dashboard`)
- Quản lý đánh giá của khách hàng (`Review`)
- Quản lý thông báo hệ thống (`Notification`)
- Xử lý liên hệ/hỗ trợ khách hàng (`Contact`)

</td>
</tr>
</table>

### 🔄 Tính Năng Thời Gian Thực

- **Chọn Ghế Trực Tiếp** — Trạng thái ghế cập nhật tức thời cho mọi người dùng đang xem cùng suất chiếu
- **Thông Báo Tức Thì** — Đẩy thông báo đặt vé/khuyến mãi qua Socket.IO/Pusher
- **Đồng Bộ Media** — Ảnh phim/banner/avatar được đồng bộ và tối ưu qua Cloudinary (kèm lệnh migrate `MigrateImagesToCloudinary`)

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
<br>Redis (Predis)
</td>
<td align="center" width="96">
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/socketio/socketio-original.svg" width="48" height="48" alt="Socket.IO" />
<br>Socket.IO / Pusher
</td>
</tr>
</table>

### Thư Viện & Dịch Vụ Chính (theo `composer.json`)

| Thư viện | Phiên bản | Vai trò |
|---|---|---|
| `laravel/framework` | ^12.0 | Framework chính |
| `php-open-source-saver/jwt-auth` | ^2.8 | Xác thực JWT (fork được duy trì của `tymon/jwt-auth`) |
| `laravel/socialite` | ^5.29 | Đăng nhập Google (OAuth) |
| `cloudinary-labs/cloudinary-laravel` | ^3.0 | Lưu trữ & tối ưu media trên Cloudinary |
| `predis/predis` | ^3.2 | Client Redis (cache, queue, broadcasting) |
| `laravel/tinker` | ^2.10 | REPL tương tác với ứng dụng |

**Dev dependencies đáng chú ý:**

| Thư viện | Phiên bản | Vai trò |
|---|---|---|
| `knuckleswtf/scribe` | ^5.11 | Tự sinh tài liệu API |
| `laravel/pint` | ^1.24 | Chuẩn hoá code style (PSR-12) |
| `laravel/pail` | ^1.2 | Xem log thời gian thực trong terminal |
| `laravel/sail` | ^1.41 | Môi trường Docker dựng sẵn |
| `phpunit/phpunit` | ^11.5 | Kiểm thử |
| `kitloong/laravel-migrations-generator` | ^7.2 | Sinh migration từ database có sẵn |
| `reliese/laravel` | ^1.4 | Sinh Eloquent model từ database có sẵn |
| `fakerphp/faker` | ^1.23 | Sinh dữ liệu giả cho seeder/factory |
| `mockery/mockery` | ^1.6 | Mocking trong test |

---

## 🚀 Bắt Đầu Nhanh

### Yêu Cầu

- PHP >= 8.2
- Composer >= 2.0
- MySQL >= 8.0
- Redis >= 6.0 (cache, queue, broadcasting — dùng qua Predis)
- Node.js >= 18.x (build asset & server realtime)
- Tài khoản Cloudinary (lưu trữ media)
- Tài khoản Google Cloud (Client ID/Secret cho đăng nhập Google)

### Cài Đặt

```bash
# 1. Clone repository
git clone <repo-url>
cd laravel-movie-booking

# 2. Cài đặt dependencies PHP & JS
composer install
npm install

# 3. Thiết lập môi trường
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# 4. Cấu hình database, Redis, Cloudinary, Google, VNPay/Momo trong .env
#    (xem mục Cấu Hình bên dưới)

# 5. Chạy migrations & seeders
php artisan migrate --seed

# 6. Link storage
php artisan storage:link

# 7. Sinh tài liệu API (Scribe)
php artisan scribe:generate

# 8. Khởi động toàn bộ môi trường dev (server + queue + log + vite)
composer dev
```

> `composer dev` chạy song song `php artisan serve`, `queue:listen`, `pail` (xem log realtime) và `npm run dev` — tương ứng script `dev` khai báo trong `composer.json`.

**Truy cập:** `http://localhost:8000`
**Tài liệu API:** `http://localhost:8000/docs`

---

## ⚙️ Cấu Hình

### Biến Môi Trường

<details>
<summary><b>Cấu Hình Database</b></summary>

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gauphim
DB_USERNAME=root
DB_PASSWORD=
```

</details>

<details>
<summary><b>Redis (Cache, Queue, Broadcasting)</b></summary>

```env
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

</details>

<details>
<summary><b>Xác Thực (JWT & Google OAuth)</b></summary>

```env
# JWT
JWT_SECRET=your_secret_key
JWT_TTL=60
JWT_REFRESH_TTL=20160

# Google OAuth (Laravel Socialite)
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback
```

</details>

<details>
<summary><b>Cổng Thanh Toán</b></summary>

```env
# VNPay
VNPAY_TMN_CODE=your_code
VNPAY_HASH_SECRET=your_secret
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=http://localhost:8000/api/payments/vnpay/return

# Momo
MOMO_PARTNER_CODE=your_code
MOMO_ACCESS_KEY=your_key
MOMO_SECRET_KEY=your_secret
MOMO_RETURN_URL=http://localhost:8000/api/payments/momo/return
```

</details>

<details>
<summary><b>Cloudinary (Lưu Trữ Media)</b></summary>

```env
CLOUDINARY_URL=cloudinary://<api_key>:<api_secret>@<cloud_name>
CLOUDINARY_UPLOAD_PRESET=your_preset
```

</details>

<details>
<summary><b>Realtime (Socket.IO / Pusher)</b></summary>

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=ap1
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

## 📚 Tài Liệu API (Scribe)

Dự án sử dụng **[Scribe](https://scribe.knuckles.wtf/laravel/)** để tự sinh tài liệu API trực tiếp từ code (routes, FormRequests, Resources), kèm giao diện Try-It-Out ngay trên trình duyệt.

### URL Cơ Bản

```
Development: http://localhost:8000/api
Production:  https://api.gauphim.com/api
```

### Xác Thực

Các endpoint yêu cầu đăng nhập cần gửi kèm Bearer token trong header:

```http
Authorization: Bearer {access_token}
Content-Type: application/json
```

### Sinh Lại Tài Liệu

```bash
php artisan scribe:generate
```

Sau khi chạy, tài liệu sẽ có sẵn tại:

```
http://localhost:8000/docs
```

File OpenAPI & Postman Collection được sinh tại:

```
storage/app/private/scribe/openapi.yaml
storage/app/private/scribe/collection.json
```

### Nhóm Endpoint Chính

<details>
<summary><b>Xác thực</b></summary>

```http
POST   /api/auth/register              # Đăng ký người dùng
POST   /api/auth/login                 # Đăng nhập
GET    /api/auth/google/redirect       # Chuyển hướng đăng nhập Google
GET    /api/auth/google/callback       # Callback đăng nhập Google
POST   /api/auth/logout                # Đăng xuất
POST   /api/auth/refresh               # Làm mới token
GET    /api/auth/me                    # Thông tin người dùng hiện tại
```

</details>

<details>
<summary><b>Phim & Suất Chiếu</b></summary>

```http
GET    /api/movies                     # Danh sách phim
GET    /api/movies/{id}                # Chi tiết phim
GET    /api/showtimes                  # Danh sách suất chiếu
GET    /api/showtimes/{id}/seats       # Sơ đồ ghế theo suất chiếu
```

</details>

<details>
<summary><b>Đặt Vé & Thanh Toán</b></summary>

```http
GET    /api/orders                     # Đơn hàng của người dùng [Auth]
POST   /api/orders                     # Tạo đơn hàng [Auth]
POST   /api/payments/vnpay/create      # Tạo thanh toán VNPay
GET    /api/payments/vnpay/return      # VNPay callback
POST   /api/payments/momo/create       # Tạo thanh toán Momo
POST   /api/payments/momo/return       # Momo callback
```

</details>

> 📖 Danh sách đầy đủ tất cả endpoint (kèm mô tả tham số, ví dụ request/response) xem tại `/docs` sau khi chạy `scribe:generate`.

---

## 🧪 Kiểm Thử

```bash
# Chạy tất cả tests
php artisan test

# Chạy test suite cụ thể
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Chất Lượng Code

```bash
# Kiểm tra & sửa code style
./vendor/bin/pint
```

---

## 🗂️ Cấu Trúc Dự Án

Dự án theo kiến trúc **Repository – Service – Controller**: mỗi module nghiệp vụ có đủ bộ `Controller → Service → Repository → Model`, đi kèm `FormRequest` để validate và `Resource` để định dạng response.

```
app/
├── Console/Commands/          # Lệnh Artisan tuỳ chỉnh (vd: MigrateImagesToCloudinary)
├── Events/                    # Sự kiện (vd: BookingCreated)
├── Helpers/                   # Hàm hỗ trợ dùng chung
├── Http/
│   ├── Controllers/Api/       # 30+ controller theo từng module nghiệp vụ
│   ├── Middleware/            # AdminMiddleware, CheckRoleMiddleware
│   ├── Requests/               # FormRequest validate dữ liệu vào
│   └── Resources/              # Định dạng response JSON
├── Listeners/                 # Xử lý sự kiện (vd: gửi email đặt vé)
├── Models/                    # Eloquent models
├── Providers/                 # Service Providers
├── Repositories/              # Tầng truy vấn dữ liệu
└── Services/                  # Tầng xử lý logic nghiệp vụ

config/
├── cloudinary.php             # Cấu hình Cloudinary
├── cors.php                   # Cấu hình CORS
├── jwt.php                    # Cấu hình JWT
└── scribe.php                 # Cấu hình sinh tài liệu API

routes/
├── api.php                    # Toàn bộ route API
├── console.php
└── web.php

storage/app/private/scribe/    # Tài liệu API đã sinh (OpenAPI, Postman Collection)
```

Các module nghiệp vụ hiện có: `Auditlog`, `Auth`, `Banner`, `Cinema`, `Contact`, `Dashboard`, `Distributor`, `Foodanddrink`, `Genre`, `Loginhistory`, `Membership`, `Menu`, `Movie`, `Moviecast`, `Moviegenre`, `News`, `Notification`, `Order`, `Orderdetail`, `Payment`, `Promotion`, `Review`, `Role`, `Room` (+ `RoomManagement`, `RoomSeat`), `Schedule`, `Seat` (+ `SeatManagement`), `Showtime`, `Showtimeseat`, `Staff`, `Ticket` (+ `TicketManagement`), `User`, `Wishlist`.

---

## 🚀 Triển Khai

### Checklist Production

- [ ] Đặt `APP_ENV=production` và `APP_DEBUG=false`
- [ ] Cấu hình database MySQL production
- [ ] Cấu hình Redis production (cache/queue/session)
- [ ] Bật HTTPS/SSL
- [ ] Cấu hình callback URL thật cho Google OAuth, VNPay, Momo
- [ ] Cấu hình Cloudinary production
- [ ] Cấu hình server broadcasting (Pusher/Socket.IO) production
- [ ] Thiết lập queue workers & task scheduler
- [ ] Sinh lại tài liệu API (`scribe:generate`) trước khi deploy

### Lệnh Triển Khai

```bash
# Pull code mới nhất
git pull origin main

# Cài đặt dependencies
composer install --no-dev --optimize-autoloader

# Chạy migrations
php artisan migrate --force

# Sinh tài liệu API
php artisan scribe:generate

# Tối ưu cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Khởi động lại services
php artisan queue:restart
sudo systemctl reload php8.2-fpm nginx
```

---

## 🔒 Bảo Mật

- ✅ Xác thực JWT + Đăng nhập Google (OAuth)
- ✅ Kiểm soát truy cập theo vai trò (Admin / Back-office Staff / Staff Rạp)
- ✅ Cấu hình CORS riêng cho từng môi trường
- ✅ Nhật ký thao tác hệ thống (Auditlog)
- ✅ Ngăn chặn SQL injection, bảo vệ XSS/CSRF
- ✅ Xác minh chữ ký callback thanh toán (VNPay/Momo)

---

## 🤝 Đóng Góp

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/ten-tinh-nang`)
3. Commit thay đổi (`git commit -m 'feat: thêm tính năng abc'`)
4. Push lên branch (`git push origin feature/ten-tinh-nang`)
5. Mở Pull Request

### Tiêu Chuẩn Code

- Tuân theo PSR-12
- Viết test cho tính năng mới (PHPUnit)
- Cập nhật tài liệu Scribe (docblock trên controller/FormRequest) khi thêm/sửa endpoint
- Commit message rõ ràng, theo dạng `feat:`, `fix:`, `refactor:`...

---

## 📝 Giấy Phép

Dự án được cấp phép theo Giấy phép MIT — xem file [LICENSE](LICENSE) để biết chi tiết.

---

<div align="center">

**Được phát triển bởi Gấu Phim Team**

⭐ Star repo này nếu bạn thấy hữu ích!

</div>