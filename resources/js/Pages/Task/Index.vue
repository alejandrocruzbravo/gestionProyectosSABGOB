<script setup>
/*
|--------------------------------------------------------------------------
|Importaciones
|--------------------------------------------------------------------------
*/
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

/*
|--------------------------------------------------------------------------
|Props
|--------------------------------------------------------------------------
*/
const props = defineProps({
    tasks: Array,
    projects: Array,
    users: Array
});

/*
|--------------------------------------------------------------------------
|Estados reactivos
|--------------------------------------------------------------------------
*/
const showCreateModal = ref(false);
const showDetailsModal = ref(false);
const selectedTask = ref(null);

const form = useForm({
    title: '',
    project_id: '',
    priority: 'medium',
    due_date: '',
    assigned_to: ''
});

/*
|--------------------------------------------------------------------------
|Funciones computadas
|--------------------------------------------------------------------------
*/
const pendingTasks = computed(() => props.tasks.filter(t => t.status === 'pending'));
const progressTasks = computed(() => props.tasks.filter(t => t.status === 'in_progress'));
const completedTasks = computed(() => props.tasks.filter(t => t.status === 'completed'));

/*
|--------------------------------------------------------------------------
|Funciones Helpers
|--------------------------------------------------------------------------
*/
// Enviar formulario de nueva tarea
const submit = () => {
    form.assigned_to = usePage().props.auth.user.id;
    form.post(route('task.store'), { 
        onSuccess: () => { showCreateModal.value = false; form.reset(); } 
    });
};
// Actualización rápida del estado de la tarea desde la lista
const quickUpdateStatus = (task, newStatus) => {
    router.put(route('task.update', task.id), { status: newStatus }, { preserveScroll: true });
};
// Abrir modal de detalles de tarea
const openTaskDetails = (task) => {
    selectedTask.value = JSON.parse(JSON.stringify(task));
    showDetailsModal.value = true;
};
// Actualizar estado de la tarea desde el modal de detalles
const updateStatus = (status) => {
    if (!selectedTask.value) return;
    selectedTask.value.status = status;
    router.put(route('task.update', selectedTask.value.id), { status: status }, { preserveScroll: true });
};
// Alternar estado de una subtarea
const toggleSubtask = (subtask) => {
    router.patch(route('subtasks.toggle', subtask.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            const sub = selectedTask.value.subtasks.find(s => s.id === subtask.id);
            if(sub) sub.status = sub.status === 'pending' ? 'completed' : 'pending';
        }
    });
};

// Obtener color según la prioridad de la tarea
const getPriorityColor = (priority) => {
    switch(priority) {
        case 'high': return 'text-red-600 bg-red-50 border-red-100';
        case 'medium': return 'text-orange-600 bg-orange-50 border-orange-100';
        case 'low': return 'text-blue-600 bg-blue-50 border-blue-100';
        default: return 'text-gray-600';
    }
};
// Obtener etiqueta según el estado de la tarea
const getStatusLabel = (status) => {
    const map = { 'pending': 'Pendiente', 'in_progress': 'En Proceso', 'completed': 'Completado' };
    return map[status] || status;
};
// Obtener color según el estado de la tarea
const getStatusColor = (status) => {
    if (status === 'completed') return 'bg-green-100 text-green-700 border-green-200';
    if (status === 'in_progress') return 'bg-blue-100 text-blue-700 border-blue-200';
    return 'bg-gray-100 text-gray-600 border-gray-200';
};
</script>

<template>
    <Head title="Mis Tareas" />

    <AppLayout>
        <div class="max-w-7xl mx-auto h-[calc(100vh-8rem)] flex flex-col">
            
            <div class="flex justify-between items-center mb-6 flex-shrink-0">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Mis Asignaciones</h1>
                    <p class="text-sm text-gray-500">Tareas asignadas específicamente a ti.</p>
                </div>
                <button @click="showCreateModal = true" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg font-bold shadow-lg shadow-primary/20 flex items-center gap-2 transition-all">
                    <span class="material-symbols-outlined">add_task</span>
                    Nueva Tarea
                </button>
            </div>

            <div class="flex-1 min-h-0 grid grid-cols-1 md:grid-cols-3 gap-6 pb-2">
                
                <div class="flex flex-col bg-gray-100/50 rounded-xl border border-gray-200 h-full max-h-full">
                    <div class="p-4 border-b border-gray-200 bg-gray-50 rounded-t-xl flex justify-between items-center flex-shrink-0">
                        <h3 class="font-bold text-gray-700 flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-gray-400"></div> Pendientes</h3>
                        <span class="bg-white border border-gray-200 text-xs font-bold px-2 py-0.5 rounded-md text-gray-500">{{ pendingTasks.length }}</span>
                    </div>
                    <div class="p-4 space-y-3 overflow-y-auto custom-scrollbar flex-1">
                        <div v-for="task in pendingTasks" :key="task.id" @click="openTaskDetails(task)" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all cursor-pointer group">
                            <div class="flex justify-between mb-2"><span :class="getPriorityColor(task.priority)" class="text-[10px] uppercase font-bold px-2 py-0.5 rounded border">{{ task.priority }}</span></div>
                            <h4 class="font-bold text-gray-800 text-sm mb-1">{{ task.title }}</h4>
                            <p class="text-xs text-gray-500 mb-3 truncate">{{ task.project?.title }}</p>
                            <div class="flex items-center justify-between pt-3 border-t border-gray-50">
                                <span class="text-xs text-gray-400">{{ task.due_date ? new Date(task.due_date).toLocaleDateString().slice(0,5) : '--/--' }}</span>
                                <button @click.stop="quickUpdateStatus(task, 'in_progress')" class="text-gray-400 hover:text-blue-600 p-1 rounded hover:bg-blue-50 transition-colors"><span class="material-symbols-outlined text-[20px]">arrow_forward</span></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col bg-blue-50/50 rounded-xl border border-blue-100 h-full max-h-full">
                    <div class="p-4 border-b border-blue-100 bg-blue-50 rounded-t-xl flex justify-between items-center flex-shrink-0">
                        <h3 class="font-bold text-blue-800 flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-blue-500 animate-pulse"></div> En Progreso</h3>
                        <span class="bg-white border border-blue-100 text-xs font-bold px-2 py-0.5 rounded-md text-blue-600">{{ progressTasks.length }}</span>
                    </div>
                    <div class="p-4 space-y-3 overflow-y-auto custom-scrollbar flex-1">
                        <div v-for="task in progressTasks" :key="task.id" @click="openTaskDetails(task)" class="bg-white p-4 rounded-lg shadow-sm border border-blue-100 hover:shadow-md transition-all cursor-pointer">
                             <div class="flex justify-between mb-2"><span :class="getPriorityColor(task.priority)" class="text-[10px] uppercase font-bold px-2 py-0.5 rounded border">{{ task.priority }}</span></div>
                            <h4 class="font-bold text-gray-800 text-sm mb-1">{{ task.title }}</h4>
                            <p class="text-xs text-gray-500 mb-3 truncate">{{ task.project?.title }}</p>
                            <div class="flex items-center justify-end pt-3 border-t border-gray-50 gap-1">
                                <button @click.stop="quickUpdateStatus(task, 'pending')" class="text-gray-400 hover:text-orange-600 p-1"><span class="material-symbols-outlined text-[18px]">arrow_back</span></button>
                                <button @click.stop="quickUpdateStatus(task, 'completed')" class="text-gray-400 hover:text-green-600 p-1"><span class="material-symbols-outlined text-[20px]">check_circle</span></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col bg-green-50/50 rounded-xl border border-green-100 h-full max-h-full">
                    <div class="p-4 border-b border-green-100 bg-green-50 rounded-t-xl flex justify-between items-center flex-shrink-0">
                        <h3 class="font-bold text-green-800 flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-green-500"></div> Completadas</h3>
                        <span class="bg-white border border-green-100 text-xs font-bold px-2 py-0.5 rounded-md text-green-600">{{ completedTasks.length }}</span>
                    </div>
                    <div class="p-4 space-y-3 overflow-y-auto custom-scrollbar flex-1">
                        <div v-for="task in completedTasks" :key="task.id" @click="openTaskDetails(task)" class="bg-white/60 p-4 rounded-lg border border-green-100 opacity-75 hover:opacity-100 transition-opacity cursor-pointer">
                            <h4 class="font-bold text-gray-600 text-sm mb-1 line-through decoration-green-500">{{ task.title }}</h4>
                            <p class="text-xs text-gray-400">{{ task.project?.title }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showDetailsModal" @close="showDetailsModal = false">
            <div v-if="selectedTask" class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                             <span :class="getPriorityColor(selectedTask.priority)" class="text-[10px] font-bold px-2 py-0.5 rounded border uppercase">{{ selectedTask.priority }}</span>
                             <span class="text-xs text-gray-400">Vence: {{ selectedTask.due_date ? new Date(selectedTask.due_date).toLocaleDateString() : 'Sin fecha' }}</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">{{ selectedTask.title }}</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ selectedTask.description || 'Sin descripción' }}</p>
                    </div>
                    <button @click="showDetailsModal = false" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined">close</span></button>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                    <div class="bg-gray-50 p-3 rounded-lg"><span class="block text-xs text-gray-500 font-bold uppercase mb-1">Proyecto</span><span class="font-medium text-gray-900">{{ selectedTask.project?.title }}</span></div>
                    <div class="bg-gray-50 p-3 rounded-lg flex flex-col justify-center">
                        <span class="block text-xs text-gray-500 font-bold uppercase mb-1">Estado Actual</span>
                        <Dropdown align="left" width="48">
                            <template #trigger><button class="flex items-center gap-1 focus:outline-none"><span :class="getStatusColor(selectedTask.status)" class="text-xs font-bold px-3 py-1 rounded-full border uppercase inline-flex items-center gap-1 cursor-pointer hover:opacity-80 transition-opacity">{{ getStatusLabel(selectedTask.status) }}<span class="material-symbols-outlined text-[14px]">expand_more</span></span></button></template>
                            <template #content><div class="py-1"><DropdownLink as="button" @click="updateStatus('pending')"><div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-gray-400"></span> Pendiente</div></DropdownLink><DropdownLink as="button" @click="updateStatus('in_progress')"><div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-500"></span> En Proceso</div></DropdownLink><DropdownLink as="button" @click="updateStatus('completed')"><div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-green-500"></span> Completado</div></DropdownLink></div></template>
                        </Dropdown>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3"><h3 class="font-bold text-gray-900 flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-gray-400">checklist</span> Subtareas</h3></div>
                    <div v-if="selectedTask.subtasks && selectedTask.subtasks.length > 0" class="space-y-2 max-h-60 overflow-y-auto pr-1">
                        <div v-for="sub in selectedTask.subtasks" :key="sub.id" class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg transition-colors"><button @click="toggleSubtask(sub)" class="flex-shrink-0 text-gray-400 hover:text-primary transition-colors focus:outline-none"><span class="material-symbols-outlined">{{ sub.status === 'completed' ? 'check_box' : 'check_box_outline_blank' }}</span></button><span :class="{'line-through text-gray-400': sub.status === 'completed', 'text-gray-700': sub.status !== 'completed'}" class="text-sm flex-1">{{ sub.title }}</span></div>
                    </div>
                    <div v-else class="text-center py-6 border-2 border-dashed border-gray-100 rounded-lg bg-gray-50/50"><p class="text-sm text-gray-500 italic">No hay subtareas.</p></div>
                </div>
                <div class="flex justify-end pt-4 border-t border-gray-100"><SecondaryButton @click="showDetailsModal = false">Cerrar</SecondaryButton></div>
            </div>
        </Modal>

        <Modal :show="showCreateModal" @close="showCreateModal = false">
             <div class="p-6"><h2 class="text-lg font-bold text-gray-900 mb-4">Nueva Tarea</h2><form @submit.prevent="submit"><div class="mb-4"><InputLabel value="Título" /><TextInput v-model="form.title" class="w-full mt-1" required autofocus /></div><div class="mb-4"><InputLabel value="Proyecto" /><select v-model="form.project_id" class="w-full mt-1 border-gray-300 rounded-md shadow-sm" required><option value="" disabled>Selecciona uno</option><option v-for="p in projects" :key="p.id" :value="p.id">{{ p.title }}</option></select></div><div class="grid grid-cols-2 gap-4 mb-4"><div><InputLabel value="Prioridad" /><select v-model="form.priority" class="w-full mt-1 border-gray-300 rounded-md shadow-sm"><option value="low">Baja</option><option value="medium">Media</option><option value="high">Alta</option></select></div><div><InputLabel value="Vence" /><TextInput type="date" v-model="form.due_date" class="w-full mt-1" /></div></div><div class="flex justify-end gap-2"><SecondaryButton @click="showCreateModal = false">Cancelar</SecondaryButton><PrimaryButton :disabled="form.processing">Guardar</PrimaryButton></div></form></div>
        </Modal>

    </AppLayout>
</template>

<style scoped>
/* Scrollbar delgado y elegante para Webkit (Chrome, Safari, Edge) */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1; /* Gris suave */
    border-radius: 20px;       /* Bordes redondeados */
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8; /* Gris más oscuro al pasar el mouse */
}
</style>