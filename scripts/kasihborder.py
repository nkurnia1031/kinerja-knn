"""
add_swimlane_bottom_border.py

Menambahkan area padding di bawah swimlane pada file SVG hasil export
PlantUML, lalu menutupnya dengan border horizontal.

Cara kerja:
1. Cari semua elemen <line> vertikal yang stroke color & stroke-width nya
   cocok dengan skinparam SwimlaneBorderColor / SwimlaneBorderThickness
   (garis kiri, pembatas antar lane, dan garis kanan).
2. Ambil x paling kiri, x paling kanan, dan y paling bawah dari garis-garis itu.
3. Tambahkan garis vertikal lanjutan ke bawah untuk setiap border swimlane
   sepanjang nilai padding.
4. Tambahkan satu garis horizontal baru di posisi bawah padding agar
   swimlane tertutup rapat seperti tabel.
5. Jika perlu, tinggi canvas SVG ikut diperbesar supaya border tambahan
   tidak terpotong.

Penggunaan:
    python add_swimlane_bottom_border.py INPUT_FOLDER OUTPUT_FOLDER
        [--color "#000000"] [--width 2] [--padding 30]

Catatan: warna & lebar harus SAMA dengan yang dipakai di skinparam
SwimlaneBorderColor / SwimlaneBorderThickness pada file .puml kamu,
supaya script bisa mengenali garis swimlane mana yang dimaksud.
"""

import argparse
import math
import glob
import os
import re

LINE_TAG_RE = re.compile(r"<line\b[^>]*?/>")
ATTR_RE = re.compile(r'([\w-]+)="([^"]*)"')
SVG_TAG_RE = re.compile(r"<svg\b[^>]*>")
STYLE_HEIGHT_RE = re.compile(r"height:([0-9.]+)px")


def parse_attrs(tag_text):
    return dict(ATTR_RE.findall(tag_text))


def parse_length(value):
    if value is None:
        return None
    value = value.strip()
    if value.endswith("px"):
        value = value[:-2]
    try:
        return float(value)
    except ValueError:
        return None


def format_number(value):
    return f"{value:g}"


def replace_attr(tag_text, attr_name, new_value):
    return re.sub(
        rf'({attr_name}=")[^"]*(")',
        lambda match: f'{match.group(1)}{new_value}{match.group(2)}',
        tag_text,
        count=1,
    )


def unique_sorted(values, tolerance=0.01):
    result = []
    for value in sorted(values):
        if not result or abs(value - result[-1]) > tolerance:
            result.append(value)
    return result


def find_swimlane_border_lines(svg_text, color, width):
    """Cari semua <line> vertikal dengan warna & lebar sesuai border swimlane."""
    candidates = []
    for m in LINE_TAG_RE.finditer(svg_text):
        attrs = parse_attrs(m.group(0))
        style = attrs.get("style", "")
        try:
            x1, x2 = float(attrs["x1"]), float(attrs["x2"])
            y1, y2 = float(attrs["y1"]), float(attrs["y2"])
        except (KeyError, ValueError):
            continue

        is_vertical = abs(x1 - x2) < 0.01
        color_match = f"stroke:{color}".lower() in style.lower()
        width_match = f"stroke-width:{width}".lower() in style.lower()

        if is_vertical and color_match and width_match:
            candidates.append((x1, min(y1, y2), max(y1, y2)))
    return candidates


def ensure_svg_height(svg_text, min_height):
    """Perbesar tinggi canvas SVG bila border baru melewati tinggi saat ini."""
    match = SVG_TAG_RE.search(svg_text)
    if not match:
        return svg_text

    svg_tag = match.group(0)
    attrs = parse_attrs(svg_tag)
    new_tag = svg_tag

    height_attr = attrs.get("height")
    current_height = parse_length(height_attr)
    if current_height is not None and current_height < min_height:
        if height_attr and height_attr.strip().endswith("px"):
            new_tag = replace_attr(new_tag, "height", f"{format_number(min_height)}px")
        else:
            new_tag = replace_attr(new_tag, "height", format_number(min_height))

    style_attr = attrs.get("style")
    if style_attr:
        style_match = STYLE_HEIGHT_RE.search(style_attr)
        if style_match:
            style_height = float(style_match.group(1))
            if style_height < min_height:
                new_style = STYLE_HEIGHT_RE.sub(f"height:{format_number(min_height)}px", style_attr, count=1)
                new_tag = replace_attr(new_tag, "style", new_style)

    view_box = attrs.get("viewBox")
    if view_box:
        parts = view_box.replace(",", " ").split()
        if len(parts) == 4:
            try:
                vb_x, vb_y, vb_width, vb_height = (float(part) for part in parts)
            except ValueError:
                vb_height = None
            if vb_height is not None and vb_height < min_height:
                new_view_box = " ".join(
                    [
                        format_number(vb_x),
                        format_number(vb_y),
                        format_number(vb_width),
                        format_number(min_height),
                    ]
                )
                new_tag = replace_attr(new_tag, "viewBox", new_view_box)

    if new_tag == svg_tag:
        return svg_text
    return svg_text[: match.start()] + new_tag + svg_text[match.end() :]


def add_bottom_border(svg_text, color="#000000", width="2", padding=30):
    candidates = find_swimlane_border_lines(svg_text, color, width)
    if not candidates:
        return svg_text, False

    x_positions = unique_sorted(c[0] for c in candidates)
    x_min = min(x_positions)
    x_max = max(x_positions)
    y_bottom = max(c[2] for c in candidates)
    y_extended = y_bottom + max(float(padding), 0)

    new_elements = []
    for x_pos in x_positions:
        new_elements.append(
            f'<line style="stroke:{color};stroke-width:{width};" '
            f'x1="{format_number(x_pos)}" x2="{format_number(x_pos)}" '
            f'y1="{format_number(y_bottom)}" y2="{format_number(y_extended)}"/>'
        )

    new_elements.append(
        f'<line style="stroke:{color};stroke-width:{width};" '
        f'x1="{format_number(x_min)}" x2="{format_number(x_max)}" '
        f'y1="{format_number(y_extended)}" y2="{format_number(y_extended)}"/>'
    )

    insert_at = svg_text.rfind("</svg>")
    if insert_at == -1:
        return svg_text, False

    stroke_width = parse_length(width) or 1
    required_height = math.ceil(y_extended + stroke_width + 2)
    resized_svg = ensure_svg_height(svg_text, required_height)
    insert_at = resized_svg.rfind("</svg>")
    new_svg = resized_svg[:insert_at] + "".join(new_elements) + resized_svg[insert_at:]
    return new_svg, True


def batch_process(input_folder, output_folder, color, width, padding):
    os.makedirs(output_folder, exist_ok=True)
    files = sorted(glob.glob(os.path.join(input_folder, "*.svg")))

    if not files:
        print(f"Tidak ada file .svg ditemukan di '{input_folder}'.")
        return

    for path in files:
        with open(path, "r", encoding="utf-8") as f:
            content = f.read()

        new_content, changed = add_bottom_border(content, color, width, padding)
        out_path = os.path.join(output_folder, os.path.basename(path))

        with open(out_path, "w", encoding="utf-8") as f:
            f.write(new_content)

        status = "padding + border bawah ditambahkan" if changed else "garis swimlane tidak terdeteksi (dilewati)"
        print(f"- {os.path.basename(path)}: {status}")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Tambahkan border bawah swimlane pada SVG hasil PlantUML.")
    parser.add_argument("input_folder", help="Folder berisi file .svg sumber")
    parser.add_argument("output_folder", help="Folder tujuan hasil modifikasi")
    parser.add_argument("--color", default="#000000", help="Warna border swimlane (samakan dengan skinparam SwimlaneBorderColor)")
    parser.add_argument("--width", default="2", help="Lebar border swimlane (samakan dengan skinparam SwimlaneBorderThickness)")
    parser.add_argument("--padding", type=float, default=30, help="Panjang tambahan vertikal ke bawah sebelum border horizontal ditutup")
    args = parser.parse_args()

    batch_process(args.input_folder, args.output_folder, args.color, args.width, args.padding)
