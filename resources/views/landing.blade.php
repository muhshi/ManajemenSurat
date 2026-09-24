<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Surat & Administrasi Terpadu - BPS Kabupaten Demak</title>
    <link rel="icon" href="{{ asset('images/logo_bps.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bps-blue: #0054A6;
            --bps-dark-blue: #00376c;
            --bps-green: #00A651;
            --bps-orange: #F7941D;
            --bg-slate: #f8fafc;
            --surface: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-slate);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Navbar */
        nav {
            padding: 1rem 0;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none;
        }

        .logo-text {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--bps-blue);
            letter-spacing: -0.02em;
        }

        .logo-text span {
            color: var(--bps-orange);
        }

        .logo-subtitle {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .nav-links a:hover {
            color: var(--bps-blue);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn {
            padding: 0.65rem 1.35rem;
            border-radius: 0.6rem;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--bps-blue) 0%, var(--bps-dark-blue) 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(0, 84, 166, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 84, 166, 0.35);
        }

        .btn-sso {
            background: #ffffff;
            color: #047857;
            border-color: #a7f3d0;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.12);
        }

        .btn-sso:hover {
            background: #ecfdf5;
            border-color: #34d399;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border-color: var(--border-color);
            color: var(--text-main);
        }

        .btn-outline:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        /* Hero Section */
        header {
            padding: 8.5rem 0 5rem;
            background: radial-gradient(circle at 50% 10%, rgba(0, 84, 166, 0.08) 0%, rgba(248, 250, 252, 1) 75%);
            position: relative;
            text-align: center;
        }

        .hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(0, 84, 166, 0.08);
            border: 1px solid rgba(0, 84, 166, 0.15);
            color: var(--bps-blue);
            padding: 0.45rem 1.15rem;
            border-radius: 9999px;
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 1.75rem;
        }

        .hero-pill-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: var(--bps-green);
            box-shadow: 0 0 0 3px rgba(0, 166, 81, 0.2);
        }

        h1 {
            font-size: 3.25rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.15;
            max-width: 900px;
            margin: 0 auto 1.5rem;
            color: #0b1e33;
        }

        h1 .highlight {
            background: linear-gradient(135deg, var(--bps-blue) 0%, #0284c7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            font-size: 1.15rem;
            color: var(--text-muted);
            max-width: 720px;
            margin: 0 auto 2.5rem;
            line-height: 1.7;
        }

        .hero-cta {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 3.5rem;
        }

        .stats-bar {
            display: flex;
            justify-content: center;
            gap: 3rem;
            flex-wrap: wrap;
            padding-top: 2rem;
            border-top: 1px solid rgba(226, 232, 240, 0.8);
            max-width: 900px;
            margin: 0 auto;
        }

        .stat-item {
            text-align: center;
        }

        .stat-num {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--bps-blue);
        }

        .stat-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        /* Features Section */
        .section-wrap {
            padding: 6rem 0;
            position: relative;
        }

        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 4rem;
        }

        .section-tag {
            font-size: 0.8rem;
            font-weight: 800;
            color: var(--bps-blue);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.6rem;
            display: inline-block;
        }

        .section-title {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.85rem;
            color: #0b1e33;
        }

        .section-desc {
            font-size: 1rem;
            color: var(--text-muted);
        }

        .grid-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 1.75rem;
        }

        .feature-card {
            background: var(--surface);
            border: 1px solid var(--border-color);
            border-radius: 1.25rem;
            padding: 2.25rem;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-sm);
        }

        .feature-card:hover {
            transform: translateY(-6px);
            border-color: #93c5fd;
            box-shadow: 0 20px 30px -10px rgba(0, 84, 166, 0.12);
        }

        .card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .card-icon-wrap {
            width: 52px;
            height: 52px;
            border-radius: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .badge-active {
            background: #dcfce7;
            color: #15803d;
            padding: 0.3rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .badge-active::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: #16a34a;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.75rem;
            letter-spacing: -0.01em;
        }

        .feature-card p {
            font-size: 0.92rem;
            color: var(--text-muted);
            line-height: 1.65;
            margin-bottom: 1.25rem;
            flex-grow: 1;
        }

        .feature-bullets {
            list-style: none;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .feature-bullets li {
            font-size: 0.82rem;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .feature-bullets li svg {
            color: var(--bps-green);
            flex-shrink: 0;
        }

        /* Banner CTA */
        .cta-banner {
            background: linear-gradient(135deg, #00376c 0%, var(--bps-blue) 100%);
            border-radius: 1.75rem;
            padding: 4rem 2rem;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            margin-top: 4rem;
        }

        .cta-banner h2 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .cta-banner p {
            font-size: 1.05rem;
            color: #cbd5e1;
            max-width: 650px;
            margin: 0 auto 2.5rem;
        }

        .cta-banner .btn-white {
            background: white;
            color: var(--bps-blue);
            font-weight: 700;
            padding: 0.85rem 2rem;
            border-radius: 0.65rem;
        }

        .cta-banner .btn-white:hover {
            background: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Footer */
        footer {
            background: #09131f;
            color: #94a3b8;
            padding: 4.5rem 0 2.5rem;
            font-size: 0.9rem;
            border-top: 1px solid #1e293b;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3.5rem;
        }

        .footer-brand h4 {
            color: white;
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
        }

        .footer-brand h4 span {
            color: var(--bps-orange);
        }

        .footer-brand p {
            line-height: 1.7;
            max-width: 380px;
            font-size: 0.85rem;
        }

        .footer-col h5 {
            color: white;
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .footer-col ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }

        .footer-col ul a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s ease;
        }

        .footer-col ul a:hover {
            color: white;
        }

        .footer-bottom {
            padding-top: 2rem;
            border-top: 1px solid #1e293b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .nav-links {
                display: none;
            }
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2.35rem;
            }

            .stats-bar {
                gap: 1.5rem;
            }

            .grid-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <!-- Navigation -->
    <nav>
        <div class="container">
            <a href="/" class="logo-wrap">
                <img src="{{ asset('/images/logo_bps.png') }}" alt="Logo BPS" height="38">
                <div>
                    <div class="logo-text">Manajemen<span>Surat</span></div>
                    <div class="logo-subtitle">BPS Kabupaten Demak</div>
                </div>
            </a>

            <ul class="nav-links">
                <li><a href="#features">Modul Layanan</a></li>
                <li><a href="#bantuan">Panduan</a></li>
            </ul>

            <div class="nav-actions">
                <a href="{{ route('sipetra.login') }}" class="btn btn-sso" title="Login Menggunakan Akun SIPETRA BPS">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 0 0zm3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/>
                    </svg>
                    SSO SIPETRA
                </a>
                <a href="/admin" class="btn btn-primary">
                    Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Header -->
    <header>
        <div class="container">
            <div class="hero-pill">
                <span class="hero-pill-dot"></span>
                <span>Versi 2.0 &bull; Administrasi & Perpajakan Terintegrasi</span>
            </div>

            <h1>Digitalisasi Tata Kelola Kedinasan <br><span class="highlight">BPS Kabupaten Demak</span></h1>
            
            <p class="hero-desc">
                Platform all-in-one terpadu untuk administrasi surat keluar/masuk, alur disposisi, rekapitulasi SP2D & rincian pajak, persediaan ATK/ARK, hingga agenda dinas dan notulensi rapat.
            </p>

            <div class="hero-cta">
                <a href="/admin" class="btn btn-primary" style="padding: 0.85rem 1.85rem; font-size: 0.98rem;">
                    Buka Dashboard Admin
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                </a>
                <a href="{{ route('sipetra.login') }}" class="btn btn-sso" style="padding: 0.85rem 1.65rem; font-size: 0.98rem;">
                    Masuk dengan SSO SIPETRA
                </a>
            </div>

            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-num">Rekap Per Pihak</div>
                    <div class="stat-label">Akumulasi Pajak per NPWP / NIK</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">Multi-Sheet & PDF</div>
                    <div class="stat-label">Excel Numerik & Navigasi Bookmark</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">Role Shield</div>
                    <div class="stat-label">Hak Akses Granular Berbasis Peran</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">Digital Disposisi</div>
                    <div class="stat-label">Pimpinan ke Staf Real-Time</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Services / Modules Grid -->
    <section class="section-wrap" id="features">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Modul Aplikasi</span>
                <h2 class="section-title">Layanan Administrasi Terlengkap</h2>
                <p class="section-desc">Mendukung percepatan birokrasi, transparansi keuangan negara, dan efisiensi logistik secara menyeluruh.</p>
            </div>

            <div class="grid-cards">
                <!-- Card 1: Rekap SP2D & Pajak Per Pihak -->
                <div class="feature-card">
                    <div class="card-top">
                        <div class="card-icon-wrap" style="background: #e0f2fe; color: #0284c7;">
                            💰
                        </div>
                        <span class="badge-active">Terbaru v2.0</span>
                    </div>
                    <h3>Rekap SP2D & Pajak</h3>
                    <p>Import otomatis data SP2D MyIntress, pemisahan rincian potongan pajak, dan rekapitulasi akumulasi per entitas penerima.</p>
                    <ul class="feature-bullets">
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Rekap Potongan Pajak Per Pihak (NPWP/NIK)
                        </li>
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Export Multi-Sheet Excel dengan pemformatan rupiah
                        </li>
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Export PDF Lanskap dengan Bookmark Navigasi
                        </li>
                    </ul>
                </div>

                <!-- Card 2: Generate Surat & SK Otomatis -->
                <div class="feature-card">
                    <div class="card-top">
                        <div class="card-icon-wrap" style="background: #eef2ff; color: #4f46e5;">
                            📄
                        </div>
                        <span class="badge-active">Aktif</span>
                    </div>
                    <h3>Surat Keluar & SK Otomatis</h3>
                    <p>Pembuatan SK dan Surat Dinas instan menggunakan template Word dinamis, penomoran otomatis, dan tata letak tanda tangan terstandar.</p>
                    <ul class="feature-bullets">
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Generate berkas .docx berbasis template resmi
                        </li>
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Klasifikasi surat & kode arsip otomatis
                        </li>
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Konfigurasi blok tanda tangan dan stempel
                        </li>
                    </ul>
                </div>

                <!-- Card 3: Surat Masuk & Disposisi Digital -->
                <div class="feature-card">
                    <div class="card-top">
                        <div class="card-icon-wrap" style="background: #ecfdf5; color: #059669;">
                            📥
                        </div>
                        <span class="badge-active">Aktif</span>
                    </div>
                    <h3>Surat Masuk & Disposisi</h3>
                    <p>Pencatatan surat dinas masuk, pengunggahan scan berkas asli, serta distribusi lembar disposisi digital pimpinan kepada staf berwenang.</p>
                    <ul class="feature-bullets">
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Penyimpanan arsip digital aman & cepat dicari
                        </li>
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Instruksi disposisi pimpinan secara berjenjang
                        </li>
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Pelacakan status tindak lanjut dokumen
                        </li>
                    </ul>
                </div>

                <!-- Card 4: Persediaan ATK / ARK -->
                <div class="feature-card">
                    <div class="card-top">
                        <div class="card-icon-wrap" style="background: #fef3c7; color: #d97706;">
                            📦
                        </div>
                        <span class="badge-active">Aktif</span>
                    </div>
                    <h3>Persediaan ATK & ARK</h3>
                    <p>Pengelolaan stok barang pakai habis, riwayat transaksi masuk/keluar, pengajuan permintaan barang pegawai, hingga kartu persediaan.</p>
                    <ul class="feature-bullets">
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Permintaan barang pakai habis oleh pegawai
                        </li>
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Kalkulasi saldo unit & cetak Kartu Persediaan
                        </li>
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Monitoring kuota persediaan habis pakai
                        </li>
                    </ul>
                </div>

                <!-- Card 5: Pengelolaan Aset BMN -->
                <div class="feature-card">
                    <div class="card-top">
                        <div class="card-icon-wrap" style="background: #f1f5f9; color: #475569;">
                            🏢
                        </div>
                        <span class="badge-active">Aktif</span>
                    </div>
                    <h3>Pengelolaan Aset BMN</h3>
                    <p>Pendataan inventaris Barang Milik Negara (BMN), mapping aset per ruangan kerja, pemantauan kondisi fisik, dan statistik aset kantor.</p>
                    <ul class="feature-bullets">
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Daftar Barang Ruangan (DBR) terintegrasi
                        </li>
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Grafik & ringkasan BMN per kategori
                        </li>
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Riwayat pemeliharaan dan mutasi aset
                        </li>
                    </ul>
                </div>

                <!-- Card 6: Agenda & Notulensi Rapat -->
                <div class="feature-card">
                    <div class="card-top">
                        <div class="card-icon-wrap" style="background: #fae8ff; color: #a21caf;">
                            📅
                        </div>
                        <span class="badge-active">Aktif</span>
                    </div>
                    <h3>Agenda & Notulensi Rapat</h3>
                    <p>Penjadwalan rapat kedinasan, pencatatan daftar hadir peserta rapat, monitoring nomor urut rapat, serta ekspor notulensi terstandar.</p>
                    <ul class="feature-bullets">
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Jadwal rapat & undangan peserta internal
                        </li>
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Generate otomatis draf Notulensi Rapat Word
                        </li>
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Monitoring kalender kegiatan satker
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Banner CTA -->
            <div class="cta-banner">
                <h2>Kelola Seluruh Administrasi Dalam Satu Portal</h2>
                <p>Didukung Single Sign-On (SSO SIPETRA BPS) dan kontrol hak akses terperinci untuk kenyamanan seluruh pegawai BPS Kabupaten Demak.</p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="/admin" class="btn btn-white">Masuk Dashboard Sekarang</a>
                    <a href="{{ route('sipetra.login') }}" class="btn" style="background: rgba(255,255,255,0.15); color: white; border-color: rgba(255,255,255,0.3);">Login SSO SIPETRA</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="bantuan">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h4>BPS <span>Kabupaten Demak</span></h4>
                    <p>
                        Sistem Informasi Administrasi Surat, Pengelolaan SP2D, Inventaris Persediaan, dan Aset BMN Terintegrasi untuk efisiensi birokrasi berstandar modern.
                    </p>
                    <p style="margin-top: 1rem; color: #64748b; font-size: 0.8rem;">
                        📍 Jl. Sultan Hadiwijaya No. 23, Demak, Jawa Tengah
                    </p>
                </div>

                <div class="footer-col">
                    <h5>Modul Layanan</h5>
                    <ul>
                        <li><a href="/admin/surats">Surat Keluar & SK</a></li>
                        <li><a href="/admin/surat-masuks">Surat Masuk & Disposisi</a></li>
                        <li><a href="/admin/sp2d-rekaps">Rekap SP2D & Pajak</a></li>
                        <li><a href="/admin/rekap-per-pihak">Rekap Pajak Per Pihak</a></li>
                        <li><a href="/admin/items">Persediaan ATK / ARK</a></li>
                        <li><a href="/admin/agendas">Agenda & Notulensi</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h5>Akses Cepat</h5>
                    <ul>
                        <li><a href="/admin">Dashboard Filament</a></li>
                        <li><a href="{{ route('sipetra.login') }}">Login SSO SIPETRA</a></li>
                        <li><a href="/admin/system-settings">Pengaturan Sistem</a></li>
                        <li><a href="/admin/shield/roles">Manajemen Hak Akses</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div>&copy; {{ date('Y') }} Badan Pusat Statistik Kabupaten Demak. All rights reserved.</div>
                <div>Versi 2.0.3 &bull; Framework Laravel & Filament</div>
            </div>
        </div>
    </footer>

</body>

</html>