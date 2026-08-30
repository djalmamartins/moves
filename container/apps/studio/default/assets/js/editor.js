const editors = new Map();
const editorModuleUrl = new URL("../../../../../../organic/editor/organic-editor.min.js", import.meta.url).href;
const uploadUrl = document.body.dataset.editorUpload;

const uploadImage = async (file) => {
    const csrf = document.querySelector('input[name="csrf"]')?.value;
    const data = new FormData();
    data.append("csrf", csrf || "");
    data.append("image", file, file.name);
    const response = await fetch(uploadUrl, {method: "POST", body: data, headers: {"X-Requested-With": "XMLHttpRequest"}});
    const result = await response.json().catch(() => ({}));
    if (!response.ok || !result.url) throw new Error(result.error || "Não foi possível enviar a imagem.");
    return {...result, size: file.size};
};

const editorButton = (label, title, action) => {
    const button = document.createElement("button");
    button.type = "button";
    button.className = "moves-organic-tool";
    button.textContent = label;
    button.title = title;
    button.setAttribute("aria-label", title);
    button.addEventListener("mousedown", (event) => event.preventDefault());
    button.addEventListener("click", action);
    return button;
};

const installToolbar = (editor, textarea) => {
    const toolbar = document.createElement("div");
    toolbar.className = "moves-organic-toolbar";
    toolbar.setAttribute("role", "toolbar");
    toolbar.setAttribute("aria-label", "Formatação do conteúdo");
    const block = document.createElement("select");
    block.title = "Formato do texto";
    [["p", "Parágrafo"], ["h2", "Título 2"], ["h3", "Título 3"], ["h4", "Título 4"], ["blockquote", "Citação"], ["pre", "Código"]].forEach(([value, label]) => block.add(new Option(label, value)));
    block.addEventListener("change", () => { editor.commands.formatBlock(block.value); editor.focus(); });
    toolbar.append(block);
    [["↶", "Desfazer", "undo"], ["↷", "Refazer", "redo"], ["B", "Negrito", "bold"], ["I", "Itálico", "italic"], ["U", "Sublinhado", "underline"], ["• Lista", "Lista com marcadores", "insertUnorderedList"], ["1. Lista", "Lista numerada", "insertOrderedList"], ["≡", "Alinhar à esquerda", "justifyLeft"], ["≣", "Centralizar", "justifyCenter"], ["☰", "Alinhar à direita", "justifyRight"]].forEach(([label, title, command]) => toolbar.append(editorButton(label, title, () => editor.execCommand(command))));
    toolbar.append(editorButton("🔗", "Inserir link", () => { editor.selection.restore(); const href = window.prompt("URL do link (https://...)"); if (href) editor.execCommand("createLink", href.trim()); }));
    const file = document.createElement("input");
    file.type = "file";
    file.accept = "image/jpeg,image/png,image/webp,image/gif";
    file.hidden = true;
    file.addEventListener("change", async () => {
        const selected = file.files?.[0];
        if (!selected) return;
        const button = toolbar.querySelector('[data-upload-tool="true"]');
        button?.classList.add("is-loading");
        try {
            const uploaded = await uploadImage(selected);
            editor.insertContent(`<p class="organic-image-block"><img src="${uploaded.url.replaceAll('"', '&quot;')}" alt="${selected.name.replaceAll('"', '&quot;')}" class="organic-content-image"></p><p><br></p>`);
        } catch (error) { window.alert(error.message); }
        finally { button?.classList.remove("is-loading"); file.value = ""; }
    });
    const imageButton = editorButton("▧", "Enviar imagem", () => file.click());
    imageButton.dataset.uploadTool = "true";
    toolbar.append(imageButton, file);
    toolbar.append(editorButton("</>", "Editar HTML", () => { const html = window.prompt("Edite o HTML do conteúdo:", editor.getContent()); if (html !== null) editor.setExternalContent(html); }));
    toolbar.append(editorButton("⛶", "Tela cheia", () => textarea.closest(".studio-panel")?.classList.toggle("moves-organic-fullscreen")));
    editor.element.before(toolbar);
};

let OrganicEditor;
try {
    ({default: OrganicEditor} = await import(editorModuleUrl));
} catch (error) {
    console.error("Organic Editor não pôde ser carregado.", error);
}

document.querySelectorAll("textarea[data-organic-editor]").forEach((textarea) => {
    if (!textarea.id) textarea.id = `organic-content-${editors.size + 1}`;
    if (!OrganicEditor) {
        textarea.hidden = false;
        textarea.removeAttribute("data-organic-editor");
        window.tinymce?.init({target: textarea, height: Number(textarea.dataset.editorHeight || 520), menubar: false, plugins: "link image lists table code fullscreen", toolbar: "undo redo | blocks | bold italic underline | bullist numlist | link image table | code fullscreen", relative_urls: false, remove_script_host: false});
        return;
    }
    const wasRequired = textarea.required;
    textarea.required = false;
    const editor = OrganicEditor.init({
        target: textarea,
        preset: "full",
        mode: "document",
        placeholder: "Comece a escrever o conteúdo...",
        upload: uploadImage,
        plugins: "image persistence clipboard visual pages"
    });
    editors.set(textarea.id, editor);
    installToolbar(editor, textarea);
    const form = textarea.form;
    form?.addEventListener("submit", (event) => {
        textarea.value = editor.getContent();
        const plain = textarea.value.replace(/<[^>]*>/g, "").replace(/&nbsp;/g, " ").trim();
        if (wasRequired && !plain && !textarea.value.match(/<(img|video|audio|iframe)\b/i)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            editor.focus();
            if (window.Organic?.Toast?.warning) window.Organic.Toast.warning("Preencha o conteúdo antes de salvar.");
            else window.alert("Preencha o conteúdo antes de salvar.");
        }
    }, true);
});

window.MovesOrganicEditor = {
    get(id) { return editors.get(id) || OrganicEditor.get(id) || null; },
    insert(id, html) {
        const editor = this.get(id);
        if (!editor) return false;
        editor.insertContent(html);
        return true;
    }
};
