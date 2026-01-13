<script setup>
/*
|--------------------------------------------------------------------------
|Importaciones
|--------------------------------------------------------------------------
*/
import { Head, Link, useForm } from '@inertiajs/vue3';

/*
|--------------------------------------------------------------------------
|Props
|--------------------------------------------------------------------------
*/
defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

/*
|--------------------------------------------------------------------------
|Estados reactivos
|--------------------------------------------------------------------------
*/
const form = useForm({
    email: '',
    password: '',
});

/*
|--------------------------------------------------------------------------
|Funciones Helpers
|--------------------------------------------------------------------------
*/
const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="flex min-h-screen w-full font-display bg-background-light text-[#101818] antialiased overflow-x-hidden selection:bg-primary selection:text-white">
        <div class="hidden lg:flex lg:w-1/2 relative flex-col justify-between overflow-hidden bg-gray-900">
            <div class="absolute inset-0 w-full h-full bg-cover bg-center opacity-90 mix-blend-overlay transition-transform duration-[20s] ease-in-out hover:scale-105" 
                 style="background-image: url('https://images.unsplash.com/photo-1497366216548-37526070297c?q=80&w=2301&auto=format&fit=crop');">
            </div>
            
            <div class="absolute inset-0 bg-gradient-to-br from-primary/80 via-[#0f4c47]/90 to-background-dark/95 mix-blend-multiply"></div>

            <div class="relative z-10 flex flex-col h-full justify-between p-16">
                <div class="max-w-md mt-auto flex flex-col">
                    <div class="mb-6">
                        <span class="material-symbols-outlined text-4xl text-primary/60">format_quote</span>
                    </div>
                    <h2 class="text-3xl font-medium leading-tight text-white mb-4">
                        "La gestión de proyectos no es solo una disciplina, es el arte de convertir ideas en realidad."
                    </h2>
                </div>
            </div>
        </div>

        <div class="flex w-full lg:w-1/2 flex-col justify-center items-center p-6 lg:p-24 relative bg-white">
            
            <div class="lg:hidden absolute top-8 left-8 flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-white">
                    <span class="material-symbols-outlined text-lg">grid_view</span>
                </div>
                <span class="text-xl font-bold text-gray-900">Nexus</span>
            </div>

            <div class="w-full max-w-[420px] flex flex-col gap-8">
                <div class="flex flex-col gap-2">
                    <h1 class="text-[#101818] text-3xl lg:text-4xl font-black tracking-tight">Inicia sesión con tu cuenta</h1>
                    <p class="text-[#5e8d89] text-base font-normal">¡Bienvenido! Por favor ingresa tus datos.</p>
                </div>

                <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="flex flex-col gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="text-[#101818] text-sm font-semibold leading-normal" for="email">Email</label>
                        <div class="relative">
                            <input 
                                id="email" 
                                type="email" 
                                class="w-full rounded-xl border border-[#dae7e6] dark:border-gray-600 bg-white px-4 py-3.5 text-base text-[#101818] placeholder-[#9ca3af] focus:border-primary focus:ring-4 focus:ring-primary/20 outline-none transition-all shadow-sm"
                                placeholder="ejemplo@correo.com"
                                v-model="form.email"
                                required 
                                autofocus 
                                autocomplete="username"
                            />
                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">mail</span>
                        </div>
                        <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-[#101818] text-sm font-semibold leading-normal" for="password">Contraseña</label>
                        <div class="relative">
                            <input 
                                id="password" 
                                type="password" 
                                class="w-full rounded-xl border border-[#dae7e6] dark:border-gray-600 bg-white px-4 py-3.5 text-base text-[#101818] placeholder-[#9ca3af] focus:border-primary focus:ring-4 focus:ring-primary/20 outline-none transition-all shadow-sm"
                                placeholder="••••••••"
                                v-model="form.password"
                                required 
                                autocomplete="current-password"
                            />
                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer hover:text-primary transition-colors">visibility_off</span>
                        </div>
                        <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
                    </div>

                    <div class="flex items-center justify-between mt-1">
                        <Link 
                            v-if="canResetPassword"
                            :href="route('password.request')" 
                            class="text-sm font-bold text-primary hover:text-primary-dark hover:underline underline-offset-4"
                        >
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </div>

                    <button 
                        :class="{ 'opacity-25': form.processing }" 
                        :disabled="form.processing"
                        class="mt-2 flex w-full items-center justify-center rounded-xl bg-primary py-3.5 text-base font-bold text-white shadow-lg shadow-primary/25 transition-all hover:bg-primary-dark hover:shadow-primary/40 hover:-translate-y-0.5 active:translate-y-0 active:shadow-none"
                    >
                        Iniciar sesión
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        ¿No tienes una cuenta? 
                        <Link :href="route('register')" class="font-bold text-primary hover:text-primary-dark hover:underline underline-offset-2">
                            Registrate
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>