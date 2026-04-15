const TITLES = {
    success: "Operacion exitosa",
    error: "Ha ocurrido un error",
    warning: "Atencion",
    info: "Informacion",
    critical: "Alerta critica",
};

const ICONS = {
    success: "sparkles",
    error: "triangle-exclamation",
    warning: "shield-exclamation",
    info: "satellite-dish",
    critical: "skull-crossbones",
};

const TYPE_ACCENT = {
    success: "#10b981",
    error: "#f43f5e",
    warning: "#f59e0b",
    info: "#22d3ee",
    critical: "#f43f5e",
};

function normalizeType(type = "info") {
    return Object.prototype.hasOwnProperty.call(TITLES, type) ? type : "info";
}

function hasSwal() {
    return typeof window !== "undefined" && typeof window.Swal !== "undefined";
}

function escapeHtml(value = "") {
    return String(value)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function ensureToastContainer() {
    let container = document.getElementById("ui-toast-container");
    if (container) return container;

    container = document.createElement("div");
    container.id = "ui-toast-container";
    container.className = "ui-toast-container";
    document.body.appendChild(container);
    return container;
}

function buildToast({
    title,
    message,
    subtitle = "",
    metadata = [],
    type = "info",
    timeout = 4200,
    theme = "futuristic",
}) {
    const toast = document.createElement("div");
    toast.className = `ui-toast ui-toast--${type} ui-toast--${theme}`;
    toast.setAttribute("role", "status");
    toast.setAttribute("aria-live", "polite");

    const metadataHtml = metadata.length
        ? `<div class="ui-toast__meta">${metadata
              .map((item) => `<span class="ui-toast__chip">${escapeHtml(item)}</span>`)
              .join("")}</div>`
        : "";

    toast.innerHTML = `
        <div class="ui-toast__glow"></div>
        <div class="ui-toast__icon">
            <i class="fas fa-${ICONS[type]}" aria-hidden="true"></i>
        </div>
        <div class="ui-toast__content">
            <p class="ui-toast__title">${escapeHtml(title)}</p>
            <p class="ui-toast__message">${escapeHtml(message)}</p>
            ${subtitle ? `<p class="ui-toast__subtitle">${escapeHtml(subtitle)}</p>` : ""}
            ${metadataHtml}
        </div>
        <button type="button" class="ui-toast__close" aria-label="Cerrar notificacion">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
        <div class="ui-toast__progress" style="animation-duration: ${Math.max(timeout, 1200)}ms"></div>
    `;

    toast.style.setProperty("--ui-toast-accent", TYPE_ACCENT[type]);

    const closeButton = toast.querySelector(".ui-toast__close");
    closeButton?.addEventListener("click", () => dismissToast(toast));
    return toast;
}

function dismissToast(toast) {
    if (!toast || toast.classList.contains("is-leaving")) return;
    toast.classList.add("is-leaving");
    window.setTimeout(() => toast.remove(), 240);
}

function buildRichDialogHtml({
    title,
    text,
    subtitle = "",
    highlight = "",
    metrics = [],
    items = [],
    type = "info",
}) {
    const metricHtml = metrics.length
        ? `<div class="ui-rich-dialog__metrics">${metrics
              .map(
                  (metric) => `
                    <div class="ui-rich-dialog__metric">
                        <span class="ui-rich-dialog__metric-label">${escapeHtml(metric.label || "")}</span>
                        <strong class="ui-rich-dialog__metric-value">${escapeHtml(metric.value || "-")}</strong>
                    </div>`
              )
              .join("")}</div>`
        : "";

    const itemsHtml = items.length
        ? `<ul class="ui-rich-dialog__list">${items
              .map((item) => `<li><i class="fas fa-circle"></i><span>${escapeHtml(item)}</span></li>`)
              .join("")}</ul>`
        : "";

    return `
        <section class="ui-rich-dialog ui-rich-dialog--${type}">
            <div class="ui-rich-dialog__orb"></div>
            <header class="ui-rich-dialog__header">
                <div class="ui-rich-dialog__icon"><i class="fas fa-${ICONS[type]}" aria-hidden="true"></i></div>
                <div>
                    <h3 class="ui-rich-dialog__title">${escapeHtml(title)}</h3>
                    ${subtitle ? `<p class="ui-rich-dialog__subtitle">${escapeHtml(subtitle)}</p>` : ""}
                </div>
            </header>
            <p class="ui-rich-dialog__text">${escapeHtml(text || "")}</p>
            ${highlight ? `<div class="ui-rich-dialog__highlight">${escapeHtml(highlight)}</div>` : ""}
            ${metricHtml}
            ${itemsHtml}
        </section>
    `;
}

export function showToast(message, options = {}) {
    const type = normalizeType(options.type || "info");
    const title = options.title || TITLES[type];
    const timeout = Number.isFinite(options.timeout) ? options.timeout : 4200;
    if (!message) return;

    const container = ensureToastContainer();
    const toast = buildToast({
        title,
        message,
        subtitle: options.subtitle,
        metadata: Array.isArray(options.metadata) ? options.metadata : [],
        type,
        timeout,
        theme: options.theme || "futuristic",
    });

    container.appendChild(toast);
    window.requestAnimationFrame(() => toast.classList.add("is-visible"));
    window.setTimeout(() => dismissToast(toast), Math.max(timeout, 1200));
}

export async function confirmDialog(options = {}) {
    const type = normalizeType(options.type || "warning");
    const title = options.title || "Confirmar accion";
    const text = options.text || "Esta accion no se puede deshacer.";
    const confirmText = options.confirmText || "Si, continuar";
    const cancelText = options.cancelText || "Cancelar";

    if (hasSwal()) {
        const result = await window.Swal.fire({
            title: "",
            html: buildRichDialogHtml({
                title,
                text,
                subtitle: options.subtitle || "Verifica la informacion antes de confirmar.",
                highlight: options.highlight || "",
                metrics: Array.isArray(options.metrics) ? options.metrics : [],
                items: Array.isArray(options.items) ? options.items : [],
                type,
            }),
            icon: undefined,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: true,
            focusCancel: true,
            customClass: {
                popup: "ui-swal-popup ui-swal-popup--futuristic",
                htmlContainer: "ui-swal-html",
                confirmButton: "ui-swal-confirm",
                cancelButton: "ui-swal-cancel",
            },
        });
        return Boolean(result.isConfirmed);
    }

    return window.confirm(`${title}\n\n${text}`);
}

export async function alertDialog(options = {}) {
    const type = normalizeType(options.type || "info");
    const title = options.title || TITLES[type];
    const text = options.text || "";
    const confirmText = options.confirmText || "Entendido";

    if (hasSwal()) {
        await window.Swal.fire({
            title: "",
            html: buildRichDialogHtml({
                title,
                text,
                subtitle: options.subtitle || "",
                highlight: options.highlight || "",
                metrics: Array.isArray(options.metrics) ? options.metrics : [],
                items: Array.isArray(options.items) ? options.items : [],
                type,
            }),
            icon: undefined,
            confirmButtonText: confirmText,
            customClass: {
                popup: "ui-swal-popup ui-swal-popup--futuristic",
                htmlContainer: "ui-swal-html",
                confirmButton: "ui-swal-confirm",
            },
        });
        return;
    }

    window.alert(`${title}\n\n${text}`);
}

export const notifications = { showToast, confirmDialog, alertDialog };
