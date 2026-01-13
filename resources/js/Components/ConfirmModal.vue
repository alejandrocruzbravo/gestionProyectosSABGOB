<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    show: Boolean,
    title: String,
    content: String,
    processing: Boolean, // Para mostrar carga en el botón
});

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <Modal :show="show" maxWidth="md" @close="emit('close')">
        <div class="p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <span class="material-symbols-outlined text-red-600 text-[24px]">warning</span>
                </div>

                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                    <h3 class="text-lg font-bold leading-6 text-gray-900">
                        {{ title }}
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            {{ content }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="emit('close')">
                    Cancelar
                </SecondaryButton>

                <DangerButton 
                    class="flex items-center gap-2 justify-center"
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                    @click="emit('confirm')"
                >
                    <span v-if="processing" class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>
                    <span v-else>Eliminar Definitivamente</span>
                </DangerButton>
            </div>
        </div>
    </Modal>
</template>