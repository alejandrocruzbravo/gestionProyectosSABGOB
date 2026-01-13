<script setup>
/*
|--------------------------------------------------------------------------
| Importaciones
|--------------------------------------------------------------------------
*/
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue'; // <--- Importamos watch
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CommentSection from '@/Components/CommentSection.vue'; // <--- Importamos Componente

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/
const props = defineProps({
    project: Object,
    tasks: Array,
    collaborators: Array,
    owner: Object,
    progress: Number
});
/*
|--------------------------------------------------------------------------
| Estados Reactivo
|--------------------------------------------------------------------------
*/
//  Modal de detalles de tarea
const showTaskDetailsModal = ref(false);  
const selectedTask = ref(null); 

// Watch(Para actualizar comentarios en modal abierto) 
watch(() => props.tasks, () => {
    if (selectedTask.value) {
        // Buscamos la tarea actualizada en las nuevas props
        const updated = props.tasks.find(t => t.id === selectedTask.value.id);
        if (updated) {
            selectedTask.value = JSON.parse(JSON.stringify(updated));
        }
    }
}, { deep: true });

/*
|--------------------------------------------------------------------------
| Funciones Helpers
|--------------------------------------------------------------------------
*/
// Obtener iniciales de un nombre
const getInitials = (name) => name ? name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() : 'NA';

// Obtener clase de color según el estado de la tarea
const getStatusColor = (status) => {
    if (status === 'completed') return 'bg-green-100 text-green-700 border-green-200';
    if (status === 'in_progress') return 'bg-blue-100 text-blue-700 border-blue-200';
    return 'bg-gray-100 text-gray-600 border-gray-200';
};

// Obtener etiqueta según el estado de la tarea
const getStatusLabel = (status) => {
    const map = { 'pending': 'Pendiente', 'in_progress': 'En Proceso', 'completed': 'Completado' };
    return map[status] || status;
};

// Obtener clase de color según la prioridad de la tarea
const getPriorityColor = (priority) => {
    switch(priority) {
        case 'high': return 'text-red-600 bg-red-50 border-red-100';
        case 'medium': return 'text-orange-600 bg-orange-50 border-orange-100';
        case 'low': return 'text-blue-600 bg-blue-50 border-blue-100';
        default: return 'text-gray-600';
    }
};

// Abrir modal de detalles de tarea
const openTaskDetails = (task) => {
    selectedTask.value = JSON.parse(JSON.stringify(task)); 
    showTaskDetailsModal.value = true;
};
</script>

<template>
    <Head :title="project.title" />

    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 relative">
                <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-purple-500/5 rounded-full blur-2xl"></div>
                </div>
                <div class="relative z-10">
                     <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <Link :href="route('collaborations.index')" class="text-sm text-gray-400 hover:text-purple-600 transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Volver
                            </Link>
                            <span class="text-gray-300">/</span>
                            <span class="text-sm text-purple-600 font-bold tracking-wide uppercase">Vista Colaborador</span>
                        </div>
                         <span :class="getStatusColor(project.status)" class="text-xs font-bold px-3 py-1 rounded-full border uppercase tracking-wider">
                            {{ getStatusLabel(project.status) }}
                        </span>
                    </div>

                    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
                        <div>
                            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ project.title }}</h1>
                            <p class="text-gray-500 mt-2 max-w-2xl text-lg leading-relaxed">{{ project.description || 'Sin descripción' }}</p>
                        </div>
                        <div class="w-full md:w-64 bg-gray-50 p-4 rounded-xl border border-gray-100">
                             <div class="flex justify-between items-end mb-2">
                                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Progreso</span>
                                <span class="text-xl font-black text-purple-600">{{ progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-1000 ease-out" :class="project.status === 'pending' ? 'bg-gray-400' : 'bg-purple-600'" :style="{ width: progress + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 min-h-[500px]">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-purple-600">list_alt</span>
                                Tareas del Proyecto
                            </h3>
                        </div>

                        <div v-if="tasks.length > 0" class="space-y-3">
                            <div v-for="task in tasks" :key="task.id" @click="openTaskDetails(task)" class="group flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:border-purple-200 hover:shadow-md transition-all bg-white cursor-pointer">
                                <div class="flex items-center gap-4">
                                    <div class="w-5 h-5 rounded border-2 border-gray-300 flex items-center justify-center">
                                        <div v-if="task.status === 'completed'" class="w-2.5 h-2.5 bg-purple-600 rounded-sm"></div>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800 group-hover:text-purple-600 transition-colors">{{ task.title }}</h4>
                                        <p class="text-xs text-gray-400">
                                            {{ task.subtasks ? task.subtasks.filter(s => s.status === 'completed').length : 0 }}/{{ task.subtasks ? task.subtasks.length : 0 }} Subtareas • 
                                            Vence: {{ task.due_date ? new Date(task.due_date).toLocaleDateString() : 'Sin fecha' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div v-if="task.assignee" class="w-6 h-6 rounded-full bg-gray-100 text-[10px] flex items-center justify-center font-bold text-gray-600 border border-gray-200" :title="task.assignee.name">
                                        {{ getInitials(task.assignee.name) }}
                                    </div>
                                    <span :class="getStatusColor(task.status)" class="text-[10px] font-bold px-2 py-1 rounded-full border uppercase">
                                        {{ getStatusLabel(task.status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-else class="flex flex-col items-center justify-center h-64 text-center border-2 border-dashed border-gray-100 rounded-xl">
                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">playlist_add_check</span>
                            <p class="text-gray-500 text-sm">Este proyecto no tiene tareas aún.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><span class="material-symbols-outlined text-purple-600">groups</span> Equipo</h3>
                        </div>
                        <div class="space-y-4">
                             <div v-if="owner" class="relative flex items-center gap-3 p-3 rounded-xl bg-purple-50 border border-purple-100">
                                <div class="w-10 h-10 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                    {{ getInitials(owner.name) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ owner.name }}</p>
                                    <p class="text-xs text-gray-500 truncate">Líder del Proyecto</p>
                                </div>
                                <span class="material-symbols-outlined text-purple-300" title="Propietario">verified</span>
                            </div>
                            
                            <div v-if="collaborators.length > 0" class="border-t border-gray-100 my-2"></div>

                            <div v-for="user in collaborators" :key="user.id" class="relative group flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-200">
                                <div class="w-10 h-10 rounded-full bg-white border-2 border-purple-200 text-purple-600 flex items-center justify-center font-bold text-sm shadow-sm">{{ getInitials(user.name) }}</div>
                                <div class="flex-1 min-w-0"><p class="text-sm font-bold text-gray-900 truncate">{{ user.name }}</p><p class="text-xs text-gray-500 truncate">{{ user.email }}</p></div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <CommentSection 
                            :comments="project.comments || []" 
                            :model-id="project.id" 
                            model-type="project" 
                        />
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showTaskDetailsModal" @close="showTaskDetailsModal = false">
            <div v-if="selectedTask" class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                             <span :class="getPriorityColor(selectedTask.priority)" class="text-[10px] font-bold px-2 py-0.5 rounded border uppercase">{{ selectedTask.priority }}</span>
                             <span class="text-xs text-gray-400">Creada el {{ new Date(selectedTask.created_at).toLocaleDateString() }}</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">{{ selectedTask.title }}</h2>
                    </div>
                    <button @click="showTaskDetailsModal = false" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined">close</span></button>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <span class="block text-xs text-gray-500 font-bold uppercase mb-1">Responsable</span>
                        <div class="flex items-center gap-2">
                             <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-600">
                                {{ selectedTask.assignee ? getInitials(selectedTask.assignee.name) : '?' }}
                            </div>
                            <span class="font-medium text-gray-900">{{ selectedTask.assignee ? selectedTask.assignee.name : 'Sin asignar' }}</span>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <span class="block text-xs text-gray-500 font-bold uppercase mb-1">Estado</span>
                        <span :class="getStatusColor(selectedTask.status)" class="text-xs font-bold px-2 py-0.5 rounded-full border uppercase inline-block">
                             {{ getStatusLabel(selectedTask.status) }}
                        </span>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-gray-400">checklist</span>
                            Subtareas
                        </h3>
                    </div>

                    <div v-if="selectedTask.subtasks && selectedTask.subtasks.length > 0" class="space-y-2 max-h-60 overflow-y-auto pr-1">
                        <div v-for="sub in selectedTask.subtasks" :key="sub.id" class="flex items-center gap-3 p-2 rounded-lg bg-gray-50/50">
                            <span class="material-symbols-outlined text-gray-400">
                                {{ sub.status === 'completed' ? 'check_box' : 'check_box_outline_blank' }}
                            </span>
                            <span :class="{'line-through text-gray-400': sub.status === 'completed', 'text-gray-700': sub.status !== 'completed'}" class="text-sm flex-1">
                                {{ sub.title }}
                            </span>
                        </div>
                    </div>
                    
                    <div v-else class="text-center py-6 border-2 border-dashed border-gray-100 rounded-lg bg-gray-50/50">
                        <p class="text-sm text-gray-500 italic">No hay subtareas relacionadas.</p>
                    </div>
                </div>

                <CommentSection 
                    v-if="selectedTask"
                    :comments="selectedTask.comments || []" 
                    :model-id="selectedTask.id" 
                    model-type="task" 
                />

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <SecondaryButton @click="showTaskDetailsModal = false">Cerrar</SecondaryButton>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>