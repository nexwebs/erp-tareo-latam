// resources/js/composables/useMaquinaModal.ts
import { ref } from 'vue';

export type ToastType = 'success' | 'error';

export function useMaquinaModal(paisRef: { value: string }) {
    const showToast     = ref(false);
    const toastMessage  = ref('');
    const toastType     = ref<ToastType>('success');
    const showModal     = ref(false);
    const showConfirm   = ref(false);
    const confirmAction = ref<(() => void) | null>(null);
    const confirmMessage = ref('');
    const modalData     = ref<Record<string, any> | null>(null);

    function toast(message: string, type: ToastType = 'success', duration = 3000) {
        toastMessage.value = message;
        toastType.value    = type;
        showToast.value    = true;
        setTimeout(() => { showToast.value = false; }, duration);
    }

    function openEditModal(row: Record<string, any>) {
        modalData.value = { ...row };
        showModal.value = true;
    }

    function closeModal() {
        showModal.value = false;
        modalData.value = null;
    }

    function confirmToggle(message: string, action: () => void) {
        confirmMessage.value = message;
        confirmAction.value  = action;
        showConfirm.value    = true;
    }

    function executeConfirm() {
        confirmAction.value?.();
        showConfirm.value   = false;
        confirmAction.value = null;
    }

    function cancelConfirm() {
        showConfirm.value   = false;
        confirmAction.value = null;
    }

    function saveModal(): Promise<void> {
        if (!modalData.value) return Promise.resolve();

        const formData = new FormData();
        formData.append('idMaquina', String(modalData.value.IdMaquina));
        formData.append('idCentro',  String(modalData.value.IdCentro));
        formData.append('modelo',    modalData.value.modelo   ?? '');
        formData.append('serie',     modalData.value.serie    ?? '');
        formData.append('tON',       modalData.value.tON      ?? '');
        formData.append('tOff',      modalData.value.tOff     ?? '');
        formData.append('esVisible', String(modalData.value.esVisible ? 1 : 0));
        formData.append('isRMT',     String(modalData.value.isRMT     ? 1 : 0));
        formData.append('pais',      paisRef.value);

        const csrfToken = document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

        return fetch('/api/maquinas/actualizar', {
            method:      'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept':       'application/json',
            },
            body: formData,
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    toast('Cambios guardados correctamente', 'success');
                    closeModal();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    toast(data.message ?? 'Error al guardar', 'error', 5000);
                }
            })
            .catch(err => {
                toast('Error de red: ' + err.message, 'error', 5000);
            });
    }

    return {
        showToast, toastMessage, toastType,
        showModal, modalData,
        showConfirm, confirmMessage,
        toast, openEditModal, closeModal,
        confirmToggle, executeConfirm, cancelConfirm,
        saveModal,
    };
}