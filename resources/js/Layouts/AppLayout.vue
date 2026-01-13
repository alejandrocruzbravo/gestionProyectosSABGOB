<script setup>
import { Link, usePage } from '@inertiajs/vue3';

// Obtener el usuario autenticado
const user = usePage().props.auth.user;

</script>

<template>
    <div
        class="bg-[#F9FAFB] font-display text-[#121416] h-screen overflow-hidden flex selection:bg-primary selection:text-white">

        <aside
            class="w-64 bg-[#1A1E23] text-gray-400 flex flex-col flex-shrink-0 transition-all duration-300 relative z-20 shadow-xl">

            <nav class="flex-1 overflow-y-auto py-6 px-3 flex flex-col gap-1">
                <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-600 mb-2">Menú Principal</p>

                <Link :href="route('dashboard')"
                    :class="route().current('dashboard') ? 'bg-primary/10 text-white border-primary' : 'hover:bg-white/5 hover:text-white border-transparent'"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-lg border-l-[3px] transition-all">
                    <span class="material-symbols-outlined"
                        :class="route().current('dashboard') ? 'text-primary' : 'group-hover:text-primary'">dashboard</span>
                    <span class="text-sm font-medium">Inicio</span>
                </Link>

                <Link :href="route('projects.index')"
                    :class="route().current('projects.*') ? 'bg-primary/10 text-white border-primary' : 'hover:bg-white/5 hover:text-white border-transparent'"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-lg border-l-[3px] transition-all">
                    <span class="material-symbols-outlined"
                        :class="route().current('projects.*') ? 'text-primary' : 'group-hover:text-primary'">folder_open</span>
                    <span class="text-sm font-medium">Mis Proyectos</span>
                </Link>

                <Link :href="route('task.index')"
                    :class="route().current('tasks.*') ? 'bg-primary/10 text-white border-primary' : 'hover:bg-white/5 hover:text-white border-transparent'"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-lg border-l-[3px] transition-all">
                    <span class="material-symbols-outlined"
                        :class="route().current('tasks.*') ? 'text-primary' : 'group-hover:text-primary'">task</span>
                    <span class="text-sm font-medium">Mis Tareas</span>
                </Link>

                <Link :href="route('collaborations.index')"
                    :class="route().current('collaborations.*') ? 'bg-primary/10 text-white border-primary' : 'hover:bg-white/5 hover:text-white border-transparent'"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-lg border-l-[3px] transition-all">
                    <span class="material-symbols-outlined"
                        :class="route().current('collaborations.*') ? 'text-primary' : 'group-hover:text-primary'">groups</span>
                    <span class="text-sm font-medium">Colaboraciones</span>
                </Link>
            </nav>

            <div class="p-4 border-t border-gray-800">
                <Link :href="route('logout')" method="post" as="button"
                    class="flex items-center gap-3 w-full hover:bg-white/5 p-2 rounded-lg transition-colors text-left group">
                    <div class="relative">
                        <div
                            class="bg-primary/20 flex items-center justify-center rounded-full size-9 ring-2 ring-gray-700 group-hover:ring-primary transition-all text-primary font-bold uppercase">
                            {{ user.name.charAt(0) }}
                        </div>
                        <span
                            class="absolute bottom-0 right-0 size-2.5 bg-green-500 border-2 border-[#1A1E23] rounded-full"></span>
                    </div>
                    <div class="flex flex-col overflow-hidden">
                        <p class="text-sm font-medium text-white truncate">{{ user.name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                    </div>
                    <span class="material-symbols-outlined ml-auto text-gray-500 text-[18px]">logout</span>
                </Link>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 bg-[#F3F4F6] relative">

            <header
                class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 sticky top-0 z-10 shadow-sm/50">
                <div class="flex items-center gap-4 flex-1">
                    <div class="relative w-full max-w-md group">
                        <span
                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 group-focus-within:text-primary transition-colors">
                            <span class="material-symbols-outlined text-[20px]">search</span>
                        </span>
                        <input
                            class="w-full bg-gray-50 border-none rounded-lg py-2 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all shadow-inner"
                            placeholder="Buscar..." type="text" />
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button class="relative p-2 rounded-full hover:bg-gray-100 text-gray-500 transition-colors">
                        <span class="material-symbols-outlined">notifications</span>
                        <span
                            class="absolute top-2 right-2 size-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>
                    <div class="h-8 w-px bg-gray-200 mx-1"></div>
                    <Link :href="route('projects.index', { create_action: true })"
                        class="bg-primary text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors shadow-lg shadow-primary/20 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Nuevo Proyecto</span>
                    </Link>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-8 scroll-smooth">
                <slot />
            </main>
        </div>
    </div>
</template>