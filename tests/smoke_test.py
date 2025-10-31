from __future__ import annotations

from html.parser import HTMLParser
from pathlib import Path
from typing import Dict, List, Optional, Tuple


class SectionCollector(HTMLParser):
    def __init__(self) -> None:
        super().__init__()
        self.section_ids: set[str] = set()
        self.forms: List[Dict[str, str]] = []

    def handle_starttag(self, tag: str, attrs: List[Tuple[str, Optional[str]]]) -> None:
        attr_map = {name: value or "" for name, value in attrs}
        if tag == "section" and "id" in attr_map:
            self.section_ids.add(attr_map["id"])
        if tag == "form":
            self.forms.append(attr_map)


def main() -> None:
    html_path = Path(__file__).resolve().parents[1] / "index.html"
    html_content = html_path.read_text(encoding="utf-8")

    parser = SectionCollector()
    parser.feed(html_content)

    required_sections = {"historia", "sabores", "combos", "galeria", "contato"}
    missing = sorted(required_sections - parser.section_ids)
    if missing:
        raise SystemExit(f"Missing required sections: {', '.join(missing)}")

    if not parser.forms:
        raise SystemExit("Expected at least one form element for the contato section")

    print("All smoke tests passed.")


if __name__ == "__main__":
    main()
