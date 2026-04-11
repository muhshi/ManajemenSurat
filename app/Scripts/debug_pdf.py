import pdfplumber
import sys

def main():
    pdf_path = sys.argv[1]
    output_path = sys.argv[2]
    with pdfplumber.open(pdf_path) as pdf:
        with open(output_path, 'w') as f:
            for i in range(16, 19):
                if i < len(pdf.pages):
                    page = pdf.pages[i]
                    text = page.extract_text(layout=True) or page.extract_text()
                    f.write(f"--- PAGE {page.page_number} ---\n")
                    f.write(text)
                    f.write("\n\n")

if __name__ == '__main__':
    main()
