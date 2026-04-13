<?php

namespace App\Exports\Sheets;

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
                $transactions = $this->item->transactions;

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
                $widths = ['A'=>5,'B'=>6,'C'=>14,'D'=>14,'E'=>28,'F'=>14,'G'=>12,'H'=>12,'I'=>12];
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
                $sheet->setCellValue("A{$r}", 'RINGKASAN PENGELUARAN PER BULAN');
                $boldCenter("A{$r}:O{$r}");
                $sheet->getStyle("A{$r}:O{$r}")->applyFromArray($headerFill);

                // Row 7: Header bulan - semua 12 bulan dalam 1 baris horizontal
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

                // Hitung pengeluaran per bulan dari transaksi
                $monthlyOut = array_fill(1, 12, 0);
                $stockAwal = 0;
                $firstSaldo = null;

                foreach ($transactions as $tx) {
                    if ($tx->tanggal) {
                        $bulan = (int) \Carbon\Carbon::parse($tx->tanggal)->format('m');
                        $monthlyOut[$bulan] += $tx->keluar_unit;
                    }
                    if ($firstSaldo === null) {
                        $stockAwal = $tx->saldo_unit + $tx->keluar_unit - $tx->masuk_unit;
                        $firstSaldo = true;
                    }
                }

                // Row 8: Angka pengeluaran per bulan
                $r = 8;
                for ($m = 0; $m < 12; $m++) {
                    $val = $monthlyOut[$m + 1];
                    $sheet->setCellValue($monthCols[$m] . $r, $val > 0 ? $val : '');
                }
                $totalOut = array_sum($monthlyOut);
                $sheet->setCellValue("M{$r}", $totalOut > 0 ? $totalOut : 0);
                $sheet->setCellValue("N{$r}", $stockAwal);
                $lastTx = $transactions->last();
                $sheet->setCellValue("O{$r}", $lastTx ? $lastTx->saldo_unit : 0);
                $sheet->getStyle("A{$r}:O{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$r}:O{$r}")->getFont()->setSize(9);

                // Border tabel bulanan
                $sheet->getStyle("A6:O8")->applyFromArray($thinBorder);

                // ============================================================
                // TABEL 2: RINCIAN TRANSAKSI (Row 10+)
                // ============================================================
                $r = 10;
                $sheet->mergeCells("A{$r}:I{$r}");
                $sheet->setCellValue("A{$r}", 'RINCIAN TRANSAKSI');
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

                // Data transaksi
                $r = 12;
                $no = 1;
                foreach ($transactions as $tx) {
                    $sheet->setCellValue("A{$r}", $no);
                    $sheet->setCellValue("B{$r}", $tx->no_dok ?? '');
                    $sheet->setCellValue("C{$r}", $tx->tanggal ? \Carbon\Carbon::parse($tx->tanggal)->format('d/m/Y') : '-');
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
                $firstData = 12;
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

                // Border tabel rincian
                $sheet->getStyle("A10:I{$sumRow}")->applyFromArray($thinBorder);

                // ============================================================
                // Number format for currency columns
                // ============================================================
                $sheet->getStyle("F{$firstData}:F{$sumRow}")->getNumberFormat()
                    ->setFormatCode('#,##0');

                // ============================================================
                // PRINT SETTINGS
                // ============================================================
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_FOLIO);
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
