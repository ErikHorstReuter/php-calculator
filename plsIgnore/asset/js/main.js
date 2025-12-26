
const term = document.getElementById('term');
const win = document.getElementById('win');
const themeName = document.getElementById('themeName');

const state = {
    user: 'root',
    host: 'web',
    cwd: '~/project',
    history: [],
    histIndex: 0,
    fs: {
        'readme.txt': 'Willkommen im Web-Terminal.\nTippe "help" für Befehle.',
        'todo.txt': '- ship it\n- coffee\n- repeat',
        'about.txt': 'Das ist ein HTML/CSS/JS-Terminal-Look. Kein echtes Terminal.'
    }
};

function promptText(){
    return `${state.user}@${state.host}`;
}
function pathText(){
    return `${state.cwd}`;
}

function scrollToBottom(){
    term.scrollTop = term.scrollHeight;
}

function addLine({prompt=false, text='', cls='' }){
    const line = document.createElement('div');
    line.className = 'line';

    if(prompt){
        const p = document.createElement('span');
        p.className = 'prompt';
        p.textContent = promptText();

        const path = document.createElement('span');
        path.className = 'path';
        path.textContent = ' ' + pathText();

        const dollar = document.createElement('span');
        dollar.className = 'dollar';
        dollar.textContent = ' ❯';

        line.appendChild(p);
        line.appendChild(path);
        line.appendChild(dollar);
    } else {
        // keep alignment with prompt width-ish
        const spacer = document.createElement('span');
        spacer.style.width = '0px';
        line.appendChild(spacer);
    }

    const out = document.createElement('span');
    out.className = (prompt ? '' : 'output ') + cls;
    out.textContent = text;
    line.appendChild(out);

    term.appendChild(line);
    scrollToBottom();
}

function addInputRow(){
    const row = document.createElement('div');
    row.className = 'inputRow';

    const p = document.createElement('span');
    p.className = 'prompt';
    p.textContent = promptText();

    const path = document.createElement('span');
    path.className = 'path';
    path.textContent = ' ' + pathText();

    const dollar = document.createElement('span');
    dollar.className = 'dollar';
    dollar.textContent = ' ❯';

    const input = document.createElement('input');
    input.className = 'cmd';
    input.autocomplete = 'off';
    input.spellcheck = false;
    input.setAttribute('aria-label', 'Terminal command input');

    row.appendChild(p);
    row.appendChild(path);
    row.appendChild(dollar);
    row.appendChild(input);

    term.appendChild(row);
    input.focus();
    scrollToBottom();

    // click to focus
    term.addEventListener('mousedown', () => input.focus(), { once:false });

    input.addEventListener('keydown', (e) => {
        // Ctrl+L -> clear
        if (e.ctrlKey && (e.key === 'l' || e.key === 'L')) {
            e.preventDefault();
            runCommand('clear');
            return;
        }

        if (e.key === 'Enter') {
            e.preventDefault();
            const cmd = input.value;
            row.removeChild(input);
            const typed = document.createElement('span');
            typed.className = 'output';
            typed.textContent = cmd;
            row.appendChild(typed);

            handle(cmd);
            return;
        }

        // history
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (!state.history.length) return;
            state.histIndex = Math.max(0, state.histIndex - 1);
            input.value = state.history[state.histIndex] ?? '';
            return;
        }
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!state.history.length) return;
            state.histIndex = Math.min(state.history.length, state.histIndex + 1);
            input.value = state.history[state.histIndex] ?? '';
            return;
        }

        // Tab autocomplete (very simple)
        if (e.key === 'Tab') {
            e.preventDefault();
            const v = input.value.trim();
            if (!v) return;
            const cmds = Object.keys(commands);
            const matches = cmds.filter(c => c.startsWith(v));
            if (matches.length === 1) input.value = matches[0] + ' ';
        }
    });
}

function tokenize(s){
    // minimal: split by space, keep quoted strings
    const out = [];
    let cur = '';
    let q = null;
    for (let i=0;i<s.length;i++){
        const ch = s[i];
        if ((ch === '"' || ch === "'")) {
            if (q === ch) { q = null; continue; }
            if (!q) { q = ch; continue; }
        }
        if (!q && /\s/.test(ch)) {
            if (cur) out.push(cur), cur='';
        } else {
            cur += ch;
        }
    }
    if (cur) out.push(cur);
    return out;
}

const commands = {
    help(){
        addLine({ text:
                `Commands:
  help                diese Hilfe
  clear               Bildschirm leeren (auch Ctrl+L)
  echo <text>          Text ausgeben
  date                Datum/Zeit anzeigen
  whoami              Benutzer anzeigen
  ls                  Dateien listen
  cat <file>          Datei anzeigen
  theme [dark|light]  Theme umschalten
  exit                "Session" beenden (zeigt nur eine Meldung)

Tip: Pfeiltasten ↑/↓ für History, Tab für Autocomplete.` , cls:'muted' });
    },
    clear(){
        term.innerHTML = '';
    },
    echo(args){
        addLine({ text: args.join(' ') });
    },
    date(){
        addLine({ text: new Date().toString(), cls:'muted' });
    },
    whoami(){
        addLine({ text: state.user, cls:'muted' });
    },
    ls(){
        const files = Object.keys(state.fs).sort();
        addLine({ text: files.join('  '), cls:'muted' });
    },
    cat(args){
        const name = args[0];
        if (!name) {
            addLine({ text: 'cat: missing filename', cls:'err' });
            return;
        }
        if (!(name in state.fs)) {
            addLine({ text: `cat: ${name}: No such file`, cls:'err' });
            return;
        }
        addLine({ text: state.fs[name] });
    },
    theme(args){
        const mode = (args[0] || '').toLowerCase();
        if (mode && !['dark','light'].includes(mode)) {
            addLine({ text: 'theme: use "dark" oder "light"', cls:'warn' });
            return;
        }
        const wantLight = mode ? mode === 'light' : !win.classList.contains('light');
        win.classList.toggle('light', wantLight);
        themeName.textContent = wantLight ? 'light' : 'dark';
    },
    exit(){
        addLine({ text: 'Session beendet. (Nicht wirklich — reload die Seite.)', cls:'warn' });
    }
};

function runCommand(raw){
    const tokens = tokenize(raw.trim());
    const name = (tokens[0] || '').toLowerCase();
    const args = tokens.slice(1);

    if (!name) return;

    if (name !== 'clear' && state.history[state.history.length - 1] !== raw.trim()) {
        state.history.push(raw.trim());
    }
    state.histIndex = state.history.length;

    const fn = commands[name];
    if (!fn) {
        addLine({ text: `${name}: command not found`, cls:'err' });
        return;
    }
    fn(args);
}

function handle(raw){
    runCommand(raw);

    // next prompt
    addInputRow();
}

// boot
addLine({ text: 'web-terminal v1 — tippe "help"', cls:'muted' });
addInputRow();
