<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class InventoryItemSheet implements WithTitle, WithEvents, WithDrawings
{
    private $item;
    private $year;
    private $index;

    public function __construct($item, $year, $index = 1)
    {
        $this->item = $item;
        $this->year = $year;
        $this->index = $index;
    }

    public function title(): string
    {
        $title = $this->item->item_name ?? 'Barang ' . $this->item->id;
        $title = str_replace(['\\', '*', ':', '?', '[', ']', '/'], '', $title);
        $title = trim($title);
        $prefix = $this->index . '. ';
        $maxLen = 31 - strlen($prefix);
        return $prefix . substr($title, 0, $maxLen);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo BPS');
        $drawing->setDescription('Logo BPS');
        $drawing->setPath(public_path('images/logo_bps.png'));
        $drawing->setHeight(60);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(5);
        return $drawing;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $allTransactions = $this->item->transactions;
                $year = $this->year;

                // ============================================================
                // PISAHKAN: transaksi sebelum tahun ini (untuk carry-over)
                //           transaksi dalam tahun ini (untuk ditampilkan)
                // ============================================================
                $prevTransactions = $allTransactions->filter(function ($tx) use ($year) {
                    return $tx->tanggal && Carbon::parse($tx->tanggal)->year < $year;
                });

                $transactions = $allTransactions->filter(function ($tx) use ($year) {
                    return $tx->tanggal && Carbon::parse($tx->tanggal)->year == $year;
                })->values();

                // Saldo carry-over = saldo_unit dari transaksi terakhir sebelum tahun ini
                $carryOverSaldo = 0;
                if ($prevTransactions->isNotEmpty()) {
                    $carryOverSaldo = $prevTransactions->last()->saldo_unit;
                }

                $thinBorder = [
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ];
                $headerFill = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9E1F2'],
                    ],
                ];
                $boldCenter = function ($range) use ($sheet) {
                    $sheet->getStyle($range)->getFont()->setBold(true);
                    $sheet->getStyle($range)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                };

                // ============================================================
                // COLUMN WIDTHS
                // ============================================================
                $widths = ['A'=>5,'B'=>22,'C'=>14,'D'=>14,'E'=>28,'F'=>14,'G'=>12,'H'=>12,'I'=>12];
                foreach ($widths as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }

                // ============================================================
                // ROW 1-4: KOP SURAT + LOGO
                // ============================================================
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(15);
                $sheet->getRowDimension(4)->setRowHeight(15);

                // Logo Space (A1:B4) — logo ditempel di sini via drawings()
                $sheet->mergeCells('A1:B4');

                // Title
                $sheet->mergeCells('C1:I1');
                $sheet->setCellValue('C1', 'KARTU PERSEDIAAN BARANG PAKAI HABIS ( ATK / ARK )');
                $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Info rows
                $sheet->mergeCells('C2:E2');
                $sheet->setCellValue('C2', 'BADAN PUSAT STATISTIK KABUPATEN DEMAK');
                $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(10);

                $sheet->setCellValue('F2', 'Nama Barang');
                $sheet->mergeCells('G2:I2');
                $sheet->setCellValue('G2', ': ' . ($this->item->item_name ?? ''));

                $sheet->mergeCells('C3:E3');
                $sheet->setCellValue('C3', 'Jalan Sultan Hadiwijaya No 23 Demak');
                $sheet->getStyle('C3')->getFont()->setSize(9);

                $sheet->setCellValue('F3', 'Kode Barang');
                $sheet->mergeCells('G3:I3');
                $sheet->setCellValue('G3', ': ' . ($this->item->item_code ?? ''));

                $sheet->mergeCells('C4:E4');
                $sheet->setCellValue('C4', '');

                $sheet->setCellValue('F4', 'Satuan');
                $sheet->setCellValue('G4', ': ' . ($this->item->satuan ?? ''));
                $sheet->setCellValue('H4', 'Tahun');
                $sheet->setCellValue('I4', ': ' . $this->year);

                // ============================================================
                // TABEL 1: RINGKASAN BULANAN (Row 6 - 8)
                // ============================================================
                $r = 6;
                $sheet->mergeCells("A{$r}:O{$r}");
                $sheet->setCellValue("A{$r}", 'RINGKASAN PENGELUARAN PER BULAN - TAHUN ' . $this->year);
                $boldCenter("A{$r}:O{$r}");
                $sheet->getStyle("A{$r}:O{$r}")->applyFromArray($headerFill);

                // Row 7: Header bulan
                $r = 7;
                $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                $monthCols = ['A','B','C','D','E','F','G','H','I','J','K','L'];
                for ($m = 0; $m < 12; $m++) {
                    $sheet->setCellValue($monthCols[$m] . $r, $months[$m]);
                    $sheet->getColumnDimension($monthCols[$m])->setWidth(6);
                }
                $sheet->setCellValue("M{$r}", 'Jumlah');
                $sheet->getColumnDimension('M')->setWidth(8);
                $sheet->setCellValue("N{$r}", 'Stock Awal');
                $sheet->getColumnDimension('N')->setWidth(10);
                $sheet->setCellValue("O{$r}", 'Stock Akhir');
                $sheet->getColumnDimension('O')->setWidth(10);
                $boldCenter("A{$r}:O{$r}");
                $sheet->getStyle("A{$r}:O{$r}")->applyFromArray($headerFill);
                $sheet->getStyle("A{$r}:O{$r}")->getFont()->setSize(8);
                $sheet->getStyle("A{$r}:O{$r}")->getAlignment()->setWrapText(true);
                $sheet->getRowDimension($r)->setRowHeight(25);

                // Hitung pengeluaran per bulan dari transaksi TAHUN INI saja
                $monthlyOut = array_fill(1, 12, 0);
                foreach ($transactions as $tx) {
                    $bulan = (int) Carbon::parse($tx->tanggal)->format('m');
                    $monthlyOut[$bulan] += $tx->keluar_unit;
                }

                // Row 8: Angka pengeluaran per bulan
                $r = 8;
                for ($m = 0; $m < 12; $m++) {
                    $val = $monthlyOut[$m + 1];
                    $sheet->setCellValue($monthCols[$m] . $r, $val > 0 ? $val : '');
                }
                $totalOut = array_sum($monthlyOut);
                $sheet->setCellValue("M{$r}", $totalOut > 0 ? $totalOut : 0);
                $sheet->setCellValue("N{$r}", $carryOverSaldo);  // Stock Awal = carry-over
                $lastTx = $transactions->last();
                $sheet->setCellValue("O{$r}", $lastTx ? $lastTx->saldo_unit : $carryOverSaldo);
                $sheet->getStyle("A{$r}:O{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$r}:O{$r}")->getFont()->setSize(9);

                // Border tabel bulanan
                $sheet->getStyle("A6:O8")->applyFromArray($thinBorder);

                // ============================================================
                // TABEL 2: RINCIAN TRANSAKSI (Row 10+)
                // ============================================================
                $r = 10;
                $sheet->mergeCells("A{$r}:I{$r}");
                $sheet->setCellValue("A{$r}", 'RINCIAN TRANSAKSI TAHUN ' . $this->year);
                $boldCenter("A{$r}:I{$r}");
                $sheet->getStyle("A{$r}:I{$r}")->applyFromArray($headerFill);

                // Header tabel rincian
                $r = 11;
                $headers = ['No', 'No. Bon/Faktur', 'Tanggal', 'Uraian Pemasukan / Pengeluaran', '', 'Harga', 'Masuk (M)', 'Keluar (K)', 'Sisa'];
                $cols = ['A','B','C','D','E','F','G','H','I'];
                foreach ($headers as $i => $h) {
                    $sheet->setCellValue($cols[$i] . $r, $h);
                }
                $sheet->mergeCells("D{$r}:E{$r}");
                $boldCenter("A{$r}:I{$r}");
                $sheet->getStyle("A{$r}:I{$r}")->applyFromArray($headerFill);
                $sheet->getStyle("A{$r}:I{$r}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("A{$r}:I{$r}")->getFont()->setSize(8);
                $sheet->getRowDimension($r)->setRowHeight(28);

                // ============================================================
                // BARIS SALDO AWAL (carry-over dari tahun sebelumnya)
                // ============================================================
                $r = 12;
                $sheet->mergeCells("A{$r}:E{$r}");
                $sheet->setCellValue("A{$r}", 'Saldo Awal Tahun ' . $this->year);
                $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setItalic(true)->setSize(8);
                $sheet->getStyle("A{$r}:I{$r}")->applyFromArray($headerFill);
                $sheet->setCellValue("I{$r}", $carryOverSaldo);
                $sheet->getStyle("I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("I{$r}")->getFont()->setBold(true)->setSize(8);

                // Data transaksi tahun ini
                $r = 13;
                $firstData = $r;
                $no = 1;
                foreach ($transactions as $tx) {
                    $sheet->setCellValue("A{$r}", $no);
                    $sheet->setCellValue("B{$r}", $tx->no_dok ?? '');
                    $sheet->setCellValue("C{$r}", $tx->tanggal ? Carbon::parse($tx->tanggal)->format('d/m/Y') : '-');
                    $sheet->mergeCells("D{$r}:E{$r}");
                    $sheet->setCellValue("D{$r}", $tx->keterangan);
                    $sheet->setCellValue("F{$r}", $tx->masuk_harga > 0 ? $tx->masuk_harga : ($tx->keluar_harga > 0 ? $tx->keluar_harga : ''));
                    $sheet->setCellValue("G{$r}", $tx->masuk_unit > 0 ? $tx->masuk_unit : '');
                    $sheet->setCellValue("H{$r}", $tx->keluar_unit > 0 ? $tx->keluar_unit : '');
                    $sheet->setCellValue("I{$r}", $tx->saldo_unit);

                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$r}:I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("A{$r}:I{$r}")->getFont()->setSize(8);

                    $no++;
                    $r++;
                }

                // JUMLAH row
                $sumRow = $r;
                $lastData = $sumRow - 1;

                $sheet->mergeCells("A{$sumRow}:E{$sumRow}");
                $sheet->setCellValue("A{$sumRow}", 'JUMLAH');
                $boldCenter("A{$sumRow}:E{$sumRow}");

                if ($lastData >= $firstData) {
                    $sheet->setCellValue("G{$sumRow}", "=SUM(G{$firstData}:G{$lastData})");
                    $sheet->setCellValue("H{$sumRow}", "=SUM(H{$firstData}:H{$lastData})");
                    $sheet->setCellValue("I{$sumRow}", '');
                } else {
                    $sheet->setCellValue("G{$sumRow}", 0);
                    $sheet->setCellValue("H{$sumRow}", 0);
                }
                $sheet->getStyle("G{$sumRow}:I{$sumRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A{$sumRow}:I{$sumRow}")->getFont()->setBold(true)->setSize(8);

                // Border tabel rincian (mulai row 10 hingga sumRow, termasuk row saldo awal)
                $sheet->getStyle("A10:I{$sumRow}")->applyFromArray($thinBorder);

                // ============================================================
                // Number format for currency columns
                // ============================================================
                $sheet->getStyle("F{$firstData}:F{$sumRow}")->getNumberFormat()
                    ->setFormatCode('#,##0');

                // ============================================================
                // PRINT SETTINGS
                // ============================================================
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_FOLIO);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);
            },
        ];
    }
}
