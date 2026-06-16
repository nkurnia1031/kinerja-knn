from __future__ import annotations

import copy
import random
import re
import shutil
import tempfile
import zipfile
from pathlib import Path
from typing import Iterable
import xml.etree.ElementTree as ET


DOCX_PATH = Path(
    r"d:\Laragon\www\2026\kinerja-knn\KLASIFIKASI KINERJA KARYAWAN TAHUNAN MENGGUNAKAN METODE K.docx"
)
ROOT_DIR = Path(r"d:\Laragon\www\2026\kinerja-knn")

NS = {
    "w": "http://schemas.openxmlformats.org/wordprocessingml/2006/main",
    "w14": "http://schemas.microsoft.com/office/word/2010/wordml",
    "a": "http://schemas.openxmlformats.org/drawingml/2006/main",
    "r": "http://schemas.openxmlformats.org/officeDocument/2006/relationships",
    "wp": "http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing",
    "pic": "http://schemas.openxmlformats.org/drawingml/2006/picture",
    "pr": "http://schemas.openxmlformats.org/package/2006/relationships",
    "wp14": "http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing",
}

for prefix, uri in NS.items():
    ET.register_namespace(prefix, uri)

W = f"{{{NS['w']}}}"
W14 = f"{{{NS['w14']}}}"
A = f"{{{NS['a']}}}"
R = f"{{{NS['r']}}}"
WP = f"{{{NS['wp']}}}"
PIC = f"{{{NS['pic']}}}"
PR = f"{{{NS['pr']}}}"
WP14 = f"{{{NS['wp14']}}}"
XML_SPACE = "{http://www.w3.org/XML/1998/namespace}space"


STRUCTURE_IMAGES = [
    {
        "role": "Admin",
        "image": ROOT_DIR / "sitemap_penilaian_kinerja (1)-Admin.drawio.png",
        "width_px": 721,
        "height_px": 396,
    },
    {
        "role": "Atasan",
        "image": ROOT_DIR / "sitemap_penilaian_kinerja (1)-Atasan.drawio.png",
        "width_px": 611,
        "height_px": 349,
    },
    {
        "role": "Karyawan",
        "image": ROOT_DIR / "sitemap_penilaian_kinerja (1)-Karyawan.drawio.png",
        "width_px": 571,
        "height_px": 158,
    },
    {
        "role": "Pimpinan",
        "image": ROOT_DIR / "sitemap_penilaian_kinerja (1)-Pimpinan.drawio.png",
        "width_px": 551,
        "height_px": 171,
    },
]

FLOWCHART_IMAGES = {
    "media/image79.png": ROOT_DIR / "flowchart_sistem_penilaian_kinerja (1)-Admin.drawio.png",
    "media/image80.png": ROOT_DIR / "flowchart_sistem_penilaian_kinerja (1)-Atasan.drawio.png",
    "media/image81.png": ROOT_DIR / "flowchart_sistem_penilaian_kinerja (1)-Karyawan.drawio.png",
    "media/image82.png": ROOT_DIR / "flowchart_sistem_penilaian_kinerja (1)-Pimpinan.drawio (1).png",
}

STRUCTURE_WIDTH_EMU = 4_480_560  # ~4.9 in

STRUCTURE_DESCRIPTIONS = {
    "Admin": (
        "Struktur menu admin menggambarkan hubungan antar menu yang dapat diakses oleh admin, "
        "meliputi dashboard, data karyawan, atur relasi atasan, kriteria penilaian, periode "
        "penilaian, rekap penilaian, analisa kinerja, laporan, dan logout."
    ),
    "Atasan": (
        "Struktur menu atasan menggambarkan susunan menu yang mendukung proses penilaian "
        "bawahan, meliputi dashboard, penilaian bawahan, review penilaian, daftar bawahan, "
        "dan logout."
    ),
    "Karyawan": (
        "Struktur menu karyawan menggambarkan menu utama yang dapat diakses karyawan, "
        "meliputi dashboard, rekap penilaian, dan logout."
    ),
    "Pimpinan": (
        "Struktur menu pimpinan menggambarkan susunan menu yang tersedia bagi pimpinan, "
        "meliputi dashboard, laporan, dan logout."
    ),
}


def para_text(elem: ET.Element) -> str:
    return "".join(t.text or "" for t in elem.findall(".//w:t", NS)).strip()


def cell_text(elem: ET.Element) -> str:
    return "".join(t.text or "" for t in elem.findall(".//w:t", NS)).strip()


def normalize_spaces(text: str) -> str:
    return re.sub(r"\s+", " ", text).strip()


def random_hex(length: int = 8) -> str:
    return "".join(random.choice("0123456789ABCDEF") for _ in range(length))


def set_text_node(t: ET.Element, text: str) -> None:
    t.text = text
    if text.startswith(" ") or text.endswith(" "):
        t.set(XML_SPACE, "preserve")
    elif XML_SPACE in t.attrib:
        del t.attrib[XML_SPACE]


def clone_run_style(source_run: ET.Element | None) -> ET.Element:
    run = ET.Element(f"{W}r")
    if source_run is not None:
        rpr = source_run.find("w:rPr", NS)
        if rpr is not None:
            run.append(copy.deepcopy(rpr))
    return run


def new_text_run(text: str, source_run: ET.Element | None = None) -> ET.Element:
    run = clone_run_style(source_run)
    t = ET.Element(f"{W}t")
    set_text_node(t, text)
    run.append(t)
    return run


def ensure_run_bold(run: ET.Element) -> None:
    rpr = run.find("w:rPr", NS)
    if rpr is None:
        rpr = ET.Element(f"{W}rPr")
        run.insert(0, rpr)
    if rpr.find("w:b", NS) is None:
        rpr.append(ET.Element(f"{W}b"))
    if rpr.find("w:bCs", NS) is None:
        rpr.append(ET.Element(f"{W}bCs"))


def replace_paragraph_text(paragraph: ET.Element, text: str) -> None:
    children = list(paragraph)
    template_run = paragraph.find("w:r", NS)
    keep = {
        f"{W}pPr",
        f"{W}bookmarkStart",
        f"{W}bookmarkEnd",
        f"{W}proofErr",
        f"{W}permStart",
        f"{W}permEnd",
    }
    for child in children:
        if child.tag not in keep:
            paragraph.remove(child)
    insert_pos = 0
    while insert_pos < len(paragraph) and paragraph[insert_pos].tag != f"{W}bookmarkEnd":
        insert_pos += 1
    paragraph.insert(insert_pos, new_text_run(text, template_run))


def caption_label_and_number(text: str, prefix: str) -> tuple[int, str] | None:
    match = re.match(rf"^{prefix} 4\.(\d+)\s*(.+)$", normalize_spaces(text))
    if not match:
        return None
    return int(match.group(1)), match.group(2).strip()


def set_caption_text(paragraph: ET.Element, label_text: str) -> None:
    replace_paragraph_text(paragraph, label_text)


def assign_unique_para_ids(element: ET.Element) -> None:
    for paragraph in element.iter():
        if paragraph.tag == f"{W}p":
            paragraph.set(f"{W14}paraId", random_hex())
            paragraph.set(f"{W14}textId", random_hex())
    for inline in element.findall(".//wp:inline", NS):
        inline.set(f"{WP14}anchorId", random_hex())
        inline.set(f"{WP14}editId", random_hex())


def get_max_docpr_id(root: ET.Element) -> int:
    values = [
        int(node.get("id", "0"))
        for node in root.findall(".//wp:docPr", NS)
        if node.get("id", "0").isdigit()
    ]
    return max(values, default=0)


def get_max_bookmark_id(root: ET.Element) -> int:
    values = [
        int(node.get(f"{W}id", "0"))
        for node in root.findall(".//w:bookmarkStart", NS)
        if node.get(f"{W}id", "0").isdigit()
    ]
    return max(values, default=0)


def get_max_rid(relroot: ET.Element) -> int:
    values = []
    for rel in relroot.findall("pr:Relationship", NS):
        rid = rel.get("Id", "")
        match = re.match(r"rId(\d+)$", rid)
        if match:
            values.append(int(match.group(1)))
    return max(values, default=0)


def ensure_bookmark(
    paragraph: ET.Element, bookmark_name: str, bookmark_id: int
) -> tuple[str, int]:
    starts = paragraph.findall("w:bookmarkStart", NS)
    ends = paragraph.findall("w:bookmarkEnd", NS)
    if starts:
        preferred = next(
            (start for start in starts if (start.get(f"{W}name") or "").startswith("_Toc")),
            starts[0],
        )
        if preferred.get(f"{W}name"):
            return preferred.get(f"{W}name"), int(
                preferred.get(f"{W}id", str(bookmark_id))
            )

    start = ET.Element(f"{W}bookmarkStart")
    start.set(f"{W}id", str(bookmark_id))
    start.set(f"{W}name", bookmark_name)
    end = ET.Element(f"{W}bookmarkEnd")
    end.set(f"{W}id", str(bookmark_id))

    insert_after = 1 if paragraph.find("w:pPr", NS) is not None else 0
    paragraph.insert(insert_after, start)
    paragraph.append(end)
    return bookmark_name, bookmark_id


def set_bookmark(paragraph: ET.Element, bookmark_name: str, bookmark_id: int) -> tuple[str, int]:
    starts = paragraph.findall("w:bookmarkStart", NS)
    ends = paragraph.findall("w:bookmarkEnd", NS)
    if starts:
        starts[0].set(f"{W}id", str(bookmark_id))
        starts[0].set(f"{W}name", bookmark_name)
        for extra in starts[1:]:
            paragraph.remove(extra)
    else:
        start = ET.Element(f"{W}bookmarkStart")
        start.set(f"{W}id", str(bookmark_id))
        start.set(f"{W}name", bookmark_name)
        insert_after = 1 if paragraph.find("w:pPr", NS) is not None else 0
        paragraph.insert(insert_after, start)

    if ends:
        ends[0].set(f"{W}id", str(bookmark_id))
        for extra in ends[1:]:
            paragraph.remove(extra)
    else:
        end = ET.Element(f"{W}bookmarkEnd")
        end.set(f"{W}id", str(bookmark_id))
        paragraph.append(end)

    return bookmark_name, bookmark_id


def update_drawing(
    paragraph: ET.Element,
    rid: str,
    cx: int,
    cy: int,
    docpr_id: int,
    picture_name: str,
) -> None:
    blip = paragraph.find(".//a:blip", NS)
    if blip is not None:
        blip.set(f"{R}embed", rid)

    extent = paragraph.find(".//wp:extent", NS)
    if extent is not None:
        extent.set("cx", str(cx))
        extent.set("cy", str(cy))

    transform_extent = paragraph.find(".//a:xfrm/a:ext", NS)
    if transform_extent is not None:
        transform_extent.set("cx", str(cx))
        transform_extent.set("cy", str(cy))

    docpr = paragraph.find(".//wp:docPr", NS)
    if docpr is not None:
        docpr.set("id", str(docpr_id))
        docpr.set("name", picture_name)

    c_nvpr = paragraph.find(".//pic:cNvPr", NS)
    if c_nvpr is not None:
        c_nvpr.set("name", picture_name)


def add_relationship(relroot: ET.Element, rid: str, target: str) -> None:
    rel = ET.Element(f"{PR}Relationship")
    rel.set("Id", rid)
    rel.set(
        "Type",
        "http://schemas.openxmlformats.org/officeDocument/2006/relationships/image",
    )
    rel.set("Target", target)
    relroot.append(rel)


def first_table_caption_paragraph(table: ET.Element) -> ET.Element:
    return table.find("./w:tr[1]/w:tc[1]/w:p", NS)


def set_hyperlink_label_and_page(
    paragraph: ET.Element, label_text: str, anchor: str, page_text: str
) -> None:
    hyperlink = paragraph.find("w:hyperlink", NS)
    if hyperlink is None:
        return

    first_visible_run = None
    for run in hyperlink.findall("w:r", NS):
        if run.find("w:t", NS) is not None:
            first_visible_run = run
            break

    children = list(hyperlink)
    control_start = len(children)
    for idx, child in enumerate(children):
        if child.tag != f"{W}r":
            continue
        if child.find("w:tab", NS) is not None or child.find("w:fldChar", NS) is not None:
            control_start = idx
            break
        if child.find("w:instrText", NS) is not None:
            control_start = idx
            break

    for child in children[:control_start]:
        hyperlink.remove(child)

    hyperlink.insert(0, new_text_run(label_text, first_visible_run))
    hyperlink.set(f"{W}anchor", anchor)

    for instr in hyperlink.findall(".//w:instrText", NS):
        if instr.text and "PAGEREF" in instr.text:
            instr.text = f" PAGEREF {anchor} \\h "

    field_runs = [
        run
        for run in hyperlink.findall("w:r", NS)
        if run.find("w:fldChar", NS) is not None
        or run.find("w:instrText", NS) is not None
        or run.find("w:tab", NS) is not None
        or run.find("w:t", NS) is not None
    ]
    seen_separate = False
    for run in field_runs:
        fld = run.find("w:fldChar", NS)
        if fld is not None and fld.get(f"{W}fldCharType") == "separate":
            seen_separate = True
            continue
        if seen_separate:
            t = run.find("w:t", NS)
            if t is not None:
                set_text_node(t, page_text)
                break


def guess_new_page(title: str, old_page: str | None, fallback: str) -> str:
    if old_page:
        return old_page
    if "Struktur Menu" in title:
        if "Admin" in title:
            return "80"
        if "Atasan" in title:
            return "80"
        if "Karyawan" in title:
            return "81"
        if "Pimpinan" in title:
            return "81"
    if "Periode Penilaian" in title and title.startswith("Tabel"):
        return "82"
    return fallback


def replace_range(
    parent: ET.Element, start_idx: int, end_idx: int, new_elements: Iterable[ET.Element]
) -> None:
    original = list(parent)
    for idx in range(end_idx, start_idx - 1, -1):
        parent.remove(original[idx])
    for offset, element in enumerate(new_elements):
        parent.insert(start_idx + offset, element)


def paragraph_startswith(body: ET.Element, text: str) -> int:
    for idx, child in enumerate(list(body)):
        if child.tag == f"{W}p" and para_text(child).startswith(text):
            return idx
    raise ValueError(f"Paragraph starting with {text!r} not found")


def child_type_and_text(child: ET.Element) -> str:
    if child.tag == f"{W}p":
        return para_text(child)
    if child.tag == f"{W}tbl":
        return cell_text(first_table_caption_paragraph(child))
    return ""


def insert_structure_section(
    root: ET.Element, relroot: ET.Element, media_dir: Path
) -> tuple[list[tuple[str, str]], list[str]]:
    body = root.find("w:body", NS)
    children = list(body)

    heading_idx = paragraph_startswith(body, "Rancangan Struktur Program")
    intro_idx = heading_idx + 1
    image_idx = heading_idx + 2
    caption_idx = heading_idx + 3
    source_idx = heading_idx + 4

    intro_template = copy.deepcopy(children[intro_idx])
    subheading_template = copy.deepcopy(children[paragraph_startswith(body, "Flowchart Menu Admin")])
    desc_template = copy.deepcopy(children[paragraph_startswith(body, "Flowchart program admin menjelaskan")])
    image_template = copy.deepcopy(children[image_idx])
    caption_template = copy.deepcopy(children[caption_idx])
    source_template = copy.deepcopy(children[source_idx])

    docpr_id = get_max_docpr_id(root)
    bookmark_id = get_max_bookmark_id(root)
    next_rid_num = get_max_rid(relroot) + 1

    new_relationships: list[tuple[str, str]] = []
    new_bookmarks: list[str] = []

    replace_paragraph_text(
        intro_template,
        "Rancangan struktur program dibedakan berdasarkan hak akses pengguna, yaitu admin, "
        "atasan, karyawan, dan pimpinan. Setiap struktur menu memperlihatkan hubungan antarfitur "
        "yang dapat diakses oleh masing-masing peran.",
    )
    assign_unique_para_ids(intro_template)

    new_elements: list[ET.Element] = [intro_template]

    for idx, item in enumerate(STRUCTURE_IMAGES):
        role = item["role"]
        if idx == 0:
            rid = "rId85"
            target = "media/image66.png"
        else:
            rid = f"rId{next_rid_num}"
            next_rid_num += 1
            target = f"media/image{94 + idx}.png"
            new_relationships.append((rid, target))

        shutil.copyfile(item["image"], media_dir / Path(target).name if idx else media_dir / "image66.png")

        subheading = copy.deepcopy(subheading_template)
        replace_paragraph_text(subheading, f"Struktur Menu {role}")
        assign_unique_para_ids(subheading)

        description = copy.deepcopy(desc_template)
        replace_paragraph_text(description, STRUCTURE_DESCRIPTIONS[role])
        assign_unique_para_ids(description)

        image_paragraph = copy.deepcopy(image_template)
        assign_unique_para_ids(image_paragraph)
        docpr_id += 1
        cy = int(round(STRUCTURE_WIDTH_EMU * item["height_px"] / item["width_px"]))
        update_drawing(
            image_paragraph,
            rid=rid,
            cx=STRUCTURE_WIDTH_EMU,
            cy=cy,
            docpr_id=docpr_id,
            picture_name=f"Structure Menu {role}",
        )

        caption = copy.deepcopy(caption_template)
        assign_unique_para_ids(caption)
        set_caption_text(caption, f"Gambar 4.{34 + idx} Rancangan Struktur Menu {role}")
        bookmark_id += 1
        bookmark_name = "_Toc232012285" if idx == 0 else f"_CodexBab4Structure{role}"
        set_bookmark(caption, bookmark_name, bookmark_id)
        new_bookmarks.append(bookmark_name)

        source = copy.deepcopy(source_template)
        replace_paragraph_text(source, "Sumber : Hasil Rancangan")
        assign_unique_para_ids(source)

        new_elements.extend([subheading, description, image_paragraph, caption, source])

    replace_range(body, intro_idx, source_idx, new_elements)

    for rid, target in new_relationships:
        add_relationship(relroot, rid, target)

    return new_relationships, new_bookmarks


def renumber_captions(root: ET.Element) -> tuple[list[dict], list[dict], dict[int, int], dict[int, int]]:
    body = root.find("w:body", NS)
    in_bab4 = False
    figure_no = 0
    table_no = 0
    figure_entries: list[dict] = []
    table_entries: list[dict] = []
    figure_map: dict[int, int] = {}
    table_map: dict[int, int] = {}
    bookmark_id = get_max_bookmark_id(root)

    for child in list(body):
        text = child_type_and_text(child)
        if not in_bab4:
            if text.startswith("BAB IV"):
                in_bab4 = True
            continue
        if text.startswith("BAB V"):
            break

        if child.tag == f"{W}p":
            info = caption_label_and_number(text, "Gambar")
            if info:
                old_no, title = info
                figure_no += 1
                figure_map[old_no] = figure_no
                set_caption_text(child, f"Gambar 4.{figure_no} {title}")
                bookmark_name, bookmark_id = ensure_bookmark(
                    child, f"_CodexBab4Figure{figure_no}", bookmark_id + 1
                )
                figure_entries.append(
                    {
                        "label": f"Gambar 4.{figure_no} {title}",
                        "anchor": bookmark_name,
                        "title": title,
                    }
                )
                continue

            info = caption_label_and_number(text, "Tabel")
            if info:
                old_no, title = info
                table_no += 1
                table_map[old_no] = table_no
                set_caption_text(child, f"Tabel 4.{table_no} {title}")
                bookmark_name, bookmark_id = ensure_bookmark(
                    child, f"_CodexBab4Table{table_no}", bookmark_id + 1
                )
                table_entries.append(
                    {
                        "label": f"Tabel 4.{table_no} {title}",
                        "anchor": bookmark_name,
                        "title": title,
                    }
                )
                continue

        if child.tag == f"{W}tbl":
            caption_p = first_table_caption_paragraph(child)
            info = caption_label_and_number(para_text(caption_p), "Tabel")
            if info:
                old_no, title = info
                table_no += 1
                table_map[old_no] = table_no
                set_caption_text(caption_p, f"Tabel 4.{table_no} {title}")
                bookmark_name, bookmark_id = ensure_bookmark(
                    caption_p, f"_CodexBab4Table{table_no}", bookmark_id + 1
                )
                table_entries.append(
                    {
                        "label": f"Tabel 4.{table_no} {title}",
                        "anchor": bookmark_name,
                        "title": title,
                    }
                )

    return figure_entries, table_entries, figure_map, table_map


def update_explicit_refs(root: ET.Element, figure_map: dict[int, int], table_map: dict[int, int]) -> None:
    body = root.find("w:body", NS)
    in_bab4 = False
    for child in list(body):
        if child.tag != f"{W}p":
            continue
        text = para_text(child)
        if not in_bab4:
            if text.startswith("BAB IV"):
                in_bab4 = True
            continue
        if text.startswith("BAB V"):
            break
        if not text:
            continue
        if text.startswith("Gambar 4.") or text.startswith("Tabel 4."):
            continue
        if child.find(".//a:blip", NS) is not None:
            continue

        new_text = re.sub(
            r"Gambar 4\.(\d+)",
            lambda match: f"Gambar 4.{figure_map.get(int(match.group(1)), int(match.group(1)))}",
            text,
        )
        new_text = re.sub(
            r"Tabel 4\.(\d+)",
            lambda match: f"Tabel 4.{table_map.get(int(match.group(1)), int(match.group(1)))}",
            new_text,
        )
        if new_text != text:
            replace_paragraph_text(child, new_text)


def apply_manual_paragraph_updates(root: ET.Element) -> None:
    body = root.find("w:body", NS)
    replacements = {
        "Class Diagram merupakan": (
            "Class Diagram merupakan rancangan struktural yang menggambarkan kelas-kelas utama "
            "dalam sistem beserta atribut, metode, dan relasi antarkelas yang digunakan untuk "
            "mendukung proses pengelolaan data, penilaian, dan analisis. Rancangan tersebut "
            "ditunjukkan pada Gambar 4.31."
        ),
        "Rancangan struktur program dibedakan berdasarkan hak akses pengguna": (
            "Rancangan struktur program dibedakan berdasarkan hak akses pengguna, yaitu admin, "
            "atasan, karyawan, dan pimpinan. Setiap struktur menu memperlihatkan hubungan "
            "antarfitur yang dapat diakses oleh masing-masing peran sebagaimana ditunjukkan pada "
            "Gambar 4.32 sampai Gambar 4.35."
        ),
        "Struktur menu admin menggambarkan hubungan antar menu": (
            "Struktur menu admin menggambarkan hubungan antar menu yang dapat diakses oleh admin, "
            "meliputi dashboard, data karyawan, atur relasi atasan, kriteria penilaian, periode "
            "penilaian, rekap penilaian, analisa kinerja, laporan, dan logout. Susunan menu "
            "tersebut ditunjukkan pada Gambar 4.32."
        ),
        "Struktur menu atasan menggambarkan susunan menu": (
            "Struktur menu atasan menggambarkan susunan menu yang mendukung proses penilaian "
            "bawahan, meliputi dashboard, penilaian bawahan, review penilaian, daftar bawahan, "
            "dan logout. Susunan menu tersebut ditunjukkan pada Gambar 4.33."
        ),
        "Struktur menu karyawan menggambarkan menu utama": (
            "Struktur menu karyawan menggambarkan menu utama yang dapat diakses karyawan, "
            "meliputi dashboard, rekap penilaian, dan logout. Susunan menu tersebut ditunjukkan "
            "pada Gambar 4.34."
        ),
        "Struktur menu pimpinan menggambarkan susunan menu": (
            "Struktur menu pimpinan menggambarkan susunan menu yang tersedia bagi pimpinan, "
            "meliputi dashboard, laporan, dan logout. Susunan menu tersebut ditunjukkan pada "
            "Gambar 4.35."
        ),
        "Rancangan database akan digunakan untuk menyimpan": (
            "Rancangan database digunakan untuk menyimpan data-data penting yang mendukung "
            "kinerja sistem. Struktur tabel database yang digunakan pada sistem ini disajikan "
            "pada Tabel 4.21 sampai Tabel 4.25."
        ),
        "Rancangan halaman login merupakan tampilan awal": (
            "Rancangan halaman login merupakan tampilan awal yang digunakan pengguna untuk masuk "
            "ke dalam sistem sesuai hak akses yang dimiliki. Adapun rancangan form login "
            "ditunjukkan pada Gambar 4.36."
        ),
        "Rancangan halaman dashboard menyajikan ringkasan": (
            "Rancangan halaman dashboard menyajikan ringkasan informasi utama, statistik sistem, "
            "serta akses cepat ke menu-menu penting. Tampilan dashboard dapat dilihat pada "
            "Gambar 4.37."
        ),
        "Rancangan halaman data karyawan digunakan": (
            "Rancangan halaman data karyawan digunakan untuk mengelola informasi pegawai yang "
            "menjadi dasar proses penilaian. Pada halaman ini disediakan form input dan tabel "
            "data karyawan sebagaimana ditunjukkan pada Gambar 4.38."
        ),
        "Rancangan halaman relasi atasan berfungsi": (
            "Rancangan halaman relasi atasan berfungsi untuk menetapkan hubungan antara karyawan "
            "bawahan dan pihak atasan yang melakukan penilaian. Pengaturan relasi tersebut "
            "ditampilkan pada Gambar 4.39."
        ),
        "Rancangan halaman kriteria penilaian digunakan": (
            "Rancangan halaman kriteria penilaian digunakan untuk menambahkan, memperbarui, dan "
            "mengatur bobot kriteria yang dipakai dalam proses penilaian. Adapun rancangan "
            "halaman tersebut ditunjukkan pada Gambar 4.40."
        ),
        "Rancangan halaman periode penilaian berfungsi": (
            "Rancangan halaman periode penilaian berfungsi untuk mengatur masa penilaian yang "
            "sedang aktif agar proses evaluasi berjalan sesuai jadwal. Susunan pengelolaan "
            "periode dapat dilihat pada Gambar 4.41."
        ),
        "Rancangan halaman penilaian bawahan menampilkan": (
            "Rancangan halaman penilaian bawahan menampilkan daftar pegawai yang akan dinilai "
            "beserta form pengisian nilai per kriteria. Tampilan halaman penilaian ini "
            "disajikan pada Gambar 4.42."
        ),
        "Rancangan halaman review penilaian digunakan": (
            "Rancangan halaman review penilaian digunakan untuk meninjau kembali data penilaian "
            "yang telah disimpan beserta detail hasilnya. Susunan halaman review tersebut "
            "ditunjukkan pada Gambar 4.43."
        ),
        "Rancangan halaman daftar bawahan menyajikan": (
            "Rancangan halaman daftar bawahan menyajikan data bawahan langsung yang menjadi "
            "tanggung jawab atasan pada proses penilaian. Tampilan halaman ini dapat diamati "
            "pada Gambar 4.44."
        ),
        "Rancangan halaman rekap penilaian menampilkan": (
            "Rancangan halaman rekap penilaian menampilkan ringkasan hasil evaluasi setiap "
            "karyawan beserta detail nilai per kriteria. Rancangan rekap penilaian ditunjukkan "
            "pada Gambar 4.45."
        ),
        "Rancangan halaman analisa kinerja digunakan": (
            "Rancangan halaman analisa kinerja digunakan untuk memproses data penilaian dengan "
            "metode KNN hingga menghasilkan klasifikasi akhir. Tampilan proses dan hasil analisa "
            "disajikan pada Gambar 4.46."
        ),
        "Rancangan halaman laporan digunakan": (
            "Rancangan halaman laporan digunakan untuk menampilkan hasil rekap data dalam bentuk "
            "filter, pratinjau, dan opsi pencetakan laporan. Adapun tampilan halaman laporan "
            "dapat dilihat pada Gambar 4.47."
        ),
        "Flowchart program admin menjelaskan": (
            "Flowchart program admin menjelaskan alur kerja pengelolaan sistem, mulai dari "
            "login, pemilihan menu utama, pengelolaan data karyawan, pengaturan relasi atasan, "
            "pengelolaan kriteria dan periode penilaian, rekap penilaian, analisa kinerja, "
            "laporan, sampai logout. Alur tersebut ditunjukkan pada Gambar 4.48."
        ),
        "Flowchart program atasan memaparkan": (
            "Flowchart program atasan memaparkan alur penilaian kinerja bawahan, mulai dari "
            "login, melihat daftar bawahan, mengisi penilaian, melakukan review, hingga keluar "
            "dari sistem. Alur tersebut ditunjukkan pada Gambar 4.49."
        ),
        "Flowchart program karyawan menunjukkan": (
            "Flowchart program karyawan menunjukkan alur akses karyawan dalam melihat dashboard "
            "dan rekap penilaian pribadi setelah berhasil login ke sistem. Alur tersebut "
            "ditunjukkan pada Gambar 4.50."
        ),
        "Flowchart program pimpinan menggambarkan": (
            "Flowchart program pimpinan menggambarkan alur penggunaan sistem oleh pimpinan, "
            "dimulai dari proses login, akses dashboard, pembukaan menu laporan, pemilihan jenis "
            "rekap, hingga proses logout. Alur tersebut ditunjukkan pada Gambar 4.51."
        ),
        "Tampilan login merupakan halaman awal": (
            "Tampilan login merupakan halaman awal yang digunakan oleh pengguna untuk masuk ke "
            "dalam sistem dengan memasukkan username dan password yang telah terdaftar. Halaman "
            "ini menjadi gerbang autentikasi sebelum pengguna memperoleh akses ke fitur sesuai "
            "peran masing-masing, sebagaimana ditunjukkan pada Gambar 4.52."
        ),
    }

    in_bab4 = False
    for child in list(body):
        if child.tag != f"{W}p":
            continue
        text = normalize_spaces(para_text(child))
        if not in_bab4:
            if text.startswith("BAB IV"):
                in_bab4 = True
            continue
        if text.startswith("BAB V"):
            break
        for prefix, replacement in replacements.items():
            if text.startswith(prefix):
                replace_paragraph_text(child, replacement)
                break


def bold_database_ids(root: ET.Element) -> None:
    body = root.find("w:body", NS)
    targets = {"id", "id_karyawan", "id_atasan", "periode_id", "karyawan_id", "penilai_id"}
    in_database = False

    for child in list(body):
        if child.tag == f"{W}p":
            text = normalize_spaces(para_text(child))
            if text.startswith("Rancangan Database"):
                in_database = True
                continue
            if in_database and text.startswith("Rancangan Tampilan Antar Muka"):
                break
            continue

        if not in_database or child.tag != f"{W}tbl":
            continue

        rows = child.findall("w:tr", NS)
        for row in rows[2:]:
            cells = row.findall("w:tc", NS)
            if len(cells) < 2:
                continue
            field_value = normalize_spaces(cell_text(cells[1])).replace(" ", "").lower()
            if field_value not in targets:
                continue
            for run in cells[1].findall(".//w:r", NS):
                ensure_run_bold(run)


def rebuild_tof(
    root: ET.Element,
    figure_entries: list[dict],
    table_entries: list[dict],
) -> None:
    body = root.find("w:body", NS)
    children = list(body)

    fig_start = fig_end = None
    tbl_start = tbl_end = None
    figure_template = None
    table_template = None
    fig_page_lookup: dict[str, str] = {}
    tbl_page_lookup: dict[str, str] = {}

    for idx, child in enumerate(children):
        text = child_type_and_text(child)
        if child.tag == f"{W}p" and text.startswith("Gambar 4."):
            if 180 <= idx <= 242:
                fig_start = idx if fig_start is None else fig_start
                fig_end = idx
                figure_template = figure_template or copy.deepcopy(child)
                hyperlink = child.find("w:hyperlink", NS)
                if hyperlink is not None:
                    anchor = hyperlink.get("anchor") or hyperlink.get(f"{W}anchor")
                    page = ""
                    seen_separate = False
                    for run in hyperlink.findall("w:r", NS):
                        fld = run.find("w:fldChar", NS)
                        if fld is not None and fld.get(f"{W}fldCharType") == "separate":
                            seen_separate = True
                            continue
                        if seen_separate:
                            t = run.find("w:t", NS)
                            if t is not None:
                                page = t.text or ""
                                break
                    if anchor:
                        fig_page_lookup[anchor] = page
        if child.tag == f"{W}p" and text.startswith("Tabel 4."):
            if 250 <= idx <= 276:
                tbl_start = idx if tbl_start is None else tbl_start
                tbl_end = idx
                table_template = table_template or copy.deepcopy(child)
                hyperlink = child.find("w:hyperlink", NS)
                if hyperlink is not None:
                    anchor = hyperlink.get("anchor") or hyperlink.get(f"{W}anchor")
                    page = ""
                    seen_separate = False
                    for run in hyperlink.findall("w:r", NS):
                        fld = run.find("w:fldChar", NS)
                        if fld is not None and fld.get(f"{W}fldCharType") == "separate":
                            seen_separate = True
                            continue
                        if seen_separate:
                            t = run.find("w:t", NS)
                            if t is not None:
                                page = t.text or ""
                                break
                    if anchor:
                        tbl_page_lookup[anchor] = page

    if fig_start is None or fig_end is None or tbl_start is None or tbl_end is None:
        return

    new_figure_entries = []
    last_page = "47"
    for entry in figure_entries:
        paragraph = copy.deepcopy(figure_template)
        assign_unique_para_ids(paragraph)
        page = guess_new_page(
            entry["label"],
            fig_page_lookup.get(entry["anchor"]),
            last_page,
        )
        last_page = page
        set_hyperlink_label_and_page(paragraph, entry["label"], entry["anchor"], page)
        new_figure_entries.append(paragraph)

    new_table_entries = []
    last_page = "30"
    for entry in table_entries:
        paragraph = copy.deepcopy(table_template)
        assign_unique_para_ids(paragraph)
        page = guess_new_page(
            entry["label"],
            tbl_page_lookup.get(entry["anchor"]),
            last_page,
        )
        last_page = page
        set_hyperlink_label_and_page(paragraph, entry["label"], entry["anchor"], page)
        new_table_entries.append(paragraph)

    replace_range(body, fig_start, fig_end, new_figure_entries)
    body = root.find("w:body", NS)
    children = list(body)
    # After replacing figure TOF, table indexes may have shifted, so re-locate them.
    tbl_indices = [
        idx
        for idx, child in enumerate(children)
        if child.tag == f"{W}p"
        and 250 <= idx <= 285
        and child_type_and_text(child).startswith("Tabel 4.")
    ]
    replace_range(body, min(tbl_indices), max(tbl_indices), new_table_entries)


def enable_update_fields(settings_root: ET.Element) -> None:
    if settings_root.find("w:updateFields", NS) is None:
        elem = ET.Element(f"{W}updateFields")
        elem.set(f"{W}val", "true")
        settings_root.append(elem)


def patch_docx(docx_path: Path) -> None:
    with tempfile.TemporaryDirectory() as tmp_dir_str:
        tmp_dir = Path(tmp_dir_str)
        extract_dir = tmp_dir / "docx"
        extract_dir.mkdir()

        with zipfile.ZipFile(docx_path, "r") as zip_in:
            zip_in.extractall(extract_dir)

        document_xml = extract_dir / "word" / "document.xml"
        rels_xml = extract_dir / "word" / "_rels" / "document.xml.rels"
        settings_xml = extract_dir / "word" / "settings.xml"
        media_dir = extract_dir / "word" / "media"

        root = ET.parse(document_xml).getroot()
        relroot = ET.parse(rels_xml).getroot()
        settings_root = ET.parse(settings_xml).getroot()

        insert_structure_section(root, relroot, media_dir)

        for target, source in FLOWCHART_IMAGES.items():
            shutil.copyfile(source, extract_dir / "word" / target.split("/", 1)[1])

        figure_entries, table_entries, figure_map, table_map = renumber_captions(root)
        update_explicit_refs(root, figure_map, table_map)
        apply_manual_paragraph_updates(root)
        bold_database_ids(root)
        rebuild_tof(root, figure_entries, table_entries)
        enable_update_fields(settings_root)

        ET.ElementTree(root).write(document_xml, encoding="utf-8", xml_declaration=True)
        ET.ElementTree(relroot).write(rels_xml, encoding="utf-8", xml_declaration=True)
        ET.ElementTree(settings_root).write(settings_xml, encoding="utf-8", xml_declaration=True)

        output_path = tmp_dir / docx_path.name
        with zipfile.ZipFile(output_path, "w", compression=zipfile.ZIP_DEFLATED) as zip_out:
            for file_path in extract_dir.rglob("*"):
                if file_path.is_file():
                    arcname = file_path.relative_to(extract_dir).as_posix()
                    zip_out.write(file_path, arcname)

        shutil.copyfile(output_path, docx_path)


def main() -> None:
    patch_docx(DOCX_PATH)


if __name__ == "__main__":
    main()
