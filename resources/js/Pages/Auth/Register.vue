<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const passwordStrength = computed(() => {
    const length = form.password.length;
    if (length === 0) return { score: 0, label: 'Vacía', color: 'bg-slate-200' };
    if (length < 6) return { score: 1, label: 'Débil', color: 'bg-red-500' };
    if (length < 10) return { score: 2, label: 'Media', color: 'bg-yellow-500' };
    return { score: 4, label: 'Fuerte', color: 'bg-primary' };
});
</script>

<template>
    <Head title="Registro" />

    <div class="bg-background-light min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8 font-display transition-colors duration-300">
        <div class="w-full max-w-[520px] bg-white rounded-xl shadow-[0_8px_40px_rgba(0,0,0,0.08)] overflow-hidden transition-all">
            
            <div class="px-8 pt-10 pb-2 text-center">
                <div class="inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-primary/5 rounded-full mb-6 border border-primary/10">
                    <div class="flex items-center gap-1">
                        <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
                        <span class="text-xs font-bold text-primary uppercase tracking-wide ml-1">Paso 1 de 2</span>
                    </div>
                    <span class="text-xs font-medium text-slate-500 border-l border-slate-200 pl-2">
                        Datos de la cuenta
                    </span>
                </div>

                <h2 class="text-[28px] font-bold text-slate-900 mb-2 leading-tight">Crea tu cuenta</h2>
                <p class="text-slate-500 text-sm max-w-xs mx-auto">
                    Únete a miles de equipos que gestionan sus proyectos eficientemente.
                </p>
            </div>

            <form @submit.prevent="submit" class="px-8 sm:px-10 pb-10 pt-6 space-y-5">
                
                <div class="group">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5 group-focus-within:text-primary transition-colors" for="fullname">
                        Nombre Completo
                    </label>
                    <input
                        id="fullname"
                        type="text"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        class="block w-full rounded-lg border-slate-200 bg-white text-slate-900 shadow-sm focus:border-primary focus:ring-primary/20 sm:text-base py-3 px-4 placeholder:text-slate-400 transition-all duration-200"
                        placeholder="Ej. Juan Pérez"
                    />
                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                </div>

                <div class="group">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5 group-focus-within:text-primary transition-colors" for="email">
                        Correo Laboral
                    </label>
                    <div class="relative">
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            class="block w-full rounded-lg border-slate-200 bg-white text-slate-900 shadow-sm focus:border-primary focus:ring-primary/20 sm:text-base py-3 px-4 pl-10 placeholder:text-slate-400 transition-all duration-200"
                            placeholder="nombre@empresa.com"
                        />
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px] pointer-events-none">mail</span>
                    </div>
                    <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="group">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5 group-focus-within:text-primary transition-colors" for="password">
                            Contraseña
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                type="password"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                                class="block w-full rounded-lg border-slate-200 bg-white text-slate-900 shadow-sm focus:border-primary focus:ring-primary/20 sm:text-base py-3 px-4 placeholder:text-slate-400 transition-all duration-200"
                                placeholder="••••••••"
                            />
                        </div>
                        <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                    </div>
                    
                    <div class="group">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 group-focus-within:text-primary transition-colors" for="confirm-password">
                            Confirmar
                        </label>
                        <input
                            id="confirm-password"
                            type="password"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            class="block w-full rounded-lg border-slate-200 bg-white text-slate-900 shadow-sm focus:border-primary focus:ring-primary/20 sm:text-base py-3 px-4 placeholder:text-slate-400 transition-all duration-200"
                            placeholder="••••••••"
                        />
                         <div v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</div>
                    </div>
                </div>

                <div class="space-y-2 pt-1 transition-all duration-500">
                    <div class="flex gap-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full transition-all duration-500" :class="[passwordStrength.score >= 1 ? passwordStrength.color : 'bg-transparent', 'w-1/4']"></div>
                        <div class="h-full rounded-full transition-all duration-500" :class="[passwordStrength.score >= 2 ? passwordStrength.color : 'bg-transparent', 'w-1/4']"></div>
                        <div class="h-full rounded-full transition-all duration-500" :class="[passwordStrength.score >= 3 ? passwordStrength.color : 'bg-transparent', 'w-1/4']"></div>
                        <div class="h-full rounded-full transition-all duration-500" :class="[passwordStrength.score >= 4 ? passwordStrength.color : 'bg-transparent', 'w-1/4']"></div>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-500">Fortaleza</span>
                        <span class="font-bold transition-colors duration-300" :class="passwordStrength.color.replace('bg-', 'text-')">
                            {{ passwordStrength.label }}
                        </span>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                    class="group w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded-lg shadow-md shadow-primary/20 text-sm font-bold text-white bg-primary hover:bg-[#00857a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200 transform active:scale-[0.98]"
                >
                    <span v-if="!form.processing">Crear Cuenta</span>
                    <span v-else>Procesando...</span>
                    <span v-if="!form.processing" class="material-symbols-outlined ml-2 text-lg group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </button>

            </form>

            <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 text-center">
                <p class="text-sm text-slate-600 font-medium">
                    ¿Ya tienes una cuenta?
                    <Link :href="route('login')" class="font-bold text-primary hover:text-teal-700 underline decoration-transparent hover:decoration-primary transition-all">
                        Iniciar Sesión
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>