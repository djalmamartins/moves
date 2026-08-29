(() => {
    const aliases = [
        "btn", "card", "card-header", "card-body", "form-group", "label",
        "input", "select", "textarea", "table-wrapper", "table", "tabs",
        "tab", "modal", "badge", "toast-region"
    ];

    const bridgeClasses = root => {
        root.querySelectorAll("[class]").forEach(element => {
            element.classList.forEach(className => {
                if (aliases.includes(className.slice(4))) {
                    element.classList.add(`organic-${className.slice(4)}`);
                }
            });
        });
    };

    const bridgeAttributes = root => {
        root.querySelectorAll("[data-org-modal], [data-org-modal-open], [data-org-modal-close], [data-org-tabs], [data-org-dropdown], [data-org-dropdown-toggle], [data-org-carousel], [data-org-carousel-prev], [data-org-carousel-next], [data-org-carousel-dot]").forEach(element => {
            Array.from(element.attributes).forEach(attribute => {
                if (!attribute.name.startsWith("data-org-")) return;
                const legacyName = attribute.name.replace("data-org-", "data-organic-");
                element.setAttribute(legacyName, attribute.value);
            });
        });
    };

    window.Organic = window.Organic || {};
    window.Organic.v2 = {
        bridge(root = document) {
            bridgeClasses(root);
            bridgeAttributes(root);
        }
    };

    window.Organic.ready(() => window.Organic.v2.bridge());
})();
