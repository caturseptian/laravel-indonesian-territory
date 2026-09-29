@php($metadata = json_decode(file_get_contents(database_path('data/metadata.json')), true))
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Kode dan data wilayah administrasi Indonesia untuk Laravel: provinsi, kabupaten/kota, kecamatan, kelurahan/desa, dan kode pos sesuai Kepmendagri 300.2.2-2138 Tahun 2025.">

        <title>{{ config('app.name') }}</title>

        <style>
            :root { color-scheme: light dark; --fg: #1b1b18; --muted: #706f6c; --bg: #fdfdfc; --line: #e3e3e0; }
            @media (prefers-color-scheme: dark) { :root { --fg: #ededec; --muted: #a1a09a; --bg: #0a0a0a; --line: #3e3e3a; } }
            body { margin: 0; padding: 2rem 1rem; background: var(--bg); color: var(--fg); font: 16px/1.6 ui-sans-serif, system-ui, sans-serif; }
            main { max-width: 44rem; margin: 0 auto; }
            h1 { font-size: 1.5rem; margin: 0 0 .25rem; }
            p { color: var(--muted); margin: 0 0 1.5rem; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; }
            th, td { text-align: left; padding: .4rem .5rem; border-bottom: 1px solid var(--line); }
            td.number { text-align: right; font-variant-numeric: tabular-nums; }
            code { font: .875rem ui-monospace, monospace; }
        </style>
    </head>
    <body>
        <main>
            <h1>Data Wilayah Indonesia</h1>
            <p>Kepmendagri {{ $metadata['base_document'] }}, berlaku {{ $metadata['effective_date'] }}.</p>

            <table>
                <tr><th>Provinsi</th><td class="number">{{ number_format($metadata['counts']['provinces'], 0, ',', '.') }}</td></tr>
                <tr><th>Kabupaten/kota</th><td class="number">{{ number_format($metadata['counts']['regencies'], 0, ',', '.') }}</td></tr>
                <tr><th>Kecamatan</th><td class="number">{{ number_format($metadata['counts']['districts'], 0, ',', '.') }}</td></tr>
                <tr><th>Kelurahan/desa</th><td class="number">{{ number_format($metadata['counts']['villages'], 0, ',', '.') }}</td></tr>
            </table>

            <table>
                <tr><td><code>GET /api/provinces</code></td></tr>
                <tr><td><code>GET /api/provinces/31/regencies</code></td></tr>
                <tr><td><code>GET /api/regencies/31.71/districts</code></td></tr>
                <tr><td><code>GET /api/districts/31.71.01/villages</code></td></tr>
                <tr><td><code>GET /api/villages/31.71.01.1001</code></td></tr>
                <tr><td><code>GET /api/villages?q=gambir</code> · <code>GET /api/villages?postal_code=10110</code></td></tr>
            </table>
        </main>
    </body>
</html>
