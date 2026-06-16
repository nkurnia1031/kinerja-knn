from __future__ import annotations

import copy
import re
import shutil
import tempfile
import zipfile
from pathlib import Path
import xml.etree.ElementTree as ET


DOCX_PATH = Path(
    r"d:\Laragon\www\2026\kinerja-knn\KLASIFIKASI KINERJA KARYAWAN TAHUNAN MENGGUNAKAN METODE K.docx"
)

NS = {
    "w": "http://schemas.openxmlformats.org/wordprocessingml/2006/main",
    "w14": "http://schemas.microsoft.com/office/word/2010/wordml",
}

for prefix, uri in NS.items():
    ET.register_namespace(prefix, uri)

W = f"{{{NS['w']}}}"
XML_SPACE = "{http://www.w3.org/XML/1998/namespace}space"

FOREIGN_PATTERNS = [
    r"\bK-Nearest Neighbor\b",
    r"\bEuclidean Distance\b",
    r"\bActivity Diagram\b",
    r"\bSequence Diagram\b",
    r"\bClass Diagram\b",
    r"\buse case\b",
    r"\bprimary key\b",
    r"\bTeam Leader\b",
    r"\bflowchart\b",
    r"\bdashboard\b",
    r"\blogin\b",
    r"\blogout\b",
    r"\breview\b",
    r"\bfilter\b",
    r"\bform\b",
    r"\binput\b",
    r"\boutput\b",
    r"\bdatabase\b",
    r"\busername\b",
    r"\bpassword\b",
    r"\bwidget\b",
    r"\bpreview\b",
    r"\bmodal\b",
    r"\bprogress\b",
    r"\bfield\b",
    r"\brole\b",
    r"\bstatus\b",
    r"\btraining\b",
    r"\bmessage\b",
    r"\bsequence\b",
]
FOREIGN_RE = re.compile("|".join(f"(?:{pattern})" for pattern in FOREIGN_PATTERNS), re.I)


def full_text(element: ET.Element) -> str:
    return "".join(t.text or "" for t in element.findall(".//w:t", NS))


def has_italic_run(element: ET.Element) -> bool:
    return any(run.find("w:rPr/w:i", NS) is not None for run in element.findall(".//w:r", NS))


def set_text_node(node: ET.Element, text: str) -> None:
    node.text = text
    if text.startswith(" ") or text.endswith(" "):
        node.set(XML_SPACE, "preserve")
    elif XML_SPACE in node.attrib:
        del node.attrib[XML_SPACE]


def strip_italic(rpr: ET.Element) -> None:
    for tag in ("i", "iCs"):
        node = rpr.find(f"w:{tag}", NS)
        if node is not None:
            rpr.remove(node)


def first_text_run(element: ET.Element) -> ET.Element | None:
    for run in element.findall(".//w:r", NS):
        if full_text(run):
            return run
    return None


def build_run(text: str, template_run: ET.Element | None, paragraph: ET.Element, italic: bool) -> ET.Element:
    run = ET.Element(f"{W}r")

    source_rpr = None
    if template_run is not None:
        source_rpr = template_run.find("w:rPr", NS)
    if source_rpr is None:
        source_rpr = paragraph.find("w:pPr/w:rPr", NS)
    if source_rpr is not None:
        rpr = copy.deepcopy(source_rpr)
        strip_italic(rpr)
        if italic:
            rpr.append(ET.Element(f"{W}i"))
            rpr.append(ET.Element(f"{W}iCs"))
        run.append(rpr)
    elif italic:
        rpr = ET.Element(f"{W}rPr")
        rpr.append(ET.Element(f"{W}i"))
        rpr.append(ET.Element(f"{W}iCs"))
        run.append(rpr)

    t = ET.Element(f"{W}t")
    set_text_node(t, text)
    run.append(t)
    return run


def build_segments(text: str) -> list[tuple[str, bool]]:
    segments: list[tuple[str, bool]] = []
    cursor = 0
    for match in FOREIGN_RE.finditer(text):
        start, end = match.span()
        if start > cursor:
            segments.append((text[cursor:start], False))
        segments.append((text[start:end], True))
        cursor = end
    if cursor < len(text):
        segments.append((text[cursor:], False))

    if not segments:
        return [(text, False)]

    merged: list[tuple[str, bool]] = []
    for seg_text, seg_italic in segments:
        if not seg_text:
            continue
        if merged and merged[-1][1] == seg_italic:
            merged[-1] = (merged[-1][0] + seg_text, seg_italic)
        else:
            merged.append((seg_text, seg_italic))
    return merged


def should_process_text(text: str, element: ET.Element) -> bool:
    if not text.strip():
        return False
    return bool(FOREIGN_RE.search(text) or has_italic_run(element))


def clear_text_children(paragraph: ET.Element) -> None:
    keep_tags = {
        f"{W}pPr",
        f"{W}bookmarkStart",
        f"{W}bookmarkEnd",
        f"{W}permStart",
        f"{W}permEnd",
    }
    for child in list(paragraph):
        if child.tag not in keep_tags:
            paragraph.remove(child)


def insertion_index(paragraph: ET.Element) -> int:
    idx = 0
    while idx < len(paragraph) and paragraph[idx].tag in {f"{W}pPr", f"{W}bookmarkStart", f"{W}permStart"}:
        idx += 1
    return idx


def format_paragraph(paragraph: ET.Element) -> bool:
    if paragraph.find(".//w:hyperlink", NS) is not None:
        return False
    if paragraph.find(".//w:drawing", NS) is not None and not full_text(paragraph).strip():
        return False

    text = full_text(paragraph)
    if not should_process_text(text, paragraph):
        return False

    template_run = first_text_run(paragraph)
    segments = build_segments(text)

    clear_text_children(paragraph)
    idx = insertion_index(paragraph)
    for offset, (seg_text, italic) in enumerate(segments):
        paragraph.insert(idx + offset, build_run(seg_text, template_run, paragraph, italic))
    return True


def visible_hyperlink_label(hyperlink: ET.Element) -> str:
    chunks: list[str] = []
    for run in hyperlink.findall("w:r", NS):
        if run.find("w:tab", NS) is not None:
            break
        if run.find("w:fldChar", NS) is not None or run.find("w:instrText", NS) is not None:
            break
        chunks.append(full_text(run))
    return "".join(chunks)


def format_hyperlink_paragraph(paragraph: ET.Element) -> bool:
    hyperlink = paragraph.find("w:hyperlink", NS)
    if hyperlink is None:
        return False

    label = visible_hyperlink_label(hyperlink)
    if not label or not should_process_text(label, hyperlink):
        return False

    template_run = None
    visible_runs: list[ET.Element] = []
    control_start = None
    children = list(hyperlink)
    for idx, child in enumerate(children):
        if child.tag != f"{W}r":
            control_start = idx
            break
        if child.find("w:tab", NS) is not None or child.find("w:fldChar", NS) is not None or child.find("w:instrText", NS) is not None:
            control_start = idx
            break
        if full_text(child):
            visible_runs.append(child)
            if template_run is None:
                template_run = child

    if control_start is None:
        control_start = len(children)

    for child in visible_runs:
        hyperlink.remove(child)

    segments = build_segments(label)
    for offset, (seg_text, italic) in enumerate(segments):
        hyperlink.insert(offset, build_run(seg_text, template_run, paragraph, italic))
    return True


def iter_bab4_elements(body: ET.Element) -> list[ET.Element]:
    in_bab4 = False
    selected: list[ET.Element] = []
    for child in list(body):
        text = full_text(child).strip()
        if not in_bab4:
            if text.startswith("BAB IV"):
                in_bab4 = True
            continue
        if text.startswith("BAB V"):
            break
        selected.append(child)
    return selected


def patch_docx(docx_path: Path) -> None:
    with tempfile.TemporaryDirectory() as tmp_dir_str:
        tmp_dir = Path(tmp_dir_str)
        extract_dir = tmp_dir / "docx"
        extract_dir.mkdir()

        with zipfile.ZipFile(docx_path, "r") as zip_in:
            zip_in.extractall(extract_dir)

        document_xml = extract_dir / "word" / "document.xml"
        root = ET.parse(document_xml).getroot()
        body = root.find("w:body", NS)

        # Bab 4 body and captions.
        for child in iter_bab4_elements(body):
            if child.tag == f"{W}p":
                format_paragraph(child)
            elif child.tag == f"{W}tbl":
                for paragraph in child.findall(".//w:p", NS):
                    format_paragraph(paragraph)

        # Daftar gambar/tabel for chapter 4 only.
        for paragraph in body.findall("w:p", NS):
            text = full_text(paragraph).strip()
            if text.startswith("Gambar 4.") or text.startswith("Tabel 4."):
                format_hyperlink_paragraph(paragraph)

        ET.ElementTree(root).write(document_xml, encoding="utf-8", xml_declaration=True)

        output_path = tmp_dir / docx_path.name
        with zipfile.ZipFile(output_path, "w", compression=zipfile.ZIP_DEFLATED) as zip_out:
            for file_path in extract_dir.rglob("*"):
                if file_path.is_file():
                    zip_out.write(file_path, file_path.relative_to(extract_dir).as_posix())

        shutil.copyfile(output_path, docx_path)


def main() -> None:
    patch_docx(DOCX_PATH)


if __name__ == "__main__":
    main()
