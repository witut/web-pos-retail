from html.parser import HTMLParser

class TagCounter(HTMLParser):
    def __init__(self):
        super().__init__()
        self.stack = []
        self.depth = 0

    def handle_starttag(self, tag, attrs):
        if tag == 'div':
            self.depth += 1
            self.stack.append((tag, self.getpos()[0]))

    def handle_endtag(self, tag):
        if tag == 'div':
            if not self.stack:
                print(f"EXTRA </{tag}> at line {self.getpos()[0]}")
            else:
                self.stack.pop()
            self.depth -= 1
            if self.depth == 0:
                print(f"Depth hits 0 at line {self.getpos()[0]}")

parser = TagCounter()
with open('resources/views/cashier/pos/index.blade.php', 'r') as f:
    content = f.read()

import re
content = re.sub(r'@[a-zA-Z]+(\([^)]*\))?', '', content)
content = re.sub(r'\{\{.*?\}\}', '', content)
content = re.sub(r'\{!!.*?!!\}', '', content)
    
parser.feed(content)
if parser.stack:
    print(f"Unclosed divs: {parser.stack}")
