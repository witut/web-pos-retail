const express = require('express');
const cors = require('cors');
const escpos = require('escpos');
const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');
const os = require('os');

const app = express();
const port = 9100;

// ==========================================
// PENGATURAN PRINTER (Sistem Operasi)
// ==========================================
// Ganti nama ini dengan nama printer Anda di CUPS (Linux) atau Share Name (Windows)
const PRINTER_NAME = 'POS58 Printer(2)';

// Membuat Custom Adapter untuk Sistem Operasi (CUPS Linux / Windows Spooler)
// Alih-alih menulis ke raw USB Port (yang butuh permission khusus/Zadig),
// kita membuat buffer ESC/POS lalu menyerahkannya ke Print Spooler bawaan OS.
class OsPrinterAdapter {
    constructor(printerName) {
        this.printerName = printerName;
        this.buffer = Buffer.alloc(0);
    }
    open(callback) { if (callback) callback(); }

    write(data, callback) {
        this.buffer = Buffer.concat([this.buffer, Buffer.from(data)]);
        if (callback) callback();
    }

    close(callback) {
        const tempPath = path.join(__dirname, 'temp_receipt.bin');
        fs.writeFileSync(tempPath, this.buffer);

        let command = '';
        if (os.platform() === 'win32') {
            // Untuk Windows: gunakan PowerShell helper yang mengirim byte RAW langsung ke printer
            // Tidak perlu melakukan printer sharing; printerName harus sesuai dengan nama printer di Windows
            const psScript = path.join(__dirname, 'send_raw_to_printer.ps1');
            command = `powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -File "${psScript}" -PrinterName "${this.printerName}" -FilePath "${tempPath}"`;
        } else {
            // Untuk Linux Development (CUPS):
            command = `lp -d "${this.printerName}" -o raw "${tempPath}"`;
        }

        console.log(`Menjalankan perintah print: ${command}`);
        exec(command, (error, stdout, stderr) => {
            if (error) {
                console.error(`Gagal mencetak: ${error.message}`);
            }
            // Hapus file temp setelah dikirim
            if (fs.existsSync(tempPath)) fs.unlinkSync(tempPath);
            if (callback) callback(error);
        });
    }
}


// Middleware
app.use(cors());
app.use(express.json());

// Main Print Endpoint
app.post('/print', (req, res) => {
    const data = req.body;

    if (!data || !data.receipt) {
        return res.status(400).json({ success: false, error: 'Invalid payload' });
    }

    try {
        // Gunakan adapter OS kita sendiri
        const device = new OsPrinterAdapter(PRINTER_NAME);

        // Disable encoding initialization to prevent garbage characters at the top of the receipt
        // CUPS might be interpreting the init bytes as printable characters
        const options = { encoding: "ascii" };
        const printer = new escpos.Printer(device, options);

        device.open(function (error) {
            if (error) {
                console.error("Error opening device:", error);
                return res.status(500).json({ success: false, error: 'Gagal membuka device spooler' });
            }

            const paperWidth = String(data.settings?.paper_width || '58');
            const is58mm = paperWidth === '58';
            // Lebar karakter maksimal per baris (58mm = 32 char, 80mm = 48 char)
            const lineWidth = is58mm ? 32 : 48;

            // Fungsi pembantu untuk membuat teks rata Kiri & Kanan dalam satu baris
            const formatLine = (leftText, rightText) => {
                const strL = String(leftText);
                const strR = String(rightText);
                const spaces = lineWidth - strL.length - strR.length;
                if (spaces > 0) return strL + ' '.repeat(spaces) + strR;
                return strL + ' ' + strR; // Fallback jika teks terlalu panjang
            };

            // Inisialisasi printer untuk keamanan (membersihkan garbage bytes sebelumnya)
            printer.hardware('INIT');

            // ================= Header =================
            // Nama Toko
            printer.align('ct');
            // Menghapus .size(1, 1) agar font kembali ke ukuran normal (tapi tetap tebal/bold)
            printer.font('a').style('b').text(data.store.name).style('normal');
            printer.text(data.store.address);
            if (data.store.phone) printer.text(data.store.phone);
            printer.text('================================'); // Khusus 58mm = 32 karakter

            if (data.receipt.header) {
                printer.text(data.receipt.header).text('-'.repeat(lineWidth));
            }

            // ================= Info =================
            printer.align('lt');
            printer.text(`Invoice: ${data.receipt.invoice_number}`);
            printer.text(`Kasir  : ${data.receipt.cashier}`);
            printer.text(`Tanggal: ${data.receipt.date}`);

            if (data.receipt.customer) {
                printer.text(`Pggn    : ${data.receipt.customer}`);
            }

            printer.text('-'.repeat(lineWidth));

            // ================= Items =================
            let totalQty = 0;
            data.items.forEach(item => {
                printer.text(item.name);

                // Asumsi payload Laravel dari `item.qty` atau `item.quantity`
                const qtyVal = item.qty || 1;
                totalQty += qtyVal;

                const priceStr = item.price.toLocaleString('id-ID');
                const subtotalStr = item.subtotal.toLocaleString('id-ID');

                // Format "1 x 12.000                  12.000"
                let lineStr = `${qtyVal} x ${priceStr}`;
                printer.text(formatLine(lineStr, subtotalStr));

                // Format diskon "diskon                    (-1.200)"
                if (item.discount > 0) {
                    printer.text(formatLine('diskon', `(-${item.discount.toLocaleString('id-ID')})`));
                }
            });

            printer.text('-'.repeat(lineWidth));

            // ================= Totals =================
            printer.align('lt');
            printer.text(formatLine('Subtotal', data.totals.subtotal.toLocaleString('id-ID')));

            if (data.totals.global_discount > 0) {
                printer.text(formatLine('Diskon total', `-${data.totals.global_discount.toLocaleString('id-ID')}`));
            }

            printer.text(formatLine('Total', data.totals.grand_total.toLocaleString('id-ID')));

            printer.text('-'.repeat(lineWidth));

            // ================= Payment =================
            printer.text('Tunai/Bayar');
            let paymentMethod = data.payment.method;
            if (paymentMethod === 'TUNAI' || paymentMethod === 'CASH') paymentMethod = 'Cash';
            printer.text(formatLine(paymentMethod, data.payment.amount_paid.toLocaleString('id-ID')));
            printer.text(formatLine('Kembali', data.payment.change.toLocaleString('id-ID')));

            // PPN ditambahkan di bagian bawah sebagai keterangan
            if (data.totals.tax > 0) {
                const roundedTax = Math.round(data.totals.tax);
                printer.font('b'); // Menggunakan font B yang lebih kecil jika printer support (biasanya 58mm tidak terlalu pengaruh, namun bisa dicoba)
                const taxLine = `Incl. PPN : ${roundedTax.toLocaleString('id-ID')}`;

                // Jika ingin dipaksa lebih kecil secara text bisa dibiarkan standar namun di align
                printer.text(taxLine);
                printer.font('a'); // Kembali ke normal
            }

            printer.text('-'.repeat(lineWidth));

            // ================= Footer =================
            printer.align('ct');
            printer.text(data.receipt.footer || 'Terima kasih atas kunjungan Anda');
            printer.text('================================');
            printer.feed(3);

            // ================= Perintah Lanjutan =================
            if (data.settings?.auto_cut) {
                printer.cut();
            } else {
                printer.feed(4); // Jika printer manual (tanpa pisau), tambah spasi robek ekstra
            }

            if (data.settings?.open_drawer) {
                printer.cashdraw(2);
            }

            printer.close();
            res.json({ success: true, message: 'Print OK' });
        });

    } catch (e) {
        console.error("Print Error:", e);
        res.status(500).json({ success: false, error: e.message });
    }
});

// Start the server
app.listen(port, () => {
    console.log(`Print Server berjalan di http://localhost:${port}`);
    console.log(`Menunggu printer USB terhubung...`);
});
