# Week 24: Manual Testing Scenarios

Berikut adalah daftar skenario pengujian komprehensif yang harus dijalankan untuk memastikan stabilitas fitur-fitur baru (Promosi, Manajemen Shift, dan Retur Barang). Centang kotak (`[ ]`) jika pengujian berhasil.

## 1. Pengujian Promosi & Diskon (Edge Cases)

Skenario ini memastikan perhitungan otomatis dan manual tidak saling tindih atau menyebabkan total tagihan menjadi minus.

- [x] **Skenario A (Promo Persentase + Kupon)**
  - **Langkah:** Masukkan barang yang sedang diskon otomatis (misalnya 10%). Setelah itu, terapkan *Kupon Global* pada keranjang tersebut.
  - **Ekspektasi:** Total tagihan berkurang dua kali (dari promo barang, lalu dari kupon). Harga tagihan bersih (Net Total) tidak boleh menjadi angka minus (di bawah Rp 0). Pajak dihitung dengan benar dari subtotal bersih.
- [x] **Skenario B (Tukar Poin + Promo)**
  - **Langkah:** Masukkan produk yang sedang promo ke keranjang. Pilih pelanggan terdaftar. Lakukan *Redeem/Tukar* semua poin pelanggan tersebut hingga nilainya menutupi (atau nyaris menutupi) seluruh tagihan.
  - **Ekspektasi:** Sistem tidak boleh *crash*. Kolom "Change/Kembalian" harus menunjukkan angka yang wajar (tidak minus secara tidak logis). Total pembayaran sesuai dengan sisa tagihan dikurangi poin.
- [x] **Skenario C (Buy X Get Y)**
  - **Langkah:** Siapkan promo beli 2 gratis 1 untuk produk A. Masukkan 1 qty produk A ke keranjang.
  - **Ekspektasi:** Harga normal.
  - **Langkah Lanjutan:** Ubah kuantitas produk A menjadi 2.
  - **Ekspektasi:** Sistem otomatis menambah 1 item produk A (gratis) ke keranjang, atau memotong harga setara 1 produk A.
  - **Langkah Akhir:** Turunkan kembali kuantitas produk A menjadi 1.
  - **Ekspektasi:** Item promo gratis otomatis terhapus atau diskon memudar kembali ke harga normal.

## 2. Pengujian Manajemen Shift (Buka / Tutup Laci Kasir)

Skenario ini memastikan riwayat modal awal, penjualan, dan penutupan sinkron dan wajib dilakukan.

- [x] **Skenario A (Akses Tanpa Shift Aktif)**
  - **Langkah:** Buka *browser incognito* (atau logout lalu login kembali). Login sebagai Kasir (yang belum membuka shift). Coba akses halaman POS (Poin Penjualan).
  - **Ekspektasi:** Sistem SEHARUSNYA mencegat akses tersebut dengan modal peringatan yang mewajibkan kasir memasukkan "Modal Awal" (Buka Kasir) sebelum bisa melakukan scan atau input barang apapun.
- [x] **Skenario B (Sisa Uang Fisik Tidak Sesuai)**
  - **Langkah:** Mulai sesi shift baru dengan modal awal (misal Rp 100.000). Lakukan 1-2 transaksi penjualan (misal senilai total Rp 50.000 secara Tunai).
  - **Ekspektasi Sistem:** Laci kasir seharusnya memiliki Rp 150.000.
  - **Langkah Lanjutan:** Lakukan *Close Register* (Tutup Shift). Pada isian "Physical Cash", masukkan angka Rp 130.000 (sengaja kurang Rp 20.000).
  - **Ekspektasi Akhir:** Sistem berhasil menutup shift, mencatat laporan Z, dan menyorot adanya "Variance" (Selisih Kurang) sebesar -Rp 20.000 di dalam database dan laporan Admin.

## 3. Pengujian Retur Barang (Product Returns & Void)

Skenario ini krusial untuk memastikan uang kembali dan ketersediaan stok fisik tetap sinkron.

- [ ] **Skenario A (Retur Barang Berkondisi Bagus)**
  - **Langkah:** Cari data transaksi yang sudah berstatus *Completed*. Lakukan proses "Retur" untuk 1 buah barang (misalkan Sabun) dengan mencentang alasan "Salah beli" dan memilih kondisi barang "Bagus/Good".
  - **Ekspektasi:** Proses refund sukses tercatat. Di halaman *Laporan Stok / Produk*, stok produk Sabun tersebut HARUS bertambah kembali 1 pcs ke etalase.
- [ ] **Skenario B (Retur Barang Kondisi Rusak/Damaged)**
  - **Langkah:** Lakukan retur untuk 1 barang lagi dari transaksi yang sama (atau beda), namun kali ini pilih kondisi "Rusak/Damaged".
  - **Ekspektasi:** Sistem menyetujui *refund* uang kepada pelanggan, TETAPI stok barang tersebut tetap TIDAK bertambah di daftar stok etalase (karena otomatis terbaca sebagai *Dead Stock* atau langsung di-*write-off*).
- [ ] **Skenario C (Proses Void Keseluruhan)**
  - **Langkah:** Buat 1 transaksi penjualan baru secara utuh hingga tahap cetak struk. Kemudian dari menu Riwayat, pilih transaksi tersebut dan perintahkan aksi "Void". (Masukkan PIN Anda sebagai otoritas Admin).
  - **Ekspektasi:** Transaksi berubah status menjadi *Void*. Uang pembelian dianggap batal, dan yang terpenting: *Semua* kuantitas barang yang ada di struk tersebut kembali utuh 100% ke dalam total stok produk toko.
