from pathlib import Path
path = Path('c:/Users/User/AppData/Roaming/Code/User/workspaceStorage/2c3fac23b6bd8f44f48adeecaa958d45/GitHub.copilot-chat/chat-session-resources/458f1746-2631-4411-b476-6b7243bce880/call_5pGoeqK4FIJusCWHw9cZQWQn__vscode-1782372584675/content.txt')
text = path.read_text('utf-16')
for term in ['The email field is required.', 'The password field is required.', '<ul class="mb-0 ps-3 small">', '<li>']:
    print('TERM:', term, 'FOUND', text.find(term))
    idx = text.find(term)
    if idx != -1:
        start = max(0, idx-260)
        end = min(len(text), idx+260)
        print(text[start:end].replace('\r\n','\n'))
        print('---')
