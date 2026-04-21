import pdfplumber
import sys
import re
import json
from concurrent.futures import ProcessPoolExecutor, as_completed
import os

def parse_number(s):
    if not s:
        return 0.0
    s = str(s).replace(',', '').strip()
    try:
        return float(s)
    except:
        return 0.0

def process_page_range(args):
    """Process a range of PDF pages. Opens the PDF only once per worker."""
    file_path, start_page, end_page = args
    
    items = {}
    transactions = []
    current_item_code = None
    current_tx_key = None
    
    re_item_code = re.compile(r'KODE\s*BARANG\s*:?\s*([\d\.]+)', re.IGNORECASE)
    re_item_name = re.compile(r'NAMA\s*BARANG\s*:?\s*(.+)', re.IGNORECASE)
    re_satuan    = re.compile(r'SATUAN\s*:?\s*(.+)', re.IGNORECASE)
    re_tx_line   = re.compile(r'^(\d+)\s+(\d{2}-\d{2}-\d{4})\s+(.*?)\s+([\w\-\/\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)$')
    re_fifo_6    = re.compile(r'^([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)$')
    re_saldo     = re.compile(r'^Saldo\s+([\d,\.]+)\s+([\d,\.]+)$', re.IGNORECASE)
    re_nodok     = re.compile(r'^[\w\-\/\.]+$')
    
    try:
        with pdfplumber.open(file_path) as pdf:
            for page_index in range(start_page, min(end_page, len(pdf.pages))):
                page = pdf.pages[page_index]
                text = page.extract_text()
                if not text:
                    continue
                
                lines = text.split('\n')
                for line in lines:
                    line_str = line.strip()
                    if not line_str:
                        continue
                    
                    # Match Master Data
                    item_match = re_item_code.search(line_str)
                    if item_match:
                        current_item_code = item_match.group(1).strip()
                        if current_item_code not in items:
                            items[current_item_code] = {"item_code": current_item_code, "item_name": "", "satuan": ""}
                    
                    nama_match = re_item_name.search(line_str)
                    if nama_match and current_item_code:
                        items[current_item_code]["item_name"] = nama_match.group(1).strip()
                    
                    satuan_match = re_satuan.search(line_str)
                    if satuan_match and current_item_code:
                        items[current_item_code]["satuan"] = satuan_match.group(1).strip()
                    
                    # Transaction match
                    tx_match = re_tx_line.match(line_str)
                    
                    if tx_match and current_item_code:
                        tanggal = tx_match.group(2)
                        d_parts = tanggal.split('-')
                        if len(d_parts) == 3:
                            tanggal = f"{d_parts[2]}-{d_parts[1]}-{d_parts[0]}"
                        
                        keterangan = tx_match.group(3).strip()
                        no_dok = tx_match.group(4).strip()
                        
                        current_tx_key = f"{tanggal}_{no_dok}"
                        
                        transactions.append({
                            "item_code": current_item_code,
                            "tanggal": tanggal,
                            "keterangan": keterangan,
                            "no_dok": no_dok,
                            "masuk_unit": int(parse_number(tx_match.group(5))),
                            "masuk_harga": parse_number(tx_match.group(6)),
                            "masuk_jumlah": parse_number(tx_match.group(7)),
                            "keluar_unit": int(parse_number(tx_match.group(8))),
                            "keluar_harga": parse_number(tx_match.group(9)),
                            "keluar_jumlah": parse_number(tx_match.group(10)),
                            "saldo_unit": int(parse_number(tx_match.group(11))),
                            "saldo_harga": parse_number(tx_match.group(12)),
                            "saldo_jumlah": parse_number(tx_match.group(13))
                        })
                    
                    elif re_fifo_6.match(line_str) and current_tx_key and transactions:
                        parts = line_str.split()
                        keluar_unit = int(parse_number(parts[0]))
                        keluar_harga = parse_number(parts[1])
                        keluar_jumlah = parse_number(parts[2])
                        saldo_unit = int(parse_number(parts[3]))
                        saldo_harga = parse_number(parts[4])
                        saldo_jumlah = parse_number(parts[5])
                        
                        if keluar_unit > 0 or keluar_jumlah > 0:
                            for tx in reversed(transactions):
                                if tx["item_code"] == current_item_code:
                                    tx["keluar_unit"] += keluar_unit
                                    tx["keluar_jumlah"] += keluar_jumlah
                                    tx["keluar_harga"] = keluar_harga
                                    tx["saldo_unit"] = saldo_unit
                                    tx["saldo_harga"] = saldo_harga
                                    tx["saldo_jumlah"] = saldo_jumlah
                                    break
                    else:
                        m_saldo = re_saldo.match(line_str)
                        if m_saldo and current_item_code and transactions:
                            su = int(parse_number(m_saldo.group(1)))
                            sj = parse_number(m_saldo.group(2))
                            for tx in reversed(transactions):
                                if tx["item_code"] == current_item_code:
                                    tx["saldo_unit"] = su
                                    tx["saldo_jumlah"] = sj
                                    break
                        elif current_tx_key and re_nodok.match(line_str) and transactions:
                            for tx in reversed(transactions):
                                if tx["item_code"] == current_item_code:
                                    tx["no_dok"] += line_str
                                    break
    except Exception:
        pass
    
    return items, transactions


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "No input file"}))
        sys.exit(1)
    
    file_path = sys.argv[1]
    
    # Determine page count
    with pdfplumber.open(file_path) as pdf:
        total_pages = len(pdf.pages)
    
    # Split pages into chunks for parallel processing
    max_workers = min(4, os.cpu_count() or 2)
    chunk_size = max(1, total_pages // max_workers)
    
    chunks = []
    for i in range(0, total_pages, chunk_size):
        chunks.append((file_path, i, min(i + chunk_size, total_pages)))
    
    all_items = {}
    all_transactions = []
    tx_index = {}
    errors = []
    
    try:
        with ProcessPoolExecutor(max_workers=max_workers) as executor:
            futures = {executor.submit(process_page_range, chunk): chunk for chunk in chunks}
            
            for future in as_completed(futures):
                try:
                    page_items, page_txs = future.result()
                    
                    # Merge items
                    for code, item_data in page_items.items():
                        if code not in all_items:
                            all_items[code] = item_data
                        else:
                            if item_data["item_name"] and not all_items[code]["item_name"]:
                                all_items[code]["item_name"] = item_data["item_name"]
                            if item_data["satuan"] and not all_items[code]["satuan"]:
                                all_items[code]["satuan"] = item_data["satuan"]
                    
                    # Merge transactions (deduplicate)
                    for tx in page_txs:
                        lookup_key = (tx["item_code"], tx["tanggal"], tx["no_dok"])
                        if lookup_key in tx_index:
                            existing = all_transactions[tx_index[lookup_key]]
                            existing["masuk_unit"] += tx["masuk_unit"]
                            existing["masuk_jumlah"] += tx["masuk_jumlah"]
                            if tx["masuk_unit"] > 0:
                                existing["masuk_harga"] = tx["masuk_harga"]
                            existing["keluar_unit"] += tx["keluar_unit"]
                            existing["keluar_jumlah"] += tx["keluar_jumlah"]
                            if tx["keluar_unit"] > 0:
                                existing["keluar_harga"] = tx["keluar_harga"]
                            existing["saldo_unit"] = tx["saldo_unit"]
                            existing["saldo_harga"] = tx["saldo_harga"]
                            existing["saldo_jumlah"] = tx["saldo_jumlah"]
                        else:
                            tx_index[lookup_key] = len(all_transactions)
                            all_transactions.append(tx)
                            
                except Exception as e:
                    errors.append(f"Page processing error: {str(e)}")
    
    except Exception as e:
        errors.append(f"Exception: {str(e)}")
    
    output = {
        "status": "success",
        "summary": {
            "total_items": len(all_items),
            "total_transactions": len(all_transactions)
        },
        "items": list(all_items.values()),
        "transactions": all_transactions,
        "errors": errors
    }
    print(json.dumps(output))

if __name__ == '__main__':
    main()
