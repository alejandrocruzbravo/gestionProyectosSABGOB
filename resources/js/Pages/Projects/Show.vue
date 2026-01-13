<script setup>
/*
|--------------------------------------------------------------------------
|Importaciones
|--------------------------------------------------------------------------
*/
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, nextTick, watch } from 'vue'; // <--- Agregamos 'watch'
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import CommentSection from '@/Components/CommentSection.vue'; 

/*
|--------------------------------------------------------------------------
|Props
|--------------------------------------------------------------------------
*/
const props = defineProps({
    project: Object,
    owner: Object,
    tasks: Array,
    collaborators: Array,
    progress: Number
});

/*
|--------------------------------------------------------------------------
|Estados reactivos
|--------------------------------------------------------------------------
*/
// --- MODALES ---
const showDeleteModal = ref(false);
const showEditModal = ref(false);
const showCreateTaskModal = ref(false);
const showTaskDetailsModal = ref(false);
const showSubtaskModal = ref(false);

// --- VARIABLES DE EDICIÓN ---
const selectedTask = ref(null);
const isEditingTitle = ref(false);
const titleInputRef = ref(null);

// --- VARIABLES DE SUBTAREAS ---
const editingSubtaskId = ref(null);
const editingSubtaskTitle = ref('');
const subtaskInputRef = ref(null);

// --- VARIABLES PARA ELIMINAR SUBTAREA ---
const showSubtaskDeleteModal = ref(false);
const subtaskIdToDelete = ref(null);
const isDeletingSubtask = ref(false);

// --- VARIABLES DE BÚSQUEDA Y ESTADO ---
/*const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);*/
const isDeleting = ref(false);

// --- VARIABLES DE FORMULARIOS ---
const editForm = useForm({ title: props.project.title, description: props.project.description });
const createTaskForm = useForm({ title: '', project_id: props.project.id, priority: 'medium', due_date: '', assigned_to: '' });
const subtaskForm = useForm({ title: '' });

// --- VARIABLES PARA ELIMINAR TAREA ---
const showDeleteTaskModal = ref(false);
const isDeletingTask = ref(false);

// --- VARIABLES PARA INVITAR COLABORADORES ---
const showInviteModal = ref(false);
const searchEmail = ref('');
const foundUser = ref(null);
const searchStatus = ref('idle');
const isAddingUser = ref(false);

// --- VARIABLES PARA ELIMINAR COLABORADOR ---
const showRemoveCollaboratorModal = ref(false);
const collaboratorToRemoveId = ref(null);
const isRemovingCollaborator = ref(false);

// --- OBSERVADOR PARA ACTUALIZAR COMENTARIOS EN MODAL ---
// Esto asegura que si se añade un comentario (y Inertia recarga las props),
// la tarea seleccionada se actualice para mostrar el nuevo comentario.
watch(() => props.tasks, () => {
    if (selectedTask.value) {
        refreshSelectedTask();
    }
}, { deep: true });

/*
|--------------------------------------------------------------------------
|Funciones Helpers
|--------------------------------------------------------------------------
*/
// función para obtener iniciales de un nombre
const getInitials = (name) => name ? name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() : 'NA';

const getStatusLabel = (status) => {
    const map = { 'pending': 'Pendiente', 'in_progress': 'En Proceso', 'completed': 'Completado' };
    return map[status] || status;
};

// Obtener etiqueta según la prioridad
const getPriorityLabel = (priority) => {
    const map = { 'low': 'Baja', 'medium': 'Media', 'high': 'Alta' };
    return map[priority] || priority;
};
// Obtener color según el estado
const getStatusColor = (status) => {
    if (status === 'completed') return 'bg-green-100 text-green-700 border-green-200';
    if (status === 'in_progress') return 'bg-blue-100 text-blue-700 border-blue-200';
    return 'bg-gray-100 text-gray-600 border-gray-200';
};
// Obtener color según la prioridad
const getPriorityColor = (priority) => {
    if (priority === 'high') return 'text-red-600 bg-red-50 border-red-100';
    if (priority === 'medium') return 'text-orange-600 bg-orange-50 border-orange-100';
    return 'text-blue-600 bg-blue-50 border-blue-100';
};
// Funciones para editar y guardar título de tarea
const startEditingTitle = () => { isEditingTitle.value = true; nextTick(() => titleInputRef.value?.focus()); };
const saveTaskTitle = () => {
    if (!selectedTask.value) return;
    router.put(route('tasks.update', selectedTask.value.id), { title: selectedTask.value.title }, {
        preserveScroll: true, onSuccess: () => isEditingTitle.value = false
    });
};

const updatePriority = (priority) => {
    if (!selectedTask.value) return;
    selectedTask.value.priority = priority;
    router.put(route('tasks.update', selectedTask.value.id), { priority: priority }, { preserveScroll: true });
};
// Función para actualizar estado de tarea
const updateStatus = (status) => {
    if (!selectedTask.value) return;
    selectedTask.value.status = status;
    router.put(route('tasks.update', selectedTask.value.id), { status: status }, { preserveScroll: true });
};
// Función para solicitar eliminación de tarea
const deleteTask = () => {
    if (!selectedTask.value) return;
    isDeletingTask.value = true;
    router.delete(route('tasks.destroy', selectedTask.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteTaskModal.value = false;
            showTaskDetailsModal.value = false;
            selectedTask.value = null;
        },
        onFinish: () => {
            isDeletingTask.value = false;
        }
    });
};

// Funciones para crear subtareas
const submitSubtask = () => {
    if (!selectedTask.value) return;
    subtaskForm.post(route('tasks.subtasks.store', selectedTask.value.id), {
        onSuccess: () => {
            showSubtaskModal.value = false;
            subtaskForm.reset();
            refreshSelectedTask();
            showTaskDetailsModal.value = true;
        }
    });
};
// Funciones para editar
const startEditingSubtask = (subtask) => {
    editingSubtaskId.value = subtask.id;
    editingSubtaskTitle.value = subtask.title;
    nextTick(() => subtaskInputRef.value?.[0]?.focus());
};
// Guardar subtarea editada
const saveSubtask = (subtask) => {
    if (!editingSubtaskTitle.value.trim()) return;
    router.put(route('subtasks.update', subtask.id), { title: editingSubtaskTitle.value }, {
        preserveScroll: true, onSuccess: () => { editingSubtaskId.value = null; refreshSelectedTask(); }
    });
};
// Solicitar eliminación de subtarea
const requestDeleteSubtask = (subtaskId) => {
    subtaskIdToDelete.value = subtaskId;
    showSubtaskDeleteModal.value = true;
};
// Eliminar subtarea
const deleteSubtask = () => {
    if (!subtaskIdToDelete.value) return;
    isDeletingSubtask.value = true;
    router.delete(route('subtasks.destroy', subtaskIdToDelete.value), {
        preserveScroll: true,
        onSuccess: () => {
            refreshSelectedTask();
            showSubtaskDeleteModal.value = false;
            subtaskIdToDelete.value = null;
        },
        onFinish: () => {
            isDeletingSubtask.value = false;
        }
    });
};
// Alternar estado de subtarea
const toggleSubtask = (subtask) => {
    router.patch(route('subtasks.toggle', subtask.id), {}, {
        preserveScroll: true, onSuccess: () => refreshSelectedTask()
    });
};

// Refrescar la tarea seleccionada desde las props
const refreshSelectedTask = () => {
    const updated = props.tasks.find(t => t.id === selectedTask.value.id);
    if (updated) selectedTask.value = JSON.parse(JSON.stringify(updated));
};

// Alternar estado de tarea desde la lista
const toggleTaskCompletion = (task) => {
    const newStatus = task.status === 'completed' ? 'pending' : 'completed';
    task.status = newStatus;
    router.put(route('tasks.update', task.id), { status: newStatus }, { preserveScroll: true });
};

// Función para buscar usuario por email
const searchUserByEmail = async () => {
    if (!searchEmail.value.includes('@')) return;
    searchStatus.value = 'searching';
    foundUser.value = null;
    try {
        const response = await axios.post(route('users.search'), { 
            email: searchEmail.value, 
            project_id: props.project.id 
        });
        if (response.data) {
            foundUser.value = response.data;
            searchStatus.value = 'found';
        } else {
            searchStatus.value = 'not_found';
        }
    } catch (error) {
        console.error(error);
        searchStatus.value = 'not_found';
    }
};

// Agregar usuario encontrado como colaborador
const addFoundUser = () => {
    if (!foundUser.value) return;
    isAddingUser.value = true;
    router.post(route('projects.users.add', props.project.id), { 
        user_id: foundUser.value.id 
    }, {
        onSuccess: () => {
            showInviteModal.value = false;
            searchEmail.value = '';
            foundUser.value = null;
            searchStatus.value = 'idle';
            isAddingUser.value = false;
        }
    });
};

// confirmar eliminación de colaborador
const confirmRemoveCollaborator = (userId) => {
    collaboratorToRemoveId.value = userId;
    showRemoveCollaboratorModal.value = true;
};

// eliminar colaborador
const removeCollaborator = () => {
    if (!collaboratorToRemoveId.value) return;
    isRemovingCollaborator.value = true;
    router.delete(route('projects.users.remove', [props.project.id, collaboratorToRemoveId.value]), {
        preserveScroll: true,
        onSuccess: () => {
            showRemoveCollaboratorModal.value = false;
            collaboratorToRemoveId.value = null;
        },
        onFinish: () => {
            isRemovingCollaborator.value = false;
        }
    });
};

// Crear nueva tarea
const submitCreateTask = () => { createTaskForm.post(route('tasks.store'), { onSuccess: () => { showCreateTaskModal.value = false; createTaskForm.reset(); } }); };
// Abrir detalles de tarea
const openTaskDetails = (task) => { selectedTask.value = JSON.parse(JSON.stringify(task)); isEditingTitle.value = false; editingSubtaskId.value = null; showTaskDetailsModal.value = true; };
// Abrir modal de subtarea
const openSubtaskModal = () => { showTaskDetailsModal.value = false; setTimeout(() => { showSubtaskModal.value = true; }, 200); };
// Abrir modal de edición de proyecto
const openEditModal = () => { editForm.title = props.project.title; editForm.description = props.project.description; showEditModal.value = true; };
// Actualizar proyecto
const updateProject = () => { editForm.put(route('projects.update', props.project.id), { onSuccess: () => showEditModal.value = false }); };
// Alternar estado del proyecto
const toggleProjectStatus = () => { router.put(route('projects.update', props.project.id), { status: props.project.status === 'pending' ? 'in_progress' : 'pending' }); };
// Solicitar eliminación de proyecto
const confirmDeleteProject = () => { showDeleteModal.value = true; };
// Eliminar proyecto
const deleteProject = () => { isDeleting.value = true; router.delete(route('projects.destroy', props.project.id), { onFinish: () => { isDeleting.value = false; showDeleteModal.value = false; } }); };
</script>

<template>
    <Head :title="project.title" />
    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 relative">
                <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-primary/5 rounded-full blur-2xl"></div>
                </div>
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <Link :href="route('projects.index')" class="text-sm text-gray-400 hover:text-primary transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Volver
                            </Link>
                            <span class="text-gray-300">/</span><span class="text-sm text-primary font-bold tracking-wide uppercase">Detalles</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span :class="getStatusColor(project.status)" class="text-xs font-bold px-3 py-1 rounded-full border uppercase tracking-wider mr-2">{{ getStatusLabel(project.status) }}</span>
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors focus:outline-none">
                                        <span class="material-symbols-outlined">settings</span>
                                    </button>
                                </template>
                                <template #content>
                                    <div class="block px-4 py-2 text-xs text-gray-400">Administrar Proyecto</div>
                                    <button type="button" @click="openEditModal" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-400 hover:bg-gray-800 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">edit</span> Editar Detalles</div>
                                    </button>
                                    <button type="button" @click="toggleProjectStatus" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-400 hover:bg-gray-800 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">{{ project.status === 'pending' ? 'play_arrow' : 'pause' }}</span> {{ project.status === 'pending' ? 'Reanudar' : 'Pausar' }}</div>
                                    </button>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <button type="button" @click="confirmDeleteProject" class="block w-full px-4 py-2 text-start text-sm leading-5 text-red-600 hover:bg-red-50 transition duration-150 ease-in-out">
                                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">delete</span> Eliminar</div>
                                    </button>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
                        <div>
                            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ project.title }}</h1>
                            <p class="text-gray-500 mt-2 max-w-2xl text-lg leading-relaxed">{{ project.description || 'Sin descripción' }}</p>
                        </div>
                        <div class="w-full md:w-64 bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div class="flex justify-between items-end mb-2"><span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Progreso</span><span class="text-xl font-black text-primary">{{ progress }}%</span></div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-1000 ease-out" :class="project.status === 'pending' ? 'bg-gray-400' : 'bg-primary'" :style="{ width: progress + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 min-h-[500px]">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><span class="material-symbols-outlined text-primary">check_circle</span> Tareas del Proyecto</h3>
                            <button @click="showCreateTaskModal = true" class="text-sm font-semibold text-primary hover:bg-primary/5 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1"><span class="material-symbols-outlined text-[18px]">add</span> Nueva Tarea</button>
                        </div>
                        <div v-if="tasks.length > 0" class="space-y-3">
                            <div v-for="task in tasks" :key="task.id" @click="openTaskDetails(task)" class="group flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:border-primary/30 hover:shadow-md transition-all bg-white cursor-pointer">
                                <div class="flex items-center gap-4">
                                    <div @click.stop="toggleTaskCompletion(task)" class="w-5 h-5 rounded border-2 border-gray-300 flex items-center justify-center cursor-pointer hover:border-primary transition-colors z-10">
                                        <div v-if="task.status === 'completed'" class="w-2.5 h-2.5 bg-primary rounded-sm"></div>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800 group-hover:text-primary transition-colors">{{ task.title }}</h4>
                                        <p class="text-xs text-gray-400">{{task.subtasks ? task.subtasks.filter(s => s.status === 'completed').length : 0 }}/{{ task.subtasks ? task.subtasks.length : 0 }} Subtareas • Vence: {{ task.due_date ? new Date(task.due_date).toLocaleDateString() : 'Sin fecha' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div v-if="task.assignee" class="w-6 h-6 rounded-full bg-gray-100 text-[10px] flex items-center justify-center font-bold text-gray-600 border border-gray-200" :title="task.assignee.name">{{ getInitials(task.assignee.name) }}</div>
                                    <span :class="getStatusColor(task.status)" class="text-[10px] font-bold px-2 py-1 rounded-full border uppercase">{{ getStatusLabel(task.status) }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center h-64 text-center border-2 border-dashed border-gray-100 rounded-xl">
                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">playlist_add_check</span>
                            <p class="text-gray-500 text-sm">No hay tareas creadas aún.</p>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-purple-600">groups</span> Equipo
                            </h3>
                            <button @click="showInviteModal = true" class="w-8 h-8 flex items-center justify-center bg-primary text-white rounded-full hover:bg-primary-dark transition-colors shadow-lg shadow-primary/30">
                                <span class="material-symbols-outlined text-lg">add</span>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div v-if="owner" class="relative flex items-center gap-3 p-3 rounded-xl bg-purple-50 border border-purple-100">
                                <div class="w-10 h-10 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">{{ getInitials(owner.name) }}</div>
                                <div class="flex-1 min-w-0"><p class="text-sm font-bold text-gray-900 truncate">{{ owner.name }}</p><p class="text-xs text-gray-500 truncate">Líder del Proyecto</p></div>
                                <span class="material-symbols-outlined text-purple-300" title="Propietario">verified</span>
                            </div>
                            <div v-if="collaborators.length > 0" class="border-t border-gray-100 my-2"></div>
                            <div v-for="user in collaborators" :key="user.id" class="relative group flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-200">
                                <div class="w-10 h-10 rounded-full bg-white border-2 border-primary/20 text-primary flex items-center justify-center font-bold text-sm shadow-sm">{{ getInitials(user.name) }}</div>
                                <div class="flex-1 min-w-0"><p class="text-sm font-bold text-gray-900 truncate">{{ user.name }}</p><p class="text-xs text-gray-500 truncate">{{ user.email }}</p></div>
                                <button @click="confirmRemoveCollaborator(user.id)" class="text-gray-300 hover:text-red-500 transition-colors p-1 opacity-0 group-hover:opacity-100" title="Eliminar del equipo"><span class="material-symbols-outlined text-[18px]">person_remove</span></button>
                            </div>
                            <div v-if="collaborators.length === 0" class="text-center py-2"><p class="text-xs text-gray-400 italic">No hay colaboradores adicionales.</p></div>
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
                    <div class="w-full mr-4">
                        <div class="flex items-center gap-4 mb-2">
                            <Dropdown align="left" width="32">
                                <template #trigger>
                                    <button class="flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded border uppercase transition-opacity hover:opacity-80 focus:outline-none" :class="getPriorityColor(selectedTask.priority)">
                                        {{ getPriorityLabel(selectedTask.priority) }}
                                        <span class="material-symbols-outlined text-[12px]">expand_more</span>
                                    </button>
                                </template>
                                <template #content>
                                    <div class="py-1">
                                        <DropdownLink as="button" @click="updatePriority('low')">Baja</DropdownLink>
                                        <DropdownLink as="button" @click="updatePriority('medium')">Media</DropdownLink>
                                        <DropdownLink as="button" @click="updatePriority('high')">Alta</DropdownLink>
                                    </div>
                                </template>
                            </Dropdown>
                            <span class="text-xs text-gray-400">Creada el {{ new Date(selectedTask.created_at).toLocaleDateString() }}</span>
                        </div>
                        <div v-if="!isEditingTitle" class="flex items-center gap-2 group">
                            <h2 class="text-xl font-bold text-gray-900">{{ selectedTask.title }}</h2>
                            <button @click="startEditingTitle" class="text-gray-300 hover:text-primary transition-colors opacity-0 group-hover:opacity-100"><span class="material-symbols-outlined text-[18px]">edit</span></button>
                        </div>
                        <div v-else class="flex items-center gap-2 w-full">
                            <input ref="titleInputRef" v-model="selectedTask.title" @keyup.enter="saveTaskTitle" type="text" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary font-bold text-lg p-1" />
                            <button @click="saveTaskTitle" class="text-green-600 hover:text-green-700 p-1"><span class="material-symbols-outlined">check</span></button>
                            <button @click="isEditingTitle = false" class="text-red-500 hover:text-red-600 p-1"><span class="material-symbols-outlined">close</span></button>
                        </div>
                    </div>
                    <button @click="showTaskDetailsModal = false" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined">close</span></button>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                    <div class="bg-gray-50 p-3 rounded-lg"><span class="block text-xs text-gray-500 font-bold uppercase mb-1">Responsable</span>
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-600">{{ selectedTask.assignee ? getInitials(selectedTask.assignee.name) : '?' }}</div><span class="font-medium text-gray-900">{{ selectedTask.assignee ? selectedTask.assignee.name : 'Sin asignar' }}</span>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg flex flex-col justify-center">
                        <span class="block text-xs text-gray-500 font-bold uppercase mb-1">Estado</span>
                        <Dropdown align="left" width="48">
                            <template #trigger><button class="flex items-center gap-1 focus:outline-none"><span :class="getStatusColor(selectedTask.status)" class="text-xs font-bold px-3 py-1 rounded-full border uppercase inline-flex items-center gap-1 cursor-pointer hover:opacity-80 transition-opacity">{{ getStatusLabel(selectedTask.status) }} <span class="material-symbols-outlined text-[14px]">expand_more</span></span></button></template>
                            <template #content><div class="py-1"><DropdownLink as="button" @click="updateStatus('pending')"><div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-gray-400"></span> Pendiente</div></DropdownLink><DropdownLink as="button" @click="updateStatus('in_progress')"><div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-500"></span> En Proceso</div></DropdownLink><DropdownLink as="button" @click="updateStatus('completed')"><div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-green-500"></span> Completado</div></DropdownLink></div></template>
                        </Dropdown>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3"><h3 class="font-bold text-gray-900 flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-gray-400">checklist</span> Subtareas</h3><button @click="openSubtaskModal" class="text-xs font-bold text-primary hover:underline flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">add</span> Añadir Subtarea</button></div>
                    <div v-if="selectedTask.subtasks && selectedTask.subtasks.length > 0" class="space-y-2 max-h-60 overflow-y-auto pr-1">
                        <div v-for="sub in selectedTask.subtasks" :key="sub.id" class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg transition-colors group">
                            <button @click="toggleSubtask(sub)" class="flex-shrink-0 text-gray-400 hover:text-primary transition-colors focus:outline-none"><span class="material-symbols-outlined">{{ sub.status === 'completed' ? 'check_box' : 'check_box_outline_blank' }}</span></button>
                            <div class="flex-1">
                                <input v-if="editingSubtaskId === sub.id" ref="subtaskInputRef" v-model="editingSubtaskTitle" @keyup.enter="saveSubtask(sub)" @blur="saveSubtask(sub)" type="text" class="w-full text-sm border-gray-300 rounded p-1 focus:ring-primary focus:border-primary" />
                                <span v-else :class="{ 'line-through text-gray-400': sub.status === 'completed', 'text-gray-700': sub.status !== 'completed' }" class="text-sm block w-full cursor-text" @dblclick="startEditingSubtask(sub)">{{ sub.title }}</span>
                            </div>
                            <div v-if="editingSubtaskId !== sub.id" class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="startEditingSubtask(sub)" class="text-gray-300 hover:text-blue-500 p-1"><span class="material-symbols-outlined text-[16px]">edit</span></button>
                                <button @click="requestDeleteSubtask(sub.id)" class="text-gray-300 hover:text-red-500 p-1"><span class="material-symbols-outlined text-[16px]">delete</span></button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-6 border-2 border-dashed border-gray-100 rounded-lg bg-gray-50/50"><p class="text-sm text-gray-500 italic">No hay subtareas relacionadas.</p><button @click="openSubtaskModal" class="mt-2 text-xs text-primary font-bold hover:underline">Crear la primera</button></div>
                </div>

                <CommentSection 
                    v-if="selectedTask"
                    :comments="selectedTask.comments || []" 
                    :model-id="selectedTask.id" 
                    model-type="task" 
                />

                <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                    <button @click="showDeleteTaskModal = true" class="text-red-500 hover:text-red-700 text-sm font-bold flex items-center gap-1 transition-colors"><span class="material-symbols-outlined text-[18px]">delete</span> Eliminar Tarea</button>
                    <SecondaryButton @click="showTaskDetailsModal = false">Cerrar</SecondaryButton>
                </div>
            </div>
        </Modal>

        <Modal :show="showCreateTaskModal" @close="showCreateTaskModal = false">
            <div class="p-6"><h2 class="text-lg font-bold text-gray-900 mb-4">Nueva Tarea</h2><form @submit.prevent="submitCreateTask"><div class="mb-4"><InputLabel value="Título" /><TextInput v-model="createTaskForm.title" class="w-full mt-1" required autofocus /></div><div class="grid grid-cols-2 gap-4 mb-4"><div><InputLabel value="Asignar" /><select v-model="createTaskForm.assigned_to" class="w-full mt-1 border-gray-300 rounded-md shadow-sm"><option value="">Sin asignar</option><option v-for="u in collaborators" :key="u.id" :value="u.id">{{ u.name }}</option></select></div><div><InputLabel value="Prioridad" /><select v-model="createTaskForm.priority" class="w-full mt-1 border-gray-300 rounded-md shadow-sm"><option value="low">Baja</option><option value="medium">Media</option><option value="high">Alta</option></select></div></div><div class="mb-6"><InputLabel value="Vence" /><TextInput type="date" v-model="createTaskForm.due_date" class="w-full mt-1" /></div><div class="flex justify-end gap-2"><SecondaryButton @click="showCreateTaskModal = false">Cancelar</SecondaryButton><PrimaryButton :disabled="createTaskForm.processing">Crear</PrimaryButton></div></form></div>
        </Modal>
        <Modal :show="showSubtaskModal" @close="showSubtaskModal = false" maxWidth="sm">
            <div class="p-6"><h2 class="text-lg font-bold text-gray-900 mb-4">Añadir Subtarea</h2><p class="text-sm text-gray-500 mb-4">Para: <span class="font-bold text-gray-700">{{ selectedTask?.title }}</span></p><form @submit.prevent="submitSubtask"><div class="mb-4"><InputLabel value="Título" /><TextInput v-model="subtaskForm.title" class="w-full mt-1" required autofocus /></div><div class="flex justify-end gap-2"><SecondaryButton @click="showSubtaskModal = false; showTaskDetailsModal = true;">Volver</SecondaryButton><PrimaryButton :disabled="subtaskForm.processing">Añadir</PrimaryButton></div></form></div>
        </Modal>
        <Modal :show="showEditModal" @close="showEditModal = false"><div class="p-6"><h2 class="text-lg font-bold text-gray-900 mb-4">Editar Proyecto</h2><form @submit.prevent="updateProject"><div class="mb-4"><InputLabel value="Nombre" /><TextInput v-model="editForm.title" class="w-full mt-1" /></div><div class="mb-6"><InputLabel value="Descripción" /><textarea v-model="editForm.description" class="w-full border-gray-300 rounded-md shadow-sm" rows="3"></textarea></div><div class="flex justify-end gap-3"><SecondaryButton @click="showEditModal = false">Cancelar</SecondaryButton><PrimaryButton :disabled="editForm.processing">Guardar</PrimaryButton></div></form></div></Modal>
        <Modal :show="showInviteModal" @close="showInviteModal = false" maxWidth="sm"><div class="p-6"><h2 class="text-lg font-bold text-gray-900 mb-1">Añadir Colaborador</h2><p class="text-sm text-gray-500 mb-6">Busca por correo electrónico para invitar.</p><div class="flex gap-2 mb-6"><TextInput v-model="searchEmail" @keyup.enter="searchUserByEmail" placeholder="correo@ejemplo.com" class="w-full" type="email" autofocus /><SecondaryButton @click="searchUserByEmail" :disabled="searchStatus === 'searching'"><span v-if="searchStatus === 'searching'" class="material-symbols-outlined animate-spin text-[20px]">progress_activity</span><span v-else class="material-symbols-outlined text-[20px]">search</span></SecondaryButton></div><div class="min-h-[100px]"><div v-if="searchStatus === 'idle'" class="text-center py-4 text-gray-400 text-sm">Ingresa un correo para buscar.</div><div v-if="searchStatus === 'not_found'" class="text-center py-4 bg-red-50 rounded-lg border border-red-100"><span class="material-symbols-outlined text-red-400 text-3xl mb-1">person_off</span><p class="text-red-600 font-bold text-sm">Usuario no encontrado</p><p class="text-xs text-red-500 mt-1">Verifica el correo o asegúrate que no esté ya en el equipo.</p></div><div v-if="searchStatus === 'found' && foundUser" class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex items-center justify-between"><div class="flex items-center gap-3"><div class="w-10 h-10 rounded-full bg-white text-primary flex items-center justify-center font-bold shadow-sm">{{ getInitials(foundUser.name) }}</div><div><p class="font-bold text-gray-900 text-sm">{{ foundUser.name }}</p><p class="text-xs text-gray-500">{{ foundUser.email }}</p></div></div><PrimaryButton @click="addFoundUser" :disabled="isAddingUser" class="text-xs px-3">{{ isAddingUser ? '...' : 'Añadir' }}</PrimaryButton></div></div><div class="flex justify-end mt-4 border-t border-gray-100 pt-4"><SecondaryButton @click="showInviteModal = false">Cerrar</SecondaryButton></div></div></Modal>
        <ConfirmModal :show="showDeleteModal" @close="showDeleteModal = false" @confirm="deleteProject" :processing="isDeleting" title="¿Eliminar Proyecto?" content="Irreversible." />
        <ConfirmModal :show="showSubtaskDeleteModal" title="¿Eliminar Subtarea?" content="Esta acción eliminará la subtarea de forma permanente. ¿Deseas continuar?" :processing="isDeletingSubtask" @close="showSubtaskDeleteModal = false" @confirm="deleteSubtask" />
        <ConfirmModal :show="showDeleteTaskModal" title="¿Eliminar Tarea?" content="Estás a punto de eliminar esta tarea y todas sus subtareas asociadas. Esta acción no se puede deshacer." :processing="isDeletingTask" @close="showDeleteTaskModal = false" @confirm="deleteTask" />
        <ConfirmModal :show="showRemoveCollaboratorModal" title="¿Eliminar Colaborador?" content="¿Estás seguro de que deseas eliminar a este usuario del equipo? Perderá el acceso al proyecto inmediatamente." :processing="isRemovingCollaborator" @close="showRemoveCollaboratorModal = false" @confirm="removeCollaborator" />
    </AppLayout>
</template>