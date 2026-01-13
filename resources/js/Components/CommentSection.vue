<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    comments: Array,
    modelId: Number,
    modelType: String, // 'project', 'task', 'subtask'
});

const form = useForm({
    body: '',
    commentable_id: props.modelId,
    commentable_type: props.modelType
});

// Usuario actual para saber si puede borrar
const currentUser = usePage().props.auth.user;

const submit = () => {
    form.post(route('comments.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset()
    });
};

const deleteComment = (commentId) => {
    if(!confirm('¿Borrar comentario?')) return;
    useForm({}).delete(route('comments.destroy', commentId), { preserveScroll: true });
};

const getInitials = (name) => name ? name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() : 'NA';
const formatDate = (date) => new Date(date).toLocaleDateString() + ' ' + new Date(date).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
</script>

<template>
    <div class="mt-6 border-t border-gray-100 pt-6">
        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-gray-400 text-[18px]">chat</span>
            Comentarios ({{ comments.length }})
        </h3>

        <div v-if="comments.length > 0" class="space-y-4 mb-6 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
            <div v-for="comment in comments" :key="comment.id" class="flex gap-3 group">
                <div class="w-8 h-8 rounded-full bg-gray-100 flex-shrink-0 flex items-center justify-center text-xs font-bold text-gray-600">
                    {{ getInitials(comment.user?.name) }}
                </div>
                <div class="flex-1 bg-gray-50 rounded-r-xl rounded-bl-xl p-3 text-sm">
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-bold text-gray-800 text-xs">{{ comment.user?.name }}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-gray-400">{{ formatDate(comment.created_at) }}</span>
                            <button v-if="comment.user_id === currentUser.id" @click="deleteComment(comment.id)" class="text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="material-symbols-outlined text-[14px]">delete</span>
                            </button>
                        </div>
                    </div>
                    <p class="text-gray-600 leading-relaxed">{{ comment.body }}</p>
                </div>
            </div>
        </div>
        <div v-else class="text-center py-4 text-xs text-gray-400 italic mb-4">
            No hay comentarios aún.
        </div>

        <form @submit.prevent="submit" class="flex gap-2">
            <input 
                v-model="form.body" 
                type="text" 
                placeholder="Escribe un comentario..." 
                class="flex-1 border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-primary shadow-sm"
                required
            />
            <button :disabled="form.processing" class="bg-gray-900 text-white p-2 rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50">
                <span class="material-symbols-outlined text-[20px]">send</span>
            </button>
        </form>
    </div>
</template>