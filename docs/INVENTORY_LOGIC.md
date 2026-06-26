# Dokumentasi Logika Bisnis & Standarisasi Inventori

Aplikasi Promise Inventory - Update Terakhir: 16 April 2026

Dokumen ini berfungsi sebagai panduan teknis dan operasional mengenai seluruh logika perhitungan, validasi, dan alur data dalam sistem Promise Inventory.

---

## Daftar Isi

1. [Konfigurasi Dasar dan Dimensi](#1-konfigurasi-dasar-dan-dimensi)
2. [Standarisasi Perhitungan PCS](#2-standarisasi-perhitungan-pcs)
3. [Valuasi Nilai Stok](#3-valuasi-nilai-stok)
4. [Manajemen Transaksi dan Pergerakan Stok](#4-manajemen-transaksi-dan-pergerakan-stok)
5. [Analisis VAVE Engineering vs Production](#5-analisis-vave-engineering-vs-production)
6. [Stock Opname Rekonsiliasi Data](#6-stock-opname-rekonsiliasi-data)
7. [Integritas Data dan Import Excel](#7-integritas-data-dan-import-excel)
8. [Referensi Teknis Database](#8-referensi-teknis-database)
9. [Contoh Kasus Operasional](#9-contoh-kasus-operasional)

---

## 1. Konfigurasi Dasar dan Dimensi

Sistem menghitung atribut produk secara otomatis berdasarkan input dimensi di Master Data.

### A. Perhitungan Berat (Weight KG)

Dihitung per satuan (per piece atau per pitch) menggunakan density baja standar (**7.85**).

> **Rumus Umum**: `(Tebal * Lebar * Panjang * Density) / 1.000.000`

| Jenis Unit | Spesifikasi Rumus |
| :--- | :--- |
| **SHEET** | `((thickness * width * length * density) / 1.000.000) / pcs_per_unit` |
| **COIL** | `((thickness * width * pitch * density) / 1.000.000) / pcs_per_pitch` |
| **TRAPEZOID** | `((thickness * width * ((length + length_2) / 2) * density) / 1.000.000) / pcs_per_unit` |

### B. Minimum Stock (Safety Buffer)

Menentukan batas aman stok sebelum sistem memicu peringatan (Stock Alert).

* **Rumus**: `unit_per_car * 90`
* **Logika**: Stok minimal harus cukup untuk memenuhi kebutuhan produksi 90 kendaraan.

---

## 2. Standarisasi Perhitungan PCS

Digunakan untuk mengonversi berat (KG) menjadi jumlah barang (PCS) agar konsisten di Dashboard, Monitoring, dan Export.

### A. Material COIL (Metode Rasio dan Estimasi Yield)

Untuk Coil, sistem menggunakan dua tahap perhitungan:

#### 1. Estimasi Target (Saat Master Data)

Sebelum transaksi, sistem menghitung berapa PCS yang **seharusnya** dihasilkan oleh satu coil (estimasi yield).

1. **Berat Per mm**: `(thickness * width * 1 * density) / 1.000.000`
2. **Total Scrap Weight**: `(top_coil + end_coil) * Berat_Per_mm`
3. **Net Weight**: `gross_coil - Total_Scrap_Weight`
4. **Estimasi PCS**: `floor(Net_Weight / weight_kg) * pcs_per_pitch`

*Hasil ini disimpan ke dalam kolom `pcs_per_unit`.*

#### 2. Konversi Berat ke PCS (Aktual)

Meskipun rumusnya identik, kolom yang digunakan berbeda tergantung di mana angka tersebut ditampilkan:

* **Untuk Saldo Stok (Balance Monitoring)**:
  Digunakan untuk melihat sisa barang di gudang.
  > **Bahasa Sistem**: `floor((current_stock_qty / gross_coil) * pcs_per_unit)`
* **Untuk Transaksi (In/Out/History)**:
  Digunakan saat membuat nota/transaksi baru atau melihat riwayat.
  > **Bahasa Sistem**: `floor((qty / gross_coil) * pcs_per_unit)`

### B. Material Non-COIL (Metode Multiplikasi)

Unit non-coil menggunakan perkalian langsung karena inputnya biasanya adalah jumlah unit (lembar, box, atau paket).

* **Konteks Saldo**: `floor(current_stock_qty * pcs_per_unit)`
* **Konteks Transaksi**: `floor(qty * pcs_per_unit)`

---

## 3. Valuasi Nilai Stok (Stock Value)

Sistem menghitung nilai moneter (Rupiah) aset dengan prinsip dasar **Biaya per KG**.

* **Prinsip Utama**: `Total Nilai = Total Berat (KG) * Harga per KG`
* **Kolom Harga**: `material_price` (Price per KG).

### Logika Perhitungan

| Kondisi | Rumus Valuasi (Rp) |
| :--- | :--- |
| **Material COIL** | `qty * material_price` (Karena Qty Coil sudah dalam KG) |
| **Material Non-COIL** | `(qty * pcs_per_unit * weight_kg) * material_price` |

> **Keterangan**: Untuk unit non-coil, sistem harus mengonversi jumlah pak/sheet menjadi total berat (KG) terlebih dahulu sebelum dikalikan dengan `material_price`.

---

## 4. Manajemen Transaksi dan Pergerakan Stok

Mengatur bagaimana data stok berubah dan divalidasi saat ada aktivitas di gudang.

### A. Validasi Ketat (Strict Validation)

Untuk menjamin akurasi, transaksi produk **COIL** akan **DITOLAK** jika data teknis berikut di Master Data bernilai 0:

* `gross_coil`, `top_coil`, `end_coil`, dan `pitch`.

### B. Pergerakan Stok (Stock Effect)

Setiap kategori transaksi memiliki efek arah stok:

* **IN (Masuk)**: Effect `+1`.
* **OUT (PP/Trial/Event)**: Effect `-1`.
* **Rumus Update**: `current_stock_qty(Baru) = current_stock_qty(Lama) + (Input_Qty * Category_Effect)`

---

## 5. Analisis VAVE Engineering vs Production

Membandingkan data desain (**Baseline/EBD**) dengan kenyataan produksi (**Actual**) untuk audit penghematan material.

### A. Perhitungan Berat Baseline (EBD Weight)

Khusus pada modul VAVE, berat baseline dihitung dengan menyertakan faktor jumlah keping per unit/pitch untuk mendapatkan berat per part yang akurat.

| Jenis Unit | Rumus Berat EBD (Baseline) |
| :--- | :--- |
| **SHEET** | `((t * w * l * density) / 1.000.000) / pcs_per_unit` |
| **COIL** | `((t * w * pitch * density) / 1.000.000) / pcs_per_pitch` |
| **TRAPEZOID** | `((t * w * ((l1 + l2) / 2) * density) / 1.000.000) / pcs_per_unit` |

### B. Status Efisiensi (Merit/Loss)

* **MERIT**: Jika `Baseline_Weight > Actual_Weight` (Terjadi penghematan).
* **LOSS**: Jika `Baseline_Weight < Actual_Weight` (Terjadi pemborosan).

### C. Budomari (Yield %)

Menampilkan persentasi pemakaian material yang menjadi barang jadi.

* **Rumus**: `(net_weight / weight_kg) * 100`

---

## 6. Stock Opname Rekonsiliasi Data

Proses penyesuaian antara data komputer dengan hitung fisik dilapangan.

* **Variance Qty**: `Qty_Real_Fisik - current_stock_qty`
* **Variance PCS**: Selisih berat yang dikonversi ke PCS menggunakan [Metode Rasio](#2-standarisasi-perhitungan-pcs).
* **Variance Amount**: `Variance_Qty * material_price` (Nilai kerugian/keuntungan dalam Rupiah).

---

## 7. Integritas Data dan Import Excel

Sistem mempermudah pembaruan data massal melalui fitur Import.

* **Logika Upsert**: Sistem mencari kombinasi `Part_No + Model + Revision`. Jika ketemu, data di-**Update**; jika tidak, data baru di-**Insert**.
* **Ranking Revisi**: Dalam laporan VAVE, sistem menentukan versi "Terbaru" berdasarkan nilai **`sort_order`** tertinggi pada Master Revisi (bukan berdasarkan tanggal input).

---

## 8. Referensi Teknis Database

Berikut adalah pemetaan variabel logika ke nama kolom asli di tabel `inv_t_product_detail`:

| Variabel Logika | Kolom Database |
| :--- | :--- |
| **Qty Sekarang** | `current_stock_qty` |
| **Berat Full Coil** | `gross_coil` |
| **Isi Per Unit** | `pcs_per_unit` |
| **Isi Per Pitch** | `pcs_per_pitch` |
| **Dimensi** | `thickness`, `width`, `length`, `length_2`, `pitch` |
| **Harga** | `material_price` |

---

## 9. Contoh Kasus Operasional

### Kasus 1: Material COIL (Pcs/Kg)

* **Master**: `gross_coil` = 300, `pcs_per_unit` = 282.
* **Transaksi**: Input Out 100 KG.
* **Hasil**: `(100/300) * 282 = 94 PCS`.

### Kasus 2: Produk SHEET (Lembaran)

* **Master**: Tebal 1.0, Lebar 100, Panjang 100.
* **Hasil Berat**: `(1.0 * 100 * 100 * 7.85) / 1.000.000 = 0.785 KG/Sheet`.

### Kasus 3: Perubahan VAVE

* **Baseline**: 0.850 KG.
* **Actual**: 0.820 KG.
* **Status**: **MERIT** (Terjadi penghematan 0.030 KG).

---

*Catatan Penting: Seluruh perhitungan PCS diwajibkan menggunakan pembulatan ke bawah (`floor`) untuk menjamin keamanan stok fisik dan menghindari selisih fiktif.*
