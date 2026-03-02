# Panduan Lengkap Instalasi & Konfigurasi Direct Printing System (ESC/POS)

Fitur Direct Printing (ESC/POS) di aplikasi POS Retail ini memungkinkan Anda mencetak struk kasir langsung ke printer thermal tanpa perlu melewati dialog `window.print()` dari browser. Proses pencetakan menjadi jauh lebih cepat dan mulus.

Untuk menggunakan fitur ini, aplikasi web POS membutuhkan sebuah **Print Server Lokal** yang berjalan di komputer/device tempat printer fisik tersambung.

Berikut adalah langkah-langkah detail untuk mempersiapkan dan mengkonfigurasi Direct Printing System.

## Tahap 1: Persiapan Perangkat Keras (Hardware) & Driver
1. **Sambungkan Printer Thermal:**
   Pastikan printer thermal Anda (seperti Epson TM-series, Xprinter, Matrix Point, dll) sudah menyala dan tersambung ke komputer (via USB, LAN, atau Bluetooth).
2. **Install Driver Printer:**
   Pastikan Anda sudah menginstal driver resmi bawaan printer tersebut di sistem operasi PC Kasir Anda (Windows/Linux/Mac). Ujicoba dengan "Print Test Page" standar OS untuk memastikan printer sudah merespon.

## Tahap 2: Menyiapkan Print Server Lokal
Aplikasi web tidak bisa langsung "berbicara" dengan port USB atau jaringan lokal (karena limitasi keamanan Browser/CORS). Oleh karena itu, kita membutuhkan jembatan berupa **REST API Print Server** (seperti `mike42/escpos-php` yang dibungkus micro-framework, atau aplikasi Node.js, atau aplikasi *desktop wrapper* seperti QZ Tray).

Salah satu implementasi Print Server yang ringan dan direkomendasikan adalah menggunakan berbasis Python atau Node.js yang menerima POST Request dan meneruskannya ke ESC/POS.

### Opsi A: Menggunakan QZ Tray (Rekomendasi - Mudah & Stabil)
QZ Tray adalah software populer untuk menjembatani browser dengan hardware lokal (printer, timbangan).
*(Catatan: Aplikasi POS kita saat ini didesain menggunakan REST endpoint standar seperti `http://localhost:9100/print`. Jika Anda menggunakan QZ Tray, pastikan endpoint dan payload disesuaikan dengan konektor QZ).*

### Opsi B: Menggunakan Node.js ESC/POS API Server (Standard POS Retail)
Sistem POS kita sudah terkonfigurasi untuk menembak endpoint HTTP POST. Anda bisa membuat Print Server sederhana menggunakan Node.js:

1. **Install Node.js:** Unduh dan install Node.js dari [nodejs.org](https://nodejs.org/).
2. **Buat Folder Print Server:**
   Buat folder baru di komputer kasir, misalnya `C:\pos-print-server`.
3. **Inisialisasi Project Node:**
   Buka terminal/command prompt di folder tersebut, lalu jalankan:
   ```bash
   npm init -y
   npm install express escpos escpos-usb cors
   ```
4. **Buat file `server.js`:**
   Buat file `server.js` dan isi dengan script REST API sederhana untuk menerima payload JSON dari POS kita dan menembakkannya ke modul `escpos`. *(Anda dapat mencari referensi standard `escpos-usb`)*.
5. **Jalankan Print Server:**
   ```bash
   node server.js
   ```
   *Pastikan server berjalan pada port yang Anda inginkan, misal Port `9100`.*

## Tahap 3: Konfigurasi di Panel Admin POS Retail
Setelah Print Server Anda berjalan (misalnya aktif di `http://localhost:9100`), lakukan konfigurasi di aplikasi POS:

1. Login sebagai **Administrator** di aplikasi POS Retail.
2. Buka menu **Pengaturan** (Settings).
3. Pindah ke tab **Printer**.
4. Isi pengaturan berikut:
   * **Tipe Printer:** Pilih **ESC/POS (Direct Print via Print Server)**
   * **URL Print Server:** Masukkan URL tempat Print Server Anda berjalan. Contoh: `http://localhost:9100` atau `http://127.0.0.1:631` (sesuaikan dengan port aplikasi print server Anda).
   * **Ukuran Kertas:** Pilih ukuran kertas thermal Anda (58mm atau 80mm).
   * **Fitur Printer:** Centang *Auto-cut* dan *Open Drawer* jika printer Anda mendukung fitur tersebut.
5. Klik tombol **Simpan Pengaturan**.

## Tahap 4: Pengujian (Testing)
1. Login sebagai **Kasir** (atau masuk ke menu POS Terminal dari Admin).
2. Lakukan transaksi pembelian seperti biasa hingga tahap pembayaran.
3. Selesaikan pembayaran. Saat status **"Pembayaran Berhasil"** muncul, sistem akan otomatis mencetak struk (jika browser tidak diblokir popup/focus script).
4. Anda juga bisa menekan tombol **Cetak Struk**. Struk akan langsung keluar dari printer thermal tanpa memunculkan dialog Print Browser.
5. Jika printer tidak merespon:
   * Cek kembali jendela Command Prompt/Terminal dari **Print Server Lokal** Anda, apakah ada error yang masuk (seperti port USB tidak ditemukan).
   * Cek Network Inspector di Browser (F12 -> Network) saat Anda mengklik "Cetak Struk" untuk memastikan request AJAX `/pos/transactions/{id}/print-proxy` berhasil mengembalikan status 200 (OK).

---

### Troubleshooting Umum

* **Gagal menghubungi Print Server (Error 500 dari proxy):**
  Artinya backend Laravel POS kita gagal mencoba mengirim request cURL/HTTP ke URL Print Server (`http://localhost:9100`). Kemungkinan Print Server mati, port salah diisi, atau diblokir Firewall. Pastikan URL Print Server di pengaturan benar.
* **Teks Terpotong atau Berantakan:**
  Pastikan ukuran kertas (Karakter Per Baris) yang dikirim dari payload POS cocok dengan tipe printer (58mm biasanya menampung ~32 karakter, 80mm menampung ~48 karakter).
* **Cash Drawer tidak terbuka:**
  Pastikan printer Anda dihubungkan menggunakan port RJ11 ke laci kasir, dan fitur *Open Drawer* diatur aktif pada panel Admin.
