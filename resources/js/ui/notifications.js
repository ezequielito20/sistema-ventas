const TITLES = {
    success: "Operacion exitosa",
    error: "Ha ocurrido un error",
    warning: "Atencion",
    info: "Informacion",
};

const ICONS = {
    success: "check-circle",
    error: "times-circle",
    warning: "exclamation-triangle",
    info: "info-circle",
};

const TOAST_BG = {
    success: "linear-gradient(135deg, #16a34a 0%, #15803d 100%)",
    error: "linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)",
    warning: "linear-gradient(135deg, #d97706 0%, #b45309 100%)",
    info: "linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)",
};

function normalizeType(type = "info") {
    return Object.prototype.hasOwnProperty.call(TITLES, type) ? type : "info";
}

function hasSwal() {
    return typeof window !== "undefined" && typeof window.Swal !== "undefined";
}

function ensureToastContainer() {
    let container = document.getElementById("ui-toast-container");
    if (container) {
        return container;
    }

    container = document.createElement("div");
    container.id = "ui-toast-container";
    container.className = "ui-toast-container";
    document.body.appendChild(container);
    return container;
}

function buildToast({ title, message, type }) {
    const toast = document.createElement("div");
    toast.className = "ui-toast";
    toast.setAttribute("role", "status");
    toast.setAttribute("aria-live", "polite");
    toast.style.background = TOAST_BG[type];

    toast.innerHTML = `
        <div class="ui-toast__icon">
            <i class="fas fa-${ICONS[type]}" aria-hidden="true"></i>
        </div>
        <div class="ui-toast__content">
            <p class="ui-toast__title">${title}</p>
            <p class="ui-toast__message">${message}</p>
        </div>
        <button type="button" class="ui-toast__close" aria-label="Cerrar notificacion">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    `;

    const closeButton = toast.querySelector(".ui-toast__close");
    closeButton?.addEventListener("click", () => {
        toast.classList.add("is-leaving");
        window.setTimeout(() => toast.remove(), 220);
    });

    return toast;
}

export function showToast(message, options = {}) {
    const type = normalizeType(options.type || "info");
    const title = options.title || TITLES[type];
    const timeout = Number.isFinite(options.timeout) ? options.timeout : 3800;

    if (!message) {
        return;
    }

    const container = ensureToastContainer();
    const toast = buildToast({ title, message, type });
    container.appendChild(toast);

    window.requestAnimationFrame(() => {
        toast.classList.add("is-visible");
    });

    window.setTimeout(() => {
        toast.classList.add("is-leaving");
        window.setTimeout(() => toast.remove(), 220);
    }, Math.max(timeout, 1200));
}

export async function confirmDialog(options = {}) {
    const normalizedType = normalizeType(options.type || "warning");
    const title = options.title || "Confirmar accion";
    const text = options.text || "Esta accion no se puede deshacer.";
    const confirmText = options.confirmText || "Si, continuar";
    const cancelText = options.cancelText || "Cancelar";

    if (hasSwal()) {
        const result = await window.Swal.fire({
            title,
            text,
            icon: normalizedType,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: true,
            focusCancel: true,
            customClass: {
                popup: "ui-swal-popup",
                title: "ui-swal-title",
                confirmButton: "ui-swal-confirm",
                cancelButton: "ui-swal-cancel",
            },
        });
        return Boolean(result.isConfirmed);
    }

    return window.confirm(`${title}\n\n${text}`);
}

export async function alertDialog(options = {}) {
    const normalizedType = normalizeType(options.type || "info");
    const title = options.title || TITLES[normalizedType];
    const text = options.text || "";
    const confirmText = options.confirmText || "Entendido";

    if (hasSwal()) {
        await window.Swal.fire({
            title,
            text,
            icon: normalizedType,
            confirmButtonText: confirmText,
            customClass: {
                popup: "ui-swal-popup",
                title: "ui-swal-title",
                confirmButton: "ui-swal-confirm",
            },
        });
        return;
    }

    window.alert(`${title}\n\n${text}`);
}

export const notifications = {
    showToast,
    confirmDialog,
    alertDialog,
};
