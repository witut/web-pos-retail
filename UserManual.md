# 📖 Buku Panduan Kasir (User Manual)
**Web-POS Retail**

Selamat datang di aplikasi POS Retail. Panduan ini dirancang untuk membantu Anda (Kasir) memahami alur kerja harian, mulai dari membuka toko, melayani pelanggan, hingga menutup toko di akhir hari.

---

## 1. Memulai Hari (Buka Shift)
Sebelum Anda bisa melayani pelanggan atau menggunakan mesin kasir, Anda **wajib** membuka shift (sesi kasir).
1. Login menggunakan akun kasir Anda.
2. Jika Anda belum membuka shift, layar akan otomatis memunculkan peringatan **"Buka Register"**.
3. Masukkan **Modal Awal (Uang Laci)** yang Anda bawa/terima pagi itu.
4. Klik **Simpan/Buka**. Sekarang Anda siap melayani pelanggan!

---

## 2. Melayani Pelanggan (Transaksi POS)
Di halaman Kasir (POS):
1. **Cari Barang**: Gunakan fitur pencarian atau *scan barcode* untuk memasukkan barang ke keranjang (*cart*).
2. **Ubah Qty**: Klik angka qty pada item untuk menambah atau mengurangi jumlah barang.
3. **Diskon & Kupon**: 
   - Sistem akan *otomatis* menerapkan diskon/promo (seperti Diskon %, Diskon Nominal, atau Promo Beli X Gratis Y) jika barang memenuhi syarat.
   - Jika pelanggan membawa **Kupon**, klik tombol **"Kupon"** dan masukkan kode tersebut.
4. **Pembayaran (Checkout)**:
   - Klik tombol **Bayar (F9)**.
   - Masukkan nominal uang yang diterima dari pelanggan.
   - Sistem akan otomatis menghitung uang kembalian.
   - Klik **Simpan & Cetak**. Transaksi akan tersimpan dan struk akan otomatis tercetak di mesin printer.

---

## 3. Fitur Tahan Transaksi (Hold)
Jika pelanggan tiba-tiba ingin mengambil barang tambahan saat Anda sedang me-scan, Anda bisa "menahan" (hold) transaksinya agar antrean berikutnya bisa dilayani:
1. Di layar kasir, tekan tombol **Hold (F8)**.
2. Keranjang akan dikosongkan secara otomatis dan transaksi disimpan sementara.
3. Anda bisa melayani pelanggan berikutnya.
4. Saat pelanggan pertama kembali, klik ikon/tombol **Daftar Hold**.
5. Pilih transaksi yang ditahan, lalu klik **Resume/Lanjutkan** untuk memasukkannya kembali ke keranjang dan melanjutkan pembayaran.

---

## 4. Cetak Struk (Printer Error/Manual)
Struk akan otomatis tercetak dari *Print Server* yang terhubung ke sistem operasi Anda. Jika terjadi gagal cetak:
1. Pastikan program `node server.js`  (Print Server) sudah berjalan di komputer / laptop kasir.
2. Jika menggunakan **Windows**: Pastikan *PowerShell script* `send_raw_to_printer.ps1` berfungsi normal dan printer sudah di-*share* secara benar di sistem Windows Anda.
3. Untuk mencetak ulang struk lama: 
   - Masuk ke menu **Riwayat Transaksi**.
   - Cari transaksi yang ingin dicetak.
   - Klik ikon/tombol **Cetak Struk**. Struk akan terkirim kembali secara *direct proxy* langsung ke printer tanpa memunculkan dialog PDF/Browser!

---

## 5. Meretur Barang (Refund)
Jika pelanggan mengembalikan barang:
1. Masuk ke halaman **Riwayat Transaksi**.
2. Cari Invoice/Transaksi sebelumnya, lalu klik tombol **Retur**.
3. Pada halaman Retur, pilih barang mana yang akan dikembalikan dan isi **Qty Retur**.
4. Pilih **Kondisi Barang** (Bagus / Rusak) karena ini akan menentukan apakah barang dimasukkan kembali ke stok etalase atau dibuang (deadstock).
5. Pilih **Metode Pengembalian Uang** (Tunai / Saldo/ Non-Tunai) dan isi **Alasan Retur**.
6. Simpan. Uang kas laci Anda akan otomatis dikurangi jika Anda memilih pengembalian Tunai.

---

## 6. Mengakhiri Hari (Tutup Shift & Z-Report)
Setelah toko tutup atau jam kerja Anda habis, Anda wajib menutup shift:
1. Pergi ke widget Profil/Shift Anda atau klik menu **Tutup Register/Shift**.
2. Masukkan rincian hitungan **Uang Tunai Aktual** yang saat ini berada di laci/brankas kasir Anda.
3. Sistem akan membandingkannya dengan peredaran kas (Modal Awal + Penjualan Tunai - Retur Tunai) untuk mendeteksi selisih (Minus/Plus).
4. Klik **Tutup Register**.
5. Sistem akan menerbitkan **Z-Report** (Laporan ringkasan penjualan, pergerakan kas, dan pajak harian Anda).

*Terima kasih telah menggunakan Web-POS Retail. Jika menemui error terkait jaringan lokal printer, segera hubungi Teknisi / Admin toko Anda.*
