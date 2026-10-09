#!/usr/bin/env python3
"""Verificador determinístico offline dos assets Podplant420 (requer Pillow)."""
from pathlib import Path
from PIL import Image
import hashlib, json, sys
base=Path(__file__).resolve().parents[1]/'web/themes/custom/aculta420/assets/branding/podplant420'
manifest=json.loads((base/'asset-inventory.json').read_text())
assert len(manifest)==22, f'Inventário esperado: 22 imagens; atual: {len(manifest)}'
seen=set()
for item in manifest:
 p=base/item['file']
 assert p.is_file(), f'Ausente: {p}'
 assert hashlib.sha256(p.read_bytes()).hexdigest()==item['sha256'], f'SHA divergente: {p}'
 assert item['sha256'] not in seen, f'Duplicata exata: {p}'
 seen.add(item['sha256'])
 with Image.open(p) as im:
  im.verify()
 with Image.open(p) as im:
  assert (im.width,im.height)==(item['width'],item['height']), p
  assert im.convert('RGBA').getextrema()[3][0] < 255, f'Sem transparencia: {p}'
  if item['file'].startswith('web/'):
   assert im.format == 'WEBP' and im.mode=='RGBA', p
print(f'PASS: {len(manifest)} imagens, SHA-256, decodificação, dimensões e transparência.')
