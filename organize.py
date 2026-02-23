import re

channels = []
with open('umitmod.m3u', 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()
    items = re.findall(r'(#EXTINF:.*?\n(http.*?))', content, re.DOTALL)
    
name_counts = {}
for item in items:
    info = item[0]
    url = item[1].strip()
    name = info.split(',')[-1].split('\n')[0].strip()
    # İsimden "S1 E1" veya "1. Bölüm" gibi kısımları temizle ki ana dizi adını bulalım
    base_name = re.sub(r'(S\d+|E\d+|\d+\.\s*Bölüm.*)', '', name, flags=re.IGNORECASE).strip()
    name_counts[base_name] = name_counts.get(base_name, 0) + 1

with open('canli.m3u', 'w') as c, open('film.m3u', 'w') as f, open('dizi.m3u', 'w') as d:
    for item in items:
        info = item[0]
        url = item[1].strip()
        name = info.split(',')[-1].split('\n')[0].strip()
        base_name = re.sub(r'(S\d+|E\d+|\d+\.\s*Bölüm.*)', '', name, flags=re.IGNORECASE).strip()
        
        entry = f"{info}{url}\n"
        if name_counts[base_name] > 2:
            d.write(entry)
        elif "movie" in info.lower() or "film" in info.lower():
            f.write(entry)
        else:
            c.write(entry)
