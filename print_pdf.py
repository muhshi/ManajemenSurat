import pdfplumber

with pdfplumber.open("PENG_PM_20250915_02 (1).pdf") as pdf:
    for page in pdf.pages:
        print("Words on page", page.page_number, ":", len(page.extract_words()))
