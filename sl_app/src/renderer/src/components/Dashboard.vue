<template>
  <div class="min-h-screen bg-background">
    <!-- Sidebar -->
    <Sidebar
      :is-open="sidebarOpen"
      @toggle="sidebarOpen = !sidebarOpen"
      @navigate="handleNavigation"
      @logout="handleLogout"
    />

    <!-- Main Content -->
    <div :class="['transition-all', sidebarOpen ? 'lg:pl-64' : '']">
      <!-- Header -->
      <header class="border-b bg-gradient-to-r from-blue-600 to-purple-600 text-white">
        <div class="container mx-auto flex h-16 items-center justify-between px-4">
          <div class="flex items-center gap-4">
            <button @click="sidebarOpen = !sidebarOpen" class="rounded-md p-2 hover:bg-white/10">
              <Menu class="h-6 w-6" />
            </button>
            <h1 class="text-xl font-semibold">Welcome, {{ user?.name }}!</h1>
          </div>
          <Button @click="handleLogout" variant="secondary" size="sm">
            Logout
          </Button>
        </div>
      </header>

      <!-- Page Content -->
      <main class="container mx-auto p-6">
        <!-- Dashboard View -->
        <div v-if="currentView === 'dashboard'" class="grid gap-6 md:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle>User Information</CardTitle>
              <CardDescription>Your account details</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="flex justify-between border-b pb-2">
                <span class="font-medium text-muted-foreground">Name:</span>
                <span class="font-medium">{{ user?.name }}</span>
              </div>
              <div class="flex justify-between border-b pb-2">
                <span class="font-medium text-muted-foreground">Username:</span>
                <span class="font-medium">{{ user?.username }}</span>
              </div>
              <div class="flex justify-between border-b pb-2">
                <span class="font-medium text-muted-foreground">Email:</span>
                <span class="font-medium">{{ user?.email }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-medium text-muted-foreground">Role:</span>
                <span 
                  class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase"
                  :class="user?.role === 'admin' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'"
                >
                  {{ user?.role }}
                </span>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Sniff & Lick Veterinary System</CardTitle>
              <CardDescription>Welcome to the management system</CardDescription>
            </CardHeader>
            <CardContent>
              <p class="text-muted-foreground mb-3">
                You are now logged in to the veterinary management system.
              </p>
              <p class="text-muted-foreground">
                Use the sidebar to navigate through different features.
              </p>
            </CardContent>
          </Card>
        </div>

        <!-- Clients View -->
        <div v-else-if="currentView === 'clients'">
          <Card>
            <CardHeader>
              <CardTitle>Clients List</CardTitle>
              <CardDescription>Manage all clients in the system</CardDescription>
            </CardHeader>
            <CardContent>
              <p class="text-muted-foreground">Clients list feature coming soon...</p>
            </CardContent>
          </Card>
        </div>

        <!-- Create Client View -->
        <div v-else-if="currentView === 'create-client'">
          <CreateClient 
            @client-created="handleClientCreated"
            @cancel="currentView = 'dashboard'"
          />
        </div>

        <!-- Create Pet View -->
        <div v-else-if="currentView === 'create-pet'">
          <CreatePet 
            @pet-created="handlePetCreated"
            @cancel="currentView = 'dashboard'"
          />
        </div>

        <!-- Create Consultation View -->
        <div v-else-if="currentView === 'create-consultation'">
          <CreateConsultation 
            @consultation-created="handleConsultationCreated"
            @cancel="currentView = 'dashboard'"
          />
        </div>

        <!-- Create Lab Test View -->
        <div v-else-if="currentView === 'create-labtest'">
          <CreateLabtest 
            @labtest-created="handleLabtestCreated"
            @cancel="currentView = 'dashboard'"
          />
        </div>

        <!-- Consultations List View -->
        <div v-else-if="currentView === 'consultations'">
          <ConsultationsList />
        </div>

        <!-- Lab Tests List View -->
        <div v-else-if="currentView === 'labtests'">
          <LabtestsList />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Menu } from 'lucide-vue-next'
import { authService } from '../services/auth'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { Button } from './ui/button'
import Sidebar from './Sidebar.vue'
import CreateClient from './CreateClient.vue'
import CreatePet from './CreatePet.vue'
import CreateConsultation from './CreateConsultation.vue'
import CreateLabtest from './CreateLabtest.vue'
import ConsultationsList from './ConsultationsList.vue'
import LabtestsList from './LabtestsList.vue'

const emit = defineEmits(['logout'])

const user = ref<any>(null)
const sidebarOpen = ref(true)
const currentView = ref<'dashboard' | 'clients' | 'create-client' | 'create-pet' | 'create-consultation' | 'create-labtest' | 'consultations' | 'labtests'>('dashboard')

onMounted(() => {
  user.value = authService.getCurrentUser()
})

const handleLogout = async () => {
  try {
    await authService.logout()
    emit('logout')
  } catch (error) {
    console.error('Logout error:', error)
    emit('logout')
  }
}

const handleNavigation = (viewId: string) => {
  currentView.value = viewId as any
  // Close sidebar on mobile after navigation
  if (window.innerWidth < 1024) {
    sidebarOpen.value = false
  }
}

const handleClientCreated = (client: any) => {
  console.log('Client created:', client)
  // Switch back to clients list or dashboard
  currentView.value = 'clients'
}

const handlePetCreated = (pet: any) => {
  console.log('Pet created:', pet)
  // Switch back to dashboard or pets list
  currentView.value = 'dashboard'
}

const handleConsultationCreated = (consultation: any) => {
  console.log('Consultation created:', consultation)
  // Switch back to dashboard
  currentView.value = 'dashboard'
}

const handleLabtestCreated = (labtest: any) => {
  console.log('Lab test created:', labtest)
  // Switch back to dashboard
  currentView.value = 'dashboard'
}
</script>
