#!/usr/bin/env python3
"""Crea le copie web dei logo togliendo l'<image> nascosto; gli originali non si toccano."""
import re, pathlib
root = pathlib.Path(__file__).resolve().parent.parent
pairs = {'CHIANUCCI.svg': 'chianucci.svg', 'CHIANUCCI_w.svg': 'chianucci-bianco.svg'}
for src, dst in pairs.items():
    svg = (root / 'design/logo/originals' / src).read_text(encoding='utf-8')
    svg = re.sub(r'<image\b.*?(?:/>|</image>)', '', svg, flags=re.S)
    svg = re.sub(r'<\?xml[^>]*\?>\s*', '', svg)
    svg = re.sub(r'<!--.*?-->\s*', '', svg, flags=re.S)
    (root / 'public/brand' / dst).write_text(svg, encoding='utf-8')
    print(dst, len(svg), 'byte')
