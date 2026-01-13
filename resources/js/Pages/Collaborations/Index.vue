<script setup>

/*
|--------------------------------------------------------------------------
| Importaciones
|--------------------------------------------------------------------------
*/
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

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
| Funciones Helpers
|--------------------------------------------------------------------------
*/
// Obtener clase de color según el estado del proyecto
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
</script>

<template>
    <Head title="Mis Colaboraciones" />

    <AppLayout>
        <div class="max-w-7xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Mis Colaboraciones</h1>
                    <p class="text-gray-500 text-sm mt-1">Proyectos en los que participas como invitado.</p>
                </div>
                </div>

            <div v-if="projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link 
                    v-for="project in projects" 
                    :key="project.id" 
                    :href="route('collaborations.show', project.id)"
                    class="bg-white rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.03)] border border-gray-100 p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group cursor-pointer relative overflow-hidden block"
                >
                    <div class="flex justify-between items-start mb-4">
                        <div class="bg-purple-50 text-purple-600 rounded-xl p-3 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                            <span class="material-symbols-outlined text-[24px]">groups</span>
                        </div>
                        <span :class="getStatusColor(project.status)" class="text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">
                            {{ getStatusLabel(project.status) }}
                        </span>
                    </div>
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-purple-600 transition-colors">{{ project.title }}</h3>
                        <p class="text-gray-500 text-sm line-clamp-2 leading-relaxed">{{ project.description || 'Sin descripción.' }}</p>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-50 flex items-center justify-between text-xs text-gray-400">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">person</span>
                            <span>Líder: {{ project.user?.name }}</span>
                        </div>
                        <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform text-gray-300 group-hover:text-purple-600">arrow_forward</span>
                    </div>
                </Link>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl border-2 border-dashed border-gray-200 text-center">
                <div class="bg-gray-50 p-4 rounded-full mb-4">
                    <span class="material-symbols-outlined text-4xl text-gray-400">folder_off</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Sin colaboraciones</h3>
                <p class="text-gray-500 text-sm max-w-sm mx-auto mb-6">Aún no te han invitado a ningún proyecto.</p>
            </div>

        </div>
    </AppLayout>
</template>