import pdfplumber
import sys
import re
import json

def parse_number(s):
    if not s:
        return 0.0
    s = str(s).replace(',', '').strip()
    try:
        return float(s)
    except:
        return 0.0

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "No input file"}))
        sys.exit(1)
        
    file_path = sys.argv[1]
    
    items = {}
    transactions_list = []
    # O(1) lookup dict: key = (item_code, tanggal, no_dok) -> index in transactions_list
    tx_index = {}
    
    current_item_code = None
    current_satuan = None
    errors = []
    
    # State for merging within the same date+dok
    current_tx_key = None
    
    # Pre-compile regex patterns (avoid recompilation per line)
    re_item_code = re.compile(r'KODE\s*BARANG\s*:\s*([\d\.]+)', re.IGNORECASE)
    re_item_name = re.compile(r'NAMA\s*BARANG\s*:\s*(.+)', re.IGNORECASE)
    re_satuan = re.compile(r'SATUAN\s*:\s*(.+)', re.IGNORECASE)
    re_tx_line = re.compile(r'^(\d+)\s+(\d{2}-\d{2}-\d{4})\s+(.*?)\s+([\w\-\/\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)$')
    re_fifo_6 = re.compile(r'^([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)$')
    re_saldo = re.compile(r'^Saldo\s+([\d,\.]+)\s+([\d,\.]+)$', re.IGNORECASE)
    re_nodok_cont = re.compile(r'^[\w\-\/\.]+$')
    
    try:
        with pdfplumber.open(file_path) as pdf:
            for page in pdf.pages:
                text = page.extract_text(layout=True)
                if not text:
                    text = page.extract_text()
                if not text:
                    continue
                
                lines = text.split('\n')
                for line in lines:
                    line_str = line.strip()
                    if not line_str:
                        continue
                        
                    # Match Master Data
                    item_match = re_item_code.search(line)
                    if item_match:
                        current_item_code = item_match.group(1).strip()
                        if current_item_code not in items:
                            items[current_item_code] = {"item_code": current_item_code, "item_name": "", "satuan": ""}
                            
                    nama_match = re_item_name.search(line)
                    if nama_match and current_item_code:
                        items[current_item_code]["item_name"] = nama_match.group(1).strip()
                            
                    satuan_match = re_satuan.search(line)
                    if satuan_match and current_item_code:
                        sat_str = satuan_match.group(1).strip()
                        items[current_item_code]["satuan"] = sat_str
                        current_satuan = sat_str
                        
                    # Normal Transaction Match
                    # Example: 2  05-01-2026 Umum       002/1/2026   0 0 0 1 4,500 4,500 7 4,500 31,500
                    tx_match = re_tx_line.match(line_str)
                    
                    if tx_match and current_item_code:
                        no_urut = tx_match.group(1)
                        tanggal = tx_match.group(2)
                        
                        # Convert to YYYY-MM-DD
                        d_parts = tanggal.split('-')
                        if len(d_parts) == 3:
                            tanggal = f"{d_parts[2]}-{d_parts[1]}-{d_parts[0]}"
                            
                        keterangan = tx_match.group(3).strip()
                        no_dok = tx_match.group(4).strip()
                        
                        masuk_unit = int(parse_number(tx_match.group(5)))
                        masuk_harga = parse_number(tx_match.group(6))
                        masuk_jumlah = parse_number(tx_match.group(7))
                        
                        keluar_unit = int(parse_number(tx_match.group(8)))
                        keluar_harga = parse_number(tx_match.group(9))
                        keluar_jumlah = parse_number(tx_match.group(10))
                        
                        saldo_unit = int(parse_number(tx_match.group(11)))
                        saldo_harga = parse_number(tx_match.group(12))
                        saldo_jumlah = parse_number(tx_match.group(13))
                        
                        tx_key = f"{tanggal}_{no_dok}"
                        current_tx_key = tx_key
                        
                        # O(1) lookup instead of O(n) linear search
                        lookup_key = (current_item_code, tanggal, no_dok)
                        
                        if lookup_key in tx_index:
                            existing = transactions_list[tx_index[lookup_key]]
                            existing["masuk_unit"] += masuk_unit
                            existing["masuk_jumlah"] += masuk_jumlah
                            if masuk_unit > 0:
                                existing["masuk_harga"] = masuk_harga # Just update to latest
                                
                            existing["keluar_unit"] += keluar_unit
                            existing["keluar_jumlah"] += keluar_jumlah
                            if keluar_unit > 0:
                                existing["keluar_harga"] = keluar_harga 
                                
                            existing["saldo_unit"] = saldo_unit
                            existing["saldo_harga"] = saldo_harga
                            existing["saldo_jumlah"] = saldo_jumlah
                        else:
                            tx_data = {
                                "item_code": current_item_code,
                                "tanggal": tanggal,
                                "keterangan": keterangan,
                                "no_dok": no_dok,
                                "masuk_unit": masuk_unit,
                                "masuk_harga": masuk_harga,
                                "masuk_jumlah": masuk_jumlah,
                                "keluar_unit": keluar_unit,
                                "keluar_harga": keluar_harga,
                                "keluar_jumlah": keluar_jumlah,
                                "saldo_unit": saldo_unit,
                                "saldo_harga": saldo_harga,
                                "saldo_jumlah": saldo_jumlah
                            }
                            tx_index[lookup_key] = len(transactions_list)
                            transactions_list.append(tx_data)
                            
                    # Check for partial FIFO lines
                    # Sometimes a line might have 6 numbers at the end (Keluar + Saldo) or 3 (Just Saldo)
                    # We are only interested in Keluar/Masuk spread lines.
                    # If a line has 6 numbers, it's extra Keluar and Saldo
                    # Format: spaces then 6 numbers: unit harga jumlah unit harga jumlah
                    elif re_fifo_6.match(line_str) and current_tx_key:
                        parts = line_str.split()
                        keluar_unit = int(parse_number(parts[0]))
                        keluar_harga = parse_number(parts[1])
                        keluar_jumlah = parse_number(parts[2])
                        
                        saldo_unit = int(parse_number(parts[3]))
                        saldo_harga = parse_number(parts[4])
                        saldo_jumlah = parse_number(parts[5])
                        
                        if keluar_unit > 0 or keluar_jumlah > 0:
                            # Merge into the current transaction (last tx for this item)
                            for tx in reversed(transactions_list):
                                if tx["item_code"] == current_item_code:
                                    tx["keluar_unit"] += keluar_unit
                                    tx["keluar_jumlah"] += keluar_jumlah
                                    tx["keluar_harga"] = keluar_harga
                                    tx["saldo_unit"] = saldo_unit
                                    tx["saldo_harga"] = saldo_harga
                                    tx["saldo_jumlah"] = saldo_jumlah
                                    break
                                
                    else:
                        # Could be 3 tokens for just saldo, we can update the last tx saldo_unit but usually "Saldo" word is there
                        # e.g. "Saldo 28 126,000"
                        m_saldo = re_saldo.match(line_str)
                        if m_saldo and current_item_code:
                            su = int(parse_number(m_saldo.group(1)))
                            sj = parse_number(m_saldo.group(2))
                            for tx in reversed(transactions_list):
                                if tx["item_code"] == current_item_code:
                                    # Override saldo to the final value shown in Saldo summary line
                                    tx["saldo_unit"] = su
                                    tx["saldo_jumlah"] = sj
                                    break
                        elif current_tx_key and re_nodok_cont.match(line_str):
                            # Jika baris ini hanya berisi karakter no_dok (huruf, angka, strip, garis miring) tanpa spasi
                            # Kemungkinan besar ini adalah sambungan dari no_dok yang terpotong ke baris bawahnya
                            for tx in reversed(transactions_list):
                                if tx["item_code"] == current_item_code:
                                    tx["no_dok"] += line_str
                                    break
                        
    except Exception as e:
        errors.append(f"Exception: {str(e)}")

    output = {
        "status": "success",
        "summary": {
            "total_items": len(items.keys()),
            "total_transactions": len(transactions_list)
        },
        "items": list(items.values()),
        "transactions": transactions_list,
        "errors": errors
    }
    print(json.dumps(output))

if __name__ == '__main__':
    main()
