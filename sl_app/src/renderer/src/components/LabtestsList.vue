<template>
  <div class="space-y-4">
    <Card>
      <CardHeader>
        <CardTitle>Lab Tests</CardTitle>
        <CardDescription>View and manage all lab test results</CardDescription>
      </CardHeader>
      <CardContent>
        <!-- Filters -->
        <div class="mb-6 grid gap-4 md:grid-cols-3">
          <div class="space-y-2">
            <Label for="filterClient">Filter by Client</Label>
            <select
              id="filterClient"
              v-model="filterClientId"
              @change="loadLabtests"
              class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            >
              <option value="">Select a client...</option>
              <option v-for="client in clients" :key="client.id" :value="client.id">
                {{ client.name }}
              </option>
            </select>
          </div>

          <div class="space-y-2">
            <Label for="filterPet">Filter by Pet</Label>
            <select
              id="filterPet"
              v-model="filterPetId"
              @change="loadLabtests"
              :disabled="!filterClientId"
              class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            >
              <option value="">All Pets</option>
              <option v-for="pet in filteredPets" :key="pet.id" :value="pet.id">
                {{ pet.name }} - {{ pet.species }}
              </option>
            </select>
          </div>

          <div class="flex items-end">
            <Button @click="resetFilters" variant="outline" class="w-full">
              Reset Filters
            </Button>
          </div>
        </div>

        <!-- No Client Selected -->
        <div v-if="!filterClientId" class="text-center py-12">
          <FlaskConical class="mx-auto h-16 w-16 text-muted-foreground/50" />
          <p class="mt-4 text-lg font-medium text-muted-foreground">Select a Client</p>
          <p class="mt-2 text-sm text-muted-foreground">Please select a client from the dropdown above to view lab tests</p>
        </div>

        <!-- Loading State -->
        <div v-else-if="loading" class="flex items-center justify-center py-8">
          <div class="text-center">
            <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent"></div>
            <p class="mt-2 text-sm text-muted-foreground">Loading lab tests...</p>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="rounded-md bg-destructive/15 p-4 text-sm text-destructive">
          {{ error }}
        </div>

        <!-- Empty State -->
        <div v-else-if="labtests.length === 0" class="text-center py-8">
          <FlaskConical class="mx-auto h-12 w-12 text-muted-foreground" />
          <p class="mt-2 text-sm text-muted-foreground">No lab tests found for this {{ filterPetId ? 'pet' : 'client' }}</p>
        </div>

        <!-- Lab Tests List -->
        <div v-else class="space-y-4">
          <div
            v-for="labtest in labtests"
            :key="labtest.id"
            class="rounded-lg border bg-card p-4 hover:bg-accent/50 transition-colors"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <!-- Lab Test Type Badge -->
                <div class="flex items-center gap-2 mb-2">
                  <span class="inline-flex items-center rounded-full bg-blue-100 px-4 py-1.5 text-sm font-semibold text-blue-700">
                    <FlaskConical class="h-4 w-4 mr-2" />
                    {{ getLabTestLabel(labtest.lab_types) }}
                  </span>
                </div>

                <!-- Consultation and Pet Info -->
                <div class="flex items-center gap-2 mb-3 text-sm text-muted-foreground">
                  <Dog class="h-4 w-4" />
                  <span>{{ labtest.consultation.pet.name }} ({{ labtest.consultation.pet.species }})</span>
                  <span class="mx-2">•</span>
                  <Users class="h-4 w-4" />
                  <span>{{ labtest.consultation.pet.client.name }}</span>
                </div>

                <!-- Consultation Date -->
                <div class="flex items-center gap-2 mb-3 text-sm text-muted-foreground">
                  <Calendar class="h-4 w-4" />
                  <span>Consultation: {{ formatDate(labtest.consultation.consultation_date) }}</span>
                </div>

                <!-- Notes -->
                <div v-if="labtest.notes" class="mb-2">
                  <p class="text-sm font-medium">Notes:</p>
                  <p class="text-sm text-muted-foreground">{{ labtest.notes }}</p>
                </div>

                <!-- Attachments -->
                <div v-if="labtest.photo_result && labtest.photo_result.length > 0" class="mt-2">
                  <p class="text-sm font-medium mb-2">Attachments ({{ labtest.photo_result.length }}):</p>
                  <div class="grid gap-2 grid-cols-3 sm:grid-cols-4 md:grid-cols-6">
                    <div
                      v-for="(photo, index) in labtest.photo_result"
                      :key="index"
                      class="relative aspect-square overflow-hidden rounded-md border bg-muted cursor-pointer hover:ring-2 hover:ring-primary"
                      @click="viewPhoto(photo)"
                    >
                      <img
                        :src="photo"
                        :alt="`Attachment ${index + 1}`"
                        class="h-full w-full object-cover"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="ml-4">
                <Button @click="confirmDelete(labtest)" size="sm" variant="destructive">
                  Delete
                </Button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination Info -->
        <div v-if="labtests.length > 0" class="mt-4 text-sm text-muted-foreground text-center">
          Showing {{ labtests.length }} lab test(s)
        </div>
      </CardContent>
    </Card>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="labtestToDelete"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      @click.self="labtestToDelete = null"
    >
      <Card class="max-w-md w-full">
        <CardHeader>
          <CardTitle class="text-destructive">Delete Lab Test</CardTitle>
          <CardDescription>Are you sure you want to delete this lab test?</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="rounded-md bg-muted p-4">
            <p class="text-sm"><span class="font-medium">Type:</span> {{ getLabTestLabel(labtestToDelete.lab_types) }}</p>
            <p class="text-sm"><span class="font-medium">Pet:</span> {{ labtestToDelete.consultation.pet.name }}</p>
            <p class="text-sm"><span class="font-medium">Client:</span> {{ labtestToDelete.consultation.pet.client.name }}</p>
            <p class="text-sm"><span class="font-medium">Consultation Date:</span> {{ formatDate(labtestToDelete.consultation.consultation_date) }}</p>
          </div>

          <div class="rounded-md bg-destructive/15 p-3 text-sm text-destructive">
            ⚠️ This action cannot be undone. This will permanently delete the lab test and all associated attachments.
          </div>

          <div class="flex gap-3">
            <Button 
              @click="deleteLabtest" 
              variant="destructive" 
              :disabled="deleting"
              class="flex-1"
            >
              {{ deleting ? 'Deleting...' : 'Yes, Delete' }}
            </Button>
            <Button 
              @click="labtestToDelete = null" 
              variant="outline"
              :disabled="deleting"
              class="flex-1"
            >
              Cancel
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Photo Viewer Modal -->
    <div
      v-if="viewingPhoto"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
      @click="viewingPhoto = null"
    >
      <div class="relative max-w-4xl w-full">
        <button
          @click="viewingPhoto = null"
          class="absolute top-4 right-4 rounded-full bg-white/10 p-2 hover:bg-white/20 text-white"
        >
          <X class="h-6 w-6" />
        </button>
        <img
          :src="viewingPhoto"
          alt="Lab test attachment"
          class="w-full h-auto rounded-lg"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { Button } from './ui/button'
import { Label } from './ui/label'
import { FlaskConical, Dog, Users, Calendar, X } from 'lucide-vue-next'
import api, { API_ENDPOINTS } from '../services/api'

interface Client {
  id: number
  name: string
  email: string
}

interface Pet {
  id: number
  name: string
  species: string
  breed: string
  client_id: number
  client: Client
}

interface Consultation {
  id: number
  pet_id: number
  consultation_date: string
  pet: Pet
}

interface Labtest {
  id: number
  consultation_id: number
  lab_types: string
  notes: string | null
  photo_result: string[] | null
  consultation: Consultation
}

const labtests = ref<Labtest[]>([])
const clients = ref<Client[]>([])
const pets = ref<Pet[]>([])
const filterClientId = ref('')
const filterPetId = ref('')
const loading = ref(false)
const error = ref('')
const labtestToDelete = ref<Labtest | null>(null)
const deleting = ref(false)
const viewingPhoto = ref<string | null>(null)

const filteredPets = computed(() => {
  if (!filterClientId.value) return []
  return pets.value.filter(pet => pet.client_id === Number(filterClientId.value))
})

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getLabTestLabel = (type: string) => {
  const labels: Record<string, string> = {
    cbc: 'CBC (Complete Blood Count)',
    microscopy: 'Microscopy',
    bloodchem: 'Blood Chemistry',
    ultrasound: 'Ultrasound',
    xray: 'X-Ray'
  }
  return labels[type] || type
}

const loadClients = async () => {
  try {
    const response = await api.get(API_ENDPOINTS.ADMIN.CLIENTS)
    if (response.data.success) {
      clients.value = response.data.clients
    }
  } catch (err: any) {
    console.error('Error loading clients:', err)
  }
}

const loadAllPets = async () => {
  try {
    const clientPromises = clients.value.map(client =>
      api.get(API_ENDPOINTS.ADMIN.CLIENT_PETS(client.id))
    )
    const responses = await Promise.all(clientPromises)
    
    const allPets: Pet[] = []
    responses.forEach((response, index) => {
      if (response.data.success) {
        const clientPets = response.data.pets.map((pet: any) => ({
          ...pet,
          client: clients.value[index]
        }))
        allPets.push(...clientPets)
      }
    })
    
    pets.value = allPets
  } catch (err: any) {
    console.error('Error loading pets:', err)
  }
}

const loadLabtests = async () => {
  if (!filterClientId.value) {
    labtests.value = []
    return
  }

  loading.value = true
  error.value = ''

  try {
    const allLabtests: Labtest[] = []
    
    const petsToQuery = filterPetId.value 
      ? filteredPets.value.filter(p => p.id === Number(filterPetId.value))
      : filteredPets.value

    for (const pet of petsToQuery) {
      try {
        // Get consultations for this pet
        const consultationsResponse = await api.get(
          API_ENDPOINTS.ADMIN.CLIENT_PET_CONSULTATIONS(pet.client_id, pet.id)
        )
        
        if (consultationsResponse.data.success && consultationsResponse.data.consultations.length > 0) {
          for (const consultation of consultationsResponse.data.consultations) {
            try {
              // Get lab tests for each consultation
              const labtestsResponse = await api.get(
                API_ENDPOINTS.ADMIN.CLIENT_PET_CONSULTATION_LABTESTS(
                  pet.client_id,
                  pet.id,
                  consultation.id
                )
              )
              
              if (labtestsResponse.data.success && labtestsResponse.data.labtests.length > 0) {
                const labtestsWithInfo = labtestsResponse.data.labtests.map((lt: any) => ({
                  ...lt,
                  consultation: {
                    ...consultation,
                    pet: pet
                  }
                }))
                allLabtests.push(...labtestsWithInfo)
              }
            } catch (err) {
              console.error(`Error loading lab tests for consultation ${consultation.id}:`, err)
            }
          }
        }
      } catch (err) {
        console.error(`Error loading consultations for pet ${pet.id}:`, err)
      }
    }
    
    labtests.value = allLabtests
  } catch (err: any) {
    console.error('Error loading lab tests:', err)
    error.value = err.response?.data?.message || 'Failed to load lab tests'
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filterClientId.value = ''
  filterPetId.value = ''
  labtests.value = []
}

const confirmDelete = (labtest: Labtest) => {
  labtestToDelete.value = labtest
}

const deleteLabtest = async () => {
  if (!labtestToDelete.value) return

  deleting.value = true
  error.value = ''

  try {
    const labtest = labtestToDelete.value
    const response = await api.delete(
      API_ENDPOINTS.ADMIN.CLIENT_PET_CONSULTATION_LABTEST(
        labtest.consultation.pet.client_id,
        labtest.consultation.pet_id,
        labtest.consultation_id,
        labtest.id
      )
    )

    if (response.data.success) {
      // Remove from list
      labtests.value = labtests.value.filter(lt => lt.id !== labtest.id)
      
      // Close modal
      labtestToDelete.value = null
    }
  } catch (err: any) {
    console.error('Delete lab test error:', err)
    error.value = err.response?.data?.message || 'Failed to delete lab test'
    labtestToDelete.value = null
  } finally {
    deleting.value = false
  }
}

const viewPhoto = (photoUrl: string) => {
  viewingPhoto.value = photoUrl
}

onMounted(async () => {
  await loadClients()
  await loadAllPets()
})
</script>
