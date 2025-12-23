<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Surat - BPS Kabupaten Demak</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bps-blue: #0054A6;
            --bps-green: #00A651;
            --bps-orange: #F7941D;
            --bg-light: #f8fafc;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Navbar */
        nav {
            padding: 1.5rem 0;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--bps-blue);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo span {
            color: var(--bps-orange);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-primary {
            background-color: var(--bps-blue);
            color: white;
        }

        .btn-primary:hover {
            background-color: #00448a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 84, 166, 0.3);
        }

        .btn-outline {
            border: 2px solid var(--bps-blue);
            color: var(--bps-blue);
        }

        .btn-outline:hover {
            background-color: var(--bps-blue);
            color: white;
        }

        /* Hero Section */
        header {
            padding: 10rem 0 6rem;
            background: linear-gradient(135deg, #ffffff 0%, #eef2ff 100%);
            text-align: center;
        }

        .hero-badge {
            background: rgba(0, 84, 166, 0.1);
            color: var(--bps-blue);
            padding: 0.5rem 1.25rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 2rem;
            display: inline-block;
        }

        h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            line-height: 1.1;
        }

        h1 span {
            color: var(--bps-green);
        }

        .hero-desc {
            font-size: 1.25rem;
            color: var(--text-muted);
            max-width: 700px;
            margin: 0 auto 3rem;
        }

        /* Features */
        .features {
            padding: 6rem 0;
            background: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .card {
            padding: 2.5rem;
            background: var(--bg-light);
            border-radius: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .card:hover {
            transform: translateY(-10px);
            background: white;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: var(--bps-blue);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .card p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .tag {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .tag-soon {
            background: #fef3c7;
            color: #92400e;
        }

        .tag-active {
            background: #dcfce7;
            color: #166534;
        }

        /* Footer */
        footer {
            padding: 4rem 0;
            background: var(--text-dark);
            color: white;
            text-align: center;
        }

        .footer-logo {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .footer-logo span {
            color: var(--bps-orange);
        }

        .copyright {
            color: #94a3b8;
            font-size: 0.875rem;
        }

        /* Mobile */
        @media (max-width: 768px) {
            h1 {
                font-size: 2.5rem;
            }

            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <nav>
        <div class="container">
            <div class="logo">
                <img src="https://demakkab.bps.go.id/static/img/logo.png" alt="BPS" height="40">
                Manajemen<span>Surat</span>
            </div>
            <a href="/admin" class="btn btn-primary">Login Dashboard</a>
        </div>
    </nav>

    <header>
        <div class="container">
            <div class="hero-badge">Internal App BPS Kabupaten Demak</div>
            <h1>Sistem Administrasi Surat <span>Terintegrasi</span></h1>
            <p class="hero-desc">Solusi cerdas untuk manajemen surat-menyurat, digitalisasi berkas, dan sistem disposisi
                dalam satu platform modern.</p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="/admin" class="btn btn-primary">Mulai Sekarang</a>
                <a href="#features" class="btn btn-outline">Lihat Fitur</a>
            </div>
        </div>
    </header>

    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Layanan Kami</h2>
                <p>Digitalisasi birokrasi untuk efisiensi kerja yang maksimal.</p>
            </div>
            <div class="grid">
                <div class="card">
                    <span class="tag tag-active">Aktif</span>
                    <div class="card-icon">📄</div>
                    <h3>Generate Surat</h3>
                    <p>Pembuatan SK dan Surat Keluar otomatis dengan template Word yang dinamis dan penomoran otomatis.
                    </p>
                </div>

                <div class="card">
                    <span class="tag tag-soon">Segera</span>
                    <div class="card-icon">📥</div>
                    <h3>Surat Masuk</h3>
                    <p>Pencatatan surat masuk secara digital dengan fitur upload scan dokumen untuk arsip yang rapi.</p>
                </div>

                <div class="card">
                    <span class="tag tag-soon">Segera</span>
                    <div class="card-icon">✍️</div>
                    <h3>Disposisi Digital</h3>
                    <p>Alur disposisi berjenjang dari pimpinan ke staf secara real-time tanpa perlu berkas fisik.</p>
                </div>

                <div class="card">
                    <span class="tag tag-soon">Segera</span>
                    <div class="card-icon">💬</div>
                    <h3>WA Notification</h3>
                    <p>Notifikasi otomatis melalui WhatsApp untuk setiap surat baru atau instruksi disposisi.</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-logo">BPS<span>Demak</span></div>
            <p class="copyright">&copy; 2025 BPS Kabupaten Demak. All rights reserved.</p>
        </div>
    </footer>

</body>

</html>