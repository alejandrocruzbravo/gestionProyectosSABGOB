<script setup>
/*
|--------------------------------------------------------------------------
| Importaciones
|--------------------------------------------------------------------------
*/
import { Head, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';
/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/
const props = defineProps({
    counts: Object,
    projects: Array,
    deadlines: Array
});

const user = usePage().props.auth.user;

/*
|--------------------------------------------------------------------------
| Funciones Computadas
|--------------------------------------------------------------------------
*/

// Configuramos las tarjetas superiores con los datos reales
const stats = computed(() => [
    { 
        label: 'Proyectos Totales', 
        value: props.counts.projects, 
        icon: 'folder_managed', 
        color: 'text-primary', 
        bg: 'bg-primary/10' 
    },
    { 
        label: 'Tareas Activas', 
        value: props.counts.tasks, 
        icon: 'check_box', 
        color: 'text-blue-600', 
        bg: 'bg-blue-50' 
    },
    { 
        label: 'Miembros del Equipo', 
        value: props.counts.team, 
        icon: 'groups', 
        color: 'text-orange-600', 
        bg: 'bg-orange-50' 
    },
]);

/*
|--------------------------------------------------------------------------
| Funciones Helpers
|--------------------------------------------------------------------------
*/
// Formatear fecha a formato legible
const formatDate = (dateString) => {
    if (!dateString) return 'Sin fecha';
    const date = new Date(dateString);
    const today = new Date();
    // Si es hoy
    if (date.toDateString() === today.toDateString()) return 'Hoy';
    return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
};
// Obtener configuración según el estado del proyecto
const getStatusConfig = (status) => {
    const map = {
        'pending': { label: 'Pendiente', color: 'bg-orange-100 text-orange-700' },
        'in_progress': { label: 'En Progreso', color: 'bg-blue-100 text-blue-700' },
        'completed': { label: 'Completado', color: 'bg-green-100 text-green-700' }
    };
    return map[status] || { label: status, color: 'bg-gray-100 text-gray-600' };
};
// Obtener color según la prioridad de la tarea
const getPriorityColor = (priority) => {
    const map = { 'high': 'red', 'medium': 'orange', 'low': 'blue' };
    return map[priority] || 'gray';
};

// Determinar ruta de proyecto (Dueño -> projects.show, Colaborador -> collaborations.show)
const getProjectRoute = (project) => {
    return project.is_owner 
        ? route('projects.show', project.id) 
        : route('collaborations.show', project.id);
};
</script>

<template>
    <Head title="Panel Principal" />

    <AppLayout>
        
        <div class="max-w-7xl mx-auto flex flex-col gap-8">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 tracking-tight">¡Hola de nuevo, {{ user.name }}!</h2>
                    <p class="text-gray-500 mt-1">Aquí tienes el resumen de tu actividad hoy.</p>
                </div>
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500 bg-white px-3 py-1.5 rounded-md shadow-sm border border-gray-100">
                    <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                    <span>{{ new Date().toLocaleDateString('es-ES', { month: 'long', day: 'numeric', year: 'numeric' }) }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <div v-for="(stat, index) in stats" :key="index" class="bg-white p-6 rounded-xl shadow-[0_2px_10px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col gap-4 group hover:-translate-y-1 transition-transform duration-300">
                    <div class="flex items-center justify-between">
                        <div :class="[stat.bg, stat.color]" class="p-3 rounded-lg transition-colors group-hover:bg-opacity-80">
                            <span class="material-symbols-outlined">{{ stat.icon }}</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">{{ stat.label }}</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ stat.value }}</h3>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 flex flex-col gap-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900">Proyectos Recientes</h3>
                        <Link :href="route('projects.index')" class="text-primary text-sm font-semibold hover:text-primary-dark hover:underline">Ver Todos</Link>
                    </div>
                    
                    <div v-if="projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Link 
                            v-for="project in projects" 
                            :key="project.id" 
                            :href="getProjectRoute(project)"
                            class="bg-white rounded-xl shadow-[0_2px_10px_rgba(0,0,0,0.03)] border border-gray-100 p-5 hover:shadow-md transition-shadow cursor-pointer flex flex-col gap-4 h-full"
                        >
                            <div class="flex justify-between items-start">
                                <div class="bg-blue-50 text-primary rounded-lg p-2.5">
                                    <span class="material-symbols-outlined">{{ project.is_owner ? 'folder_managed' : 'groups' }}</span>
                                </div>
                                <span :class="getStatusConfig(project.status).color" class="text-xs font-bold px-2 py-1 rounded-full uppercase">
                                    {{ getStatusConfig(project.status).label }}
                                </span>
                            </div>
                            <div>
                                <h4 class="font-bold text-lg text-gray-900 mb-1 line-clamp-1">{{ project.title }}</h4>
                                <p class="text-gray-500 text-sm line-clamp-2 min-h-[40px]">{{ project.desc || 'Sin descripción disponible.' }}</p>
                            </div>
                            <div class="mt-auto pt-4 border-t border-gray-50">
                                <div class="flex justify-between text-xs font-semibold text-gray-500 mb-1.5">
                                    <span>Progreso</span>
                                    <span class="text-primary">{{ project.progress }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2 mb-4 overflow-hidden">
                                    <div class="bg-primary h-2 rounded-full transition-all duration-1000" :style="{ width: project.progress + '%' }"></div>
                                </div>
                                <div class="flex items-center justify-between text-xs text-gray-400">
                                    <span>{{ project.is_owner ? 'Líder' : 'Colaborador' }}</span>
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">calendar_month</span>
                                        <span>{{ new Date(project.created_at).toLocaleDateString() }}</span>
                                    </div>
                                </div>
                            </div>
                        </Link>

                        <Link :href="route('projects.index')" class="bg-white rounded-xl shadow-[0_2px_10px_rgba(0,0,0,0.03)] border border-dashed border-gray-300 p-5 hover:bg-gray-50 transition-colors cursor-pointer flex flex-col items-center justify-center gap-2 h-full min-h-[220px]">
                            <div class="bg-gray-100 p-3 rounded-full text-gray-400">
                                <span class="material-symbols-outlined text-[32px]">add</span>
                            </div>
                            <h4 class="font-bold text-gray-900">Crear Nuevo Proyecto</h4>
                            <p class="text-gray-500 text-xs text-center px-4">Inicia una nueva iniciativa e invita a tu equipo.</p>
                        </Link>
                    </div>

                    <div v-else class="bg-white p-8 rounded-xl text-center border border-dashed border-gray-300">
                        <p class="text-gray-500">No tienes proyectos activos.</p>
                        <Link :href="route('projects.index')" class="text-primary font-bold text-sm mt-2 inline-block">Crear el primero</Link>
                    </div>
                </div>

                <div class="flex flex-col gap-8">
                    <div class="bg-white rounded-xl shadow-[0_2px_10px_rgba(0,0,0,0.03)] border border-gray-100 overflow-hidden min-h-[300px]">
                        <div class="p-4 border-b border-gray-50 bg-gray-50/50 flex items-center justify-between">
                            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wide">Próximos Vencimientos</h3>
                            <span class="material-symbols-outlined text-gray-400 text-[20px]">event</span>
                        </div>
                        <div class="p-2">
                            <div v-if="deadlines.length > 0">
                                <Link 
                                    v-for="task in deadlines" 
                                    :key="task.id" 
                                    :href="route('task.index')"
                                    class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg transition-colors cursor-pointer border-l-2 border-transparent hover:border-primary group"
                                >
                                    <div :class="`bg-${getPriorityColor(task.priority)}-50 text-${getPriorityColor(task.priority)}-600`" class="rounded-lg p-2 flex-shrink-0">
                                        <span class="material-symbols-outlined text-[20px]">priority_high</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-bold text-gray-900 truncate group-hover:text-primary transition-colors">{{ task.title }}</h4>
                                        <p class="text-xs text-gray-500 truncate">{{ task.project_title }} • <span :class="`text-${getPriorityColor(task.priority)}-500 font-semibold`">{{ formatDate(task.date) }}</span></p>
                                    </div>
                                </Link>
                            </div>
                            <div v-else class="text-center py-8 px-4">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">task_alt</span>
                                <p class="text-xs text-gray-400">¡Todo al día! No tienes tareas próximas a vencer.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="h-12"></div>

    </AppLayout>
</template>