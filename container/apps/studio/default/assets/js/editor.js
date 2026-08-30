const {default: OrganicEditor} = await import(new URL("../../../../../../organic/editor/organic-editor.min.js", import.meta.url).href);

const editors = new Map();

document.querySelectorAll("textarea[data-organic-editor]").forEach((textarea) => {
    const editor = OrganicEditor.init({
        target: textarea,
        preset: "full",
        mode: "document",
        placeholder: "Comece a escrever o conteúdo..."
    });
    editors.set(textarea.id, editor);
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
