<script setup>
/*
|--------------------------------------------------------------------------
| Importaciones
|--------------------------------------------------------------------------
*/
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3'; // <--- 1. Importamos Link
import { ref, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/
defineProps({
    projects: Array
});

/*
|--------------------------------------------------------------------------
| Estados reactivos
|--------------------------------------------------------------------------
*/
const showCreateModal = ref(false);


/*
|--------------------------------------------------------------------------
|Funciones Helpers
|--------------------------------------------------------------------------
*/
// Formulario para crear nuevo proyecto
const form = useForm({
    title: '',
    description: '',
});

// Enviar formulario
const submit = () => {
    form.post(route('projects.store'), {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

// Cerrar modal y resetear formulario
const closeModal = () => {
    showCreateModal.value = false;
    form.reset();
    form.clearErrors();
};

// Obtener color según el estado del proyecto
const getStatusColor = (status) => {
    switch(status) {
        case 'completed': return 'bg-green-100 text-green-700';
        case 'pending': return 'bg-orange-100 text-orange-700';
        case 'in_progress': return 'bg-blue-100 text-blue-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};

// Obtener etiqueta según el estado del proyecto
const getStatusLabel = (status) => {
    return status === 'completed' ? 'Completado' : status === 'in_progress' ? 'En Progreso' : 'Pendiente';
};

// Abrir modal automáticamente si viene el parámetro create_action en la URL
onMounted(() => {
    if (route().params.create_action) {
        showCreateModal.value = true;
        const url = new URL(window.location);
        url.searchParams.delete('create_action');
        window.history.replaceState({}, '', url);
    }
});
</script>

<template>
    <Head title="Mis Proyectos" />

    <AppLayout>
        <div class="max-w-7xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Mis Proyectos</h1>
                    <p class="text-gray-500 text-sm mt-1">Gestiona y monitorea el progreso de tus iniciativas.</p>
                </div>
                
                <button @click="showCreateModal = true" class="bg-primary text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/25 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    <span>Crear Nuevo</span>
                </button>
            </div>

            <div v-if="projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link 
                    v-for="project in projects" 
                    :key="project.id" 
                    :href="route('projects.show', project.id)"
                    class="bg-white rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.03)] border border-gray-100 p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group cursor-pointer relative overflow-hidden block"
                >
                    <div class="flex justify-between items-start mb-4">
                        <div class="bg-blue-50 text-blue-600 rounded-xl p-3 group-hover:bg-primary group-hover:text-white transition-colors">
                            <span class="material-symbols-outlined text-[24px]">folder_open</span>
                        </div>
                        <span :class="getStatusColor(project.status)" class="text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">
                            {{ getStatusLabel(project.status) }}
                        </span>
                    </div>
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-primary transition-colors">{{ project.title }}</h3>
                        <p class="text-gray-500 text-sm line-clamp-2 leading-relaxed">{{ project.description || 'Sin descripción.' }}</p>
                    </div>
                    <div class="pt-4 border-t border-gray-50 flex items-center justify-between text-xs text-gray-400">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                            <span>{{ new Date(project.created_at).toLocaleDateString() }}</span>
                        </div>
                        <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform text-gray-300 group-hover:text-primary">arrow_forward</span>
                    </div>
                </Link>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl border-2 border-dashed border-gray-200 text-center">
                <div class="bg-gray-50 p-4 rounded-full mb-4">
                    <span class="material-symbols-outlined text-4xl text-gray-400">folder_off</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Aún no tienes proyectos</h3>
                <p class="text-gray-500 text-sm max-w-sm mx-auto mb-6">Comienza creando tu primer proyecto.</p>
                <button @click="showCreateModal = true" class="text-primary font-bold hover:underline">Crear mi primer proyecto</button>
            </div>

            <Modal :show="showCreateModal" @close="closeModal">
                <div class="p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Crear Nuevo Proyecto</h2>
                    
                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <InputLabel for="title" value="Nombre del Proyecto" />
                            <TextInput
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Ej. Rediseño Web Corporativo"
                                autofocus
                            />
                            <InputError :message="form.errors.title" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <InputLabel for="description" value="Descripción (Opcional)" />
                            <textarea 
                                id="description"
                                v-model="form.description"
                                class="mt-1 block w-full border-gray-300 focus:border-primary focus:ring-primary rounded-md shadow-sm"
                                rows="3"
                                placeholder="Detalles clave del proyecto..."
                            ></textarea>
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <div class="flex justify-end gap-3">
                            <SecondaryButton @click="closeModal"> Cancelar </SecondaryButton>
                            
                            <PrimaryButton 
                                :class="{ 'opacity-25': form.processing }" 
                                :disabled="form.processing"
                                class="bg-primary hover:bg-primary-dark"
                            >
                                Guardar Proyecto
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

        </div>
    </AppLayout>
</template>