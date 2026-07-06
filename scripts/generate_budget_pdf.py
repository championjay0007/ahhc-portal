from reportlab.pdfgen import canvas
from reportlab.lib.pagesizes import letter
from reportlab.lib.units import inch
import os

txt_path = os.path.join('docs', 'budget_workflow_explainer.txt')
pdf_path = os.path.join('docs', 'budget_workflow_explainer.pdf')

if not os.path.exists(txt_path):
    print('MISSING_TXT')
    raise SystemExit(1)

with open(txt_path, 'r', encoding='utf-8') as f:
    lines = f.read().splitlines()

c = canvas.Canvas(pdf_path, pagesize=letter)
width, height = letter
left_margin = inch * 0.75
right_margin = inch * 0.75
top_margin = inch * 0.75
bottom_margin = inch * 0.75
y = height - top_margin

c.setFont('Helvetica', 11)
leading = 14

for line in lines:
    # wrap long lines manually
    if not line:
        y -= leading
    else:
        words = line.split(' ')
        cur = ''
        for w in words:
            trial = (cur + ' ' + w).strip()
            if c.stringWidth(trial, 'Helvetica', 11) > (width - left_margin - right_margin):
                c.drawString(left_margin, y, cur)
                y -= leading
                cur = w
            else:
                cur = trial
        if cur:
            c.drawString(left_margin, y, cur)
            y -= leading
    if y < bottom_margin + leading:
        c.showPage()
        c.setFont('Helvetica', 11)
        y = height - top_margin

c.save()
size = os.path.getsize(pdf_path) if os.path.exists(pdf_path) else 0
print('PDF_CREATED', pdf_path, size)
