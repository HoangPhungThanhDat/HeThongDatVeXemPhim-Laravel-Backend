@php
    use Knuckles\Scribe\Tools\WritingUtils as u;
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{!! $metadata['title'] !!}</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{!! $assetPathPrefix !!}css/theme-default.style.css" media="screen">
    <link rel="stylesheet" href="{!! $assetPathPrefix !!}css/theme-default.print.css" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

@if(isset($metadata['example_languages']))
    <style id="language-style">
        @foreach($metadata['example_languages'] as $lang)
            body .content .{{ $lang }}-example code { display: none; }
        @endforeach
    </style>
@endif

@if($tryItOut['enabled'] ?? true)
    <script>
        var tryItOutBaseUrl = "{!! $tryItOut['base_url'] ?? $baseUrl !!}";
        var useCsrf = Boolean({!! $tryItOut['use_csrf'] ?? null !!});
        var csrfUrl = "{!! $tryItOut['csrf_url'] ?? null !!}";
    </script>
    <script src="{{ u::getVersionedAsset($assetPathPrefix.'js/tryitout.js') }}"></script>
@endif

    <script src="{{ u::getVersionedAsset($assetPathPrefix.'js/theme-default.js') }}"></script>

    <style>
        /* ================================================================
           GẤU PHIM API - PROFESSIONAL DARK THEME (FULL WIDTH FIX)
           ================================================================ */

        /* ----- RESET & BODY ----- */
        * {
            box-sizing: border-box;
        }

        body {
            background: #0a0a16 !important;
            color: #e8edf5 !important;
            font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* ================================================================
           FIX: TẤT CẢ HEADINGS
           ================================================================ */
        h1, h2, h3, h4, h5, h6 {
            color: #e8edf5 !important;
            font-weight: 700 !important;
        }

        .content h1 {
            color: #ffffff !important;
            font-size: 32px !important;
            font-weight: 700 !important;
            border-bottom: 2px solid rgba(233, 69, 96, 0.2) !important;
            padding-bottom: 16px !important;
            margin-bottom: 24px !important;
            background: transparent !important;
        }

        .content h2 {
            color: #e8edf5 !important;
            font-size: 24px !important;
            font-weight: 700 !important;
            border-bottom: 2px solid rgba(233, 69, 96, 0.15) !important;
            padding-bottom: 12px !important;
            margin-top: 36px !important;
            margin-bottom: 20px !important;
            background: transparent !important;
        }

        .content h2::before {
            content: "✦ " !important;
            color: #e94560 !important;
        }

        .content h3 {
            color: #c8d0e0 !important;
            font-size: 18px !important;
            font-weight: 600 !important;
            margin-top: 24px !important;
            margin-bottom: 14px !important;
            background: transparent !important;
        }

        .content h4 {
            color: #b0b8d0 !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            background: transparent !important;
        }

        .content h5 {
            color: #8895aa !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            background: transparent !important;
        }

        /* ================================================================
           FIX: TẤT CẢ INPUT, TEXTAREA, SELECT
           ================================================================ */
        input,
        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="password"],
        input[type="search"],
        input[type="tel"],
        input[type="url"],
        input[type="date"],
        input[type="datetime-local"],
        input[type="month"],
        input[type="time"],
        input[type="week"],
        textarea,
        select,
        .form-control,
        .form-input,
        .form-control:focus,
        .form-input:focus {
            background: #111128 !important;
            border: 1px solid #2a2a4a !important;
            color: #e8edf5 !important;
            border-radius: 6px !important;
            padding: 10px 16px !important;
            font-size: 14px !important;
            transition: all 0.3s !important;
            width: 100% !important;
            outline: none !important;
            box-shadow: none !important;
            -webkit-text-fill-color: #e8edf5 !important;
            opacity: 1 !important;
        }

        input:focus,
        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="search"]:focus,
        textarea:focus,
        select:focus,
        .form-control:focus,
        .form-input:focus {
            border-color: #e94560 !important;
            background: #1a1a3a !important;
            box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.12) !important;
            -webkit-text-fill-color: #e8edf5 !important;
        }

        input::placeholder,
        textarea::placeholder {
            color: #667799 !important;
            -webkit-text-fill-color: #667799 !important;
            opacity: 1 !important;
        }

        /* Fix cho autofill */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #111128 inset !important;
            -webkit-text-fill-color: #e8edf5 !important;
            background-color: #111128 !important;
            background: #111128 !important;
            border-color: #2a2a4a !important;
        }

        /* ================================================================
           FIX: LABELS
           ================================================================ */
        label,
        .form-label,
        .control-label {
            color: #b0b8d0 !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            margin-bottom: 6px !important;
            display: block !important;
        }

        /* ================================================================
           FIX: TRY IT OUT CONTAINER
           ================================================================ */
        .try-it-out,
        .try-out {
            background: #111128 !important;
            border: 1px solid #1a1a3a !important;
            border-radius: 8px !important;
            padding: 16px !important;
            margin-top: 12px !important;
        }

        .try-it-out input,
        .try-it-out textarea,
        .try-it-out select,
        .try-out input,
        .try-out textarea,
        .try-out select {
            background: #0a0a16 !important;
            border: 1px solid #2a2a4a !important;
            color: #e8edf5 !important;
            -webkit-text-fill-color: #e8edf5 !important;
        }

        .try-it-out input:focus,
        .try-it-out textarea:focus,
        .try-it-out select:focus,
        .try-out input:focus,
        .try-out textarea:focus,
        .try-out select:focus {
            border-color: #e94560 !important;
            box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.12) !important;
            background: #1a1a3a !important;
            -webkit-text-fill-color: #e8edf5 !important;
        }

        /* ================================================================
           SIDEBAR - CỐ ĐỊNH KÍCH THƯỚC
           ================================================================ */
        .tocify-wrapper {
            background: #0d0d1a !important;
            border-right: 2px solid #1a1a3a !important;
            min-height: 100vh !important;
            padding: 0 !important;
            width: 320px !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            bottom: 0 !important;
            overflow-y: auto !important;
            z-index: 100 !important;
        }

        .brand-header {
            padding: 24px 20px 20px !important;
            border-bottom: 2px solid rgba(233, 69, 96, 0.15) !important;
            text-align: center !important;
            background: linear-gradient(180deg, rgba(233, 69, 96, 0.05) 0%, transparent 100%) !important;
        }

        .brand-name {
            font-size: 24px !important;
            font-weight: 800 !important;
            color: #ffffff !important;
            letter-spacing: -0.5px !important;
            background: linear-gradient(135deg, #e94560, #ff6b81) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            background-clip: text !important;
        }

        .brand-sub {
            font-size: 12px !important;
            color: #667799 !important;
            margin-top: 4px !important;
            letter-spacing: 1px !important;
            text-transform: uppercase !important;
        }

        .brand-header .version-badge {
            display: inline-block !important;
            margin-top: 8px !important;
            background: rgba(233, 69, 96, 0.2) !important;
            color: #e94560 !important;
            padding: 3px 14px !important;
            border-radius: 12px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            border: 1px solid rgba(233, 69, 96, 0.2) !important;
            -webkit-text-fill-color: #e94560 !important;
        }

        .tocify-wrapper .search {
            padding: 16px 20px 12px !important;
            background: #0d0d1a !important;
        }

        .tocify-wrapper .search input {
            background: #1a1a3a !important;
            border: 1px solid #2a2a4a !important;
            color: #e8edf5 !important;
            border-radius: 8px !important;
            padding: 10px 16px !important;
            width: 100% !important;
            font-size: 13px !important;
            transition: all 0.3s !important;
            -webkit-text-fill-color: #e8edf5 !important;
        }

        .tocify-wrapper .search input:focus {
            border-color: #e94560 !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.15) !important;
        }

        .tocify-wrapper .search input::placeholder {
            color: #667799 !important;
            -webkit-text-fill-color: #667799 !important;
        }

        #toc {
            padding: 0 0 20px 0 !important;
        }

        .tocify-header {
            margin: 0 !important;
        }

        .tocify-item.level-1 > a {
            color: #8895aa !important;
            font-weight: 700 !important;
            font-size: 12px !important;
            text-transform: uppercase !important;
            letter-spacing: 1.5px !important;
            padding: 10px 20px 6px !important;
            display: block !important;
            border-bottom: 1px solid rgba(26, 26, 58, 0.3) !important;
        }

        .tocify-item.level-1 > a:hover {
            color: #e8edf5 !important;
        }

        .tocify-item.level-2 > a {
            color: #b0b8d0 !important;
            font-weight: 400 !important;
            font-size: 13px !important;
            padding: 8px 20px 8px 36px !important;
            display: block !important;
            border-left: 3px solid transparent !important;
            transition: all 0.2s !important;
        }

        .tocify-item.level-2 > a:hover {
            background: rgba(233, 69, 96, 0.08) !important;
            color: #ffffff !important;
            border-left-color: #e94560 !important;
        }

        .tocify-item.level-2.active > a {
            background: rgba(233, 69, 96, 0.12) !important;
            color: #ffffff !important;
            border-left-color: #e94560 !important;
        }

        .tocify-item.level-3 > a {
            color: #667799 !important;
            font-size: 12px !important;
            padding: 5px 20px 5px 52px !important;
            display: block !important;
        }

        .tocify-item.level-3 > a:hover {
            color: #b0b8d0 !important;
        }

        .toc-footer {
            border-top: 1px solid #1a1a3a !important;
            padding: 16px 20px !important;
            list-style: none !important;
            margin: 0 !important;
        }

        .toc-footer li {
            color: #667799 !important;
            font-size: 12px !important;
            padding: 4px 0 !important;
        }

        .toc-footer li a {
            color: #667799 !important;
            text-decoration: none !important;
            transition: color 0.2s !important;
        }

        .toc-footer li a:hover {
            color: #e94560 !important;
        }

        #last-updated {
            border-top: 1px solid #1a1a3a !important;
            padding: 12px 20px !important;
            list-style: none !important;
            margin: 0 !important;
        }

        #last-updated li {
            color: #667799 !important;
            font-size: 11px !important;
        }

        /* ================================================================
           CONTENT - FULL WIDTH (KHÔNG GIỚI HẠN)
           ================================================================ */
        .page-wrapper {
            margin-left: 320px !important;
            min-height: 100vh !important;
        }

        .content {
            padding: 30px 60px 50px !important;
            background: #0a0a16 !important;
            max-width: 100% !important; /* Thay đổi từ 1200px thành 100% */
            width: 100% !important;
        }

        /* ================================================================
           CARDS - TỐI ƯU CHO MÀN HÌNH LỚN
           ================================================================ */
        .card {
            background: #111128 !important;
            border: 1px solid #1a1a3a !important;
            border-radius: 12px !important;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.4) !important;
            margin-bottom: 24px !important;
            overflow: hidden !important;
            transition: box-shadow 0.3s ease !important;
        }

        .card:hover {
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.6) !important;
        }

        .card-header {
            background: rgba(233, 69, 96, 0.06) !important;
            border-bottom: 1px solid #1a1a3a !important;
            padding: 14px 24px !important;
            display: flex !important;
            align-items: center !important;
            gap: 16px !important;
            flex-wrap: wrap !important;
        }

        .card-header .method {
            font-weight: 700 !important;
            font-size: 12px !important;
            padding: 4px 16px !important;
            border-radius: 4px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            color: #ffffff !important;
        }

        .method-get { background: #61affe !important; }
        .method-post { background: #49cc90 !important; }
        .method-put { background: #fca130 !important; }
        .method-delete { background: #f93e3e !important; }
        .method-patch { background: #50e3c2 !important; color: #111128 !important; }

        .card-header .path {
            color: #e8edf5 !important;
            font-family: 'Courier New', monospace !important;
            font-size: 15px !important;
            font-weight: 500 !important;
        }

        .card-header .summary {
            color: #667799 !important;
            font-size: 13px !important;
            margin-left: auto !important;
        }

        .card-body {
            padding: 24px 28px !important;
            color: #c8d0e0 !important;
            font-size: 14px !important;
        }

        /* ----- CODE BLOCKS ----- */
        pre {
            background: #0a0a16 !important;
            border: 1px solid #1a1a3a !important;
            border-radius: 8px !important;
            padding: 16px 20px !important;
            font-size: 14px !important;
            line-height: 1.8 !important;
            color: #e8edf5 !important;
            overflow-x: auto !important;
        }

        code {
            background: rgba(233, 69, 96, 0.15) !important;
            color: #ff6b81 !important;
            padding: 2px 8px !important;
            border-radius: 4px !important;
            font-size: 13px !important;
        }

        /* ----- BUTTONS ----- */
        .btn {
            display: inline-block !important;
            font-weight: 600 !important;
            padding: 8px 20px !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            transition: all 0.3s !important;
            border: none !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, #e94560, #c23152) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(233, 69, 96, 0.3) !important;
        }

        .btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 24px rgba(233, 69, 96, 0.5) !important;
        }

        .btn-secondary {
            background: #1a1a3a !important;
            border: 1px solid #2a2a4a !important;
            color: #b0b8d0 !important;
        }

        .btn-secondary:hover {
            background: #2a2a4a !important;
            color: #ffffff !important;
        }

        /* ----- TABLES - FULL WIDTH ----- */
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            background: #111128 !important;
            border-radius: 8px !important;
            overflow: hidden !important;
            margin: 12px 0 !important;
        }

        table th {
            background: #1a1a3a !important;
            color: #e8edf5 !important;
            font-weight: 600 !important;
            padding: 12px 20px !important;
            text-align: left !important;
            font-size: 12px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }

        table td {
            color: #c8d0e0 !important;
            padding: 12px 20px !important;
            border-bottom: 1px solid #1a1a3a !important;
        }

        table tr:hover td {
            background: rgba(233, 69, 96, 0.04) !important;
        }

        /* ----- AUTH BADGES ----- */
        .auth-required {
            background: rgba(233, 69, 96, 0.2) !important;
            color: #ff6b81 !important;
            padding: 2px 12px !important;
            border-radius: 12px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            border: 1px solid rgba(233, 69, 96, 0.3) !important;
        }

        .auth-not-required {
            background: rgba(73, 204, 144, 0.2) !important;
            color: #66d9a0 !important;
            padding: 2px 12px !important;
            border-radius: 12px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            border: 1px solid rgba(73, 204, 144, 0.3) !important;
        }

        /* ----- LINKS ----- */
        a {
            color: #64ffda !important;
            text-decoration: none !important;
            transition: color 0.2s !important;
        }

        a:hover {
            color: #ff6b81 !important;
            text-decoration: underline !important;
        }

        /* ----- TRY IT OUT BUTTON ----- */
        .try-it-out-btn {
            background: linear-gradient(135deg, #00d4ff, #0090ff) !important;
            border: none !important;
            border-radius: 6px !important;
            padding: 6px 18px !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            cursor: pointer !important;
            transition: all 0.3s !important;
        }

        .try-it-out-btn:hover {
            transform: scale(1.02) !important;
            box-shadow: 0 4px 15px rgba(0, 212, 255, 0.4) !important;
        }

        /* ----- RESPONSE STATUS ----- */
        .status-200 { color: #49cc90 !important; font-weight: 700 !important; }
        .status-201 { color: #49cc90 !important; font-weight: 700 !important; }
        .status-400 { color: #fca130 !important; font-weight: 700 !important; }
        .status-401 { color: #f93e3e !important; font-weight: 700 !important; }
        .status-403 { color: #f93e3e !important; font-weight: 700 !important; }
        .status-404 { color: #f93e3e !important; font-weight: 700 !important; }
        .status-500 { color: #f93e3e !important; font-weight: 700 !important; }

        /* ----- SCROLLBAR ----- */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0a0a16;
        }
        ::-webkit-scrollbar-thumb {
            background: #2a2a4a;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #e94560;
        }

        /* ================================================================
           RESPONSIVE - TỐI ƯU CHO MỌI MÀN HÌNH
           ================================================================ */
        
        /* Màn hình lớn (Desktop) */
        @media (min-width: 1400px) {
            .content {
                padding: 40px 80px 60px !important;
            }
            .card-header {
                padding: 16px 32px !important;
            }
            .card-body {
                padding: 28px 32px !important;
            }
            table th,
            table td {
                padding: 14px 24px !important;
            }
        }

        /* Màn hình trung bình (Laptop) */
        @media (max-width: 1200px) {
            .content {
                padding: 24px 40px !important;
            }
        }

        /* Màn hình nhỏ (Tablet) */
        @media (max-width: 992px) {
            .tocify-wrapper {
                width: 280px !important;
            }
            .page-wrapper {
                margin-left: 280px !important;
            }
            .content {
                padding: 20px 30px !important;
            }
            .card-header {
                padding: 12px 16px !important;
            }
            .card-body {
                padding: 16px 20px !important;
            }
        }

        /* Màn hình rất nhỏ (Mobile) */
        @media (max-width: 768px) {
            .tocify-wrapper {
                width: 100% !important;
                position: relative !important;
                min-height: auto !important;
                border-right: none !important;
                border-bottom: 2px solid #1a1a3a !important;
            }
            .page-wrapper {
                margin-left: 0 !important;
            }
            .content {
                padding: 16px 20px !important;
            }
            .docs-header h1 {
                font-size: 18px !important;
            }
            .card-header {
                flex-wrap: wrap !important;
                gap: 8px !important;
            }
            .card-header .summary {
                margin-left: 0 !important;
                width: 100% !important;
            }
            pre {
                font-size: 12px !important;
                padding: 12px !important;
            }
            .brand-name {
                font-size: 20px !important;
            }
            .card-header .path {
                font-size: 13px !important;
            }
        }

        /* Màn hình siêu nhỏ */
        @media (max-width: 480px) {
            .content {
                padding: 12px 14px !important;
            }
            .card-header .method {
                font-size: 10px !important;
                padding: 3px 10px !important;
            }
            .card-header .path {
                font-size: 12px !important;
            }
            table th,
            table td {
                padding: 8px 12px !important;
                font-size: 12px !important;
            }
        }

        /* ----- LANGUAGE SELECTOR ----- */
        .lang-selector {
            background: #0d0d1a !important;
            padding: 8px 0 !important;
        }

        .lang-selector .lang-button {
            background: transparent !important;
            color: #667799 !important;
            border: none !important;
            padding: 6px 12px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: color 0.2s !important;
        }

        .lang-selector .lang-button:hover {
            color: #e8edf5 !important;
        }

        .lang-selector .lang-button.active {
            color: #e94560 !important;
        }

        /* ----- FIX: PARAMETER TABLE ----- */
        .parameters {
            background: #111128 !important;
        }

        .parameters th {
            background: #1a1a3a !important;
            color: #e8edf5 !important;
        }

        .parameters td {
            color: #c8d0e0 !important;
        }

        /* ----- FIX: RESPONSE EXAMPLE ----- */
        .response-example {
            background: #0a0a16 !important;
            border: 1px solid #1a1a3a !important;
            border-radius: 8px !important;
            padding: 16px !important;
        }

        /* ----- FIX: INTRO TEXT ----- */
        .content p,
        .content li,
        .content div:not(.card):not(.try-it-out) {
            color: #c8d0e0 !important;
        }

        .content strong,
        .content b {
            color: #e8edf5 !important;
        }

        /* ----- FIX: AUTH SECTION ----- */
        .auth-section {
            background: #111128 !important;
            border: 1px solid #1a1a3a !important;
            border-radius: 8px !important;
            padding: 20px !important;
            margin: 16px 0 !important;
        }

        .auth-section h1,
        .auth-section h2,
        .auth-section h3,
        .auth-section h4 {
            color: #e8edf5 !important;
        }
    </style>
</head>

<body data-languages="{{ json_encode($metadata['example_languages'] ?? []) }}">

@include("scribe::themes.default.sidebar")

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        {!! $intro !!}

        {!! $auth !!}

        @include("scribe::themes.default.groups")

        {!! $append !!}
    </div>
    <div class="dark-box">
        @if(isset($metadata['example_languages']))
            <div class="lang-selector">
                @foreach($metadata['example_languages'] as $name => $lang)
                    @php if (is_numeric($name)) $name = $lang; @endphp
                    <button type="button" class="lang-button" data-language-name="{{$lang}}">{{$name}}</button>
                @endforeach
            </div>
        @endif
    </div>
</div>
</body>
</html>