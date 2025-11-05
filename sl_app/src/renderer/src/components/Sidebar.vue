<script setup lang="ts">
import { Menu, Users, Home, LogOut, Dog, Stethoscope, FlaskConical } from 'lucide-vue-next'

defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits(['toggle', 'navigate', 'logout'])

const menuItems = [
  { id: 'dashboard', icon: Home, label: 'Dashboard' },
  { id: 'clients', icon: Users, label: 'Clients' },
  { id: 'create-client', icon: Users, label: 'Create Client' },
  { id: 'create-pet', icon: Dog, label: 'Create Pet' },
  { id: 'consultations', icon: Stethoscope, label: 'Consultations' },
  { id: 'create-consultation', icon: Stethoscope, label: 'Create Consultation' },
  { id: 'labtests', icon: FlaskConical, label: 'Lab Tests' },
  { id: 'create-labtest', icon: FlaskConical, label: 'Create Lab Test' },
]
</script>

<template>
  <div>
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed left-0 top-0 z-40 h-screen transition-transform',
        isOpen ? 'translate-x-0' : '-translate-x-full',
        'w-64 bg-card border-r'
      ]"
    >
      <div class="flex h-full flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between border-b p-4">
          <h2 class="text-lg font-semibold">Menu</h2>
          <button @click="emit('toggle')" class="rounded-md p-2 hover:bg-accent">
            <Menu class="h-5 w-5" />
          </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-1 p-4">
          <button
            v-for="item in menuItems"
            :key="item.id"
            @click="emit('navigate', item.id)"
            class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
          >
            <component :is="item.icon" class="h-5 w-5" />
            <span>{{ item.label }}</span>
          </button>
        </nav>

        <!-- Logout -->
        <div class="border-t p-4">
          <button
            @click="emit('logout')"
            class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-destructive transition-colors hover:bg-destructive/10"
          >
            <LogOut class="h-5 w-5" />
            <span>Logout</span>
          </button>
        </div>
      </div>
    </aside>

    <!-- Overlay -->
    <div
      v-if="isOpen"
      @click="emit('toggle')"
      class="fixed inset-0 z-30 bg-black/50 lg:hidden"
    />
  </div>
</template>
