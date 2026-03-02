# POS Print Server (ESC/POS)

Ini adalah API Server sederhana berbasis Node.js untuk menjembatani aplikasi web POS Retail dengan printer thermal USB lokal menggunakan protokol ESC/POS. File telah dimodifikasi untuk menggunakan CUPS/Spooler OS demi menghindari masalah permissions raw USB di Linux.

## Prasyarat
- Node.js terinstal
- Printer thermal USB terhubung (disarankan Epson TM-series, XPrinter).

## Instalasi
```bash
npm install
```

## Cara Menyiapkan Printer di Linux (Development)
Karena server ini menggunakan `lp` untuk Linux, pastikan printer Anda telah ditambahkan di **CUPS** (bisa melalui `http://localhost:631/`).
Buka `server.js` dan ubah `PRINTER_NAME` sesuai dengan nama Queue (misal: `xantri-58`).

## Cara Menyiapkan Printer di Windows (Production)
Pastikan printer telah terinstall di Windows dan opsi **Share/Berbagi** telah diaktifkan dengan share name yang sama dengan isi variabel `PRINTER_NAME` di dalam `server.js` (misal: `xantri-58`).

## Menjalankan Server
Buka terminal di folder ini dan jalankan:
```bash
node server.js
```
Aplikasi POS (backend Laravel) akan mengirim payload ke `POST http://localhost:9100/print`.
