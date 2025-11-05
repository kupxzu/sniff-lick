<template>
  <div class="space-y-4">
    <Card>
      <CardHeader>
        <CardTitle>Pet Consultations</CardTitle>
        <CardDescription>View and manage all pet consultations</CardDescription>
      </CardHeader>
      <CardContent>
        <!-- Filters -->
        <div class="mb-6 grid gap-4 md:grid-cols-4">
          <div class="space-y-2">
            <Label for="filterClient">Filter by Client</Label>
            <select
              id="filterClient"
              v-model="filterClientId"
              @change="loadConsultations"
              class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            >
              <option value="">All Clients</option>
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
              @change="loadConsultations"
              :disabled="!filterClientId"
              class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            >
              <option value="">All Pets</option>
              <option v-for="pet in filteredPets" :key="pet.id" :value="pet.id">
                {{ pet.name }} - {{ pet.species }}
              </option>
            </select>
          </div>

          <div class="space-y-2">
            <Label for="filterView">View</Label>
            <select
              id="filterView"
              v-model="viewMode"
              class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            >
              <option value="consultations">Consultations</option>
              <option value="labtests">Lab Tests</option>
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
          <Users class="mx-auto h-16 w-16 text-muted-foreground/50" />
          <p class="mt-4 text-lg font-medium text-muted-foreground">Select a Client</p>
          <p class="mt-2 text-sm text-muted-foreground">Please select a client from the dropdown above to view consultations</p>
        </div>

        <!-- Loading State -->
        <div v-else-if="loading" class="flex items-center justify-center py-8">
          <div class="text-center">
            <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent"></div>
            <p class="mt-2 text-sm text-muted-foreground">Loading consultations...</p>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="rounded-md bg-destructive/15 p-4 text-sm text-destructive">
          {{ error }}
        </div>

        <!-- Empty State -->
        <div v-else-if="viewMode === 'consultations' && consultations.length === 0" class="text-center py-8">
          <Stethoscope class="mx-auto h-12 w-12 text-muted-foreground" />
          <p class="mt-2 text-sm text-muted-foreground">No consultations found for this {{ filterPetId ? 'pet' : 'client' }}</p>
        </div>

        <!-- Empty State for Lab Tests -->
        <div v-else-if="viewMode === 'labtests' && labtests.length === 0" class="text-center py-8">
          <FlaskConical class="mx-auto h-12 w-12 text-muted-foreground" />
          <p class="mt-2 text-sm text-muted-foreground">No lab tests found for this {{ filterPetId ? 'pet' : 'client' }}</p>
        </div>

        <!-- Consultations List -->
        <div v-else-if="viewMode === 'consultations'" class="space-y-4">
          <div
            v-for="consultation in consultations"
            :key="consultation.id"
            class="rounded-lg border bg-card p-4 hover:bg-accent/50 transition-colors"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <!-- Pet and Client Info -->
                <div class="flex items-center gap-2 mb-2">
                  <Dog class="h-5 w-5 text-primary" />
                  <h3 class="font-semibold text-lg">
                    {{ consultation.pet.name }}
                  </h3>
                  <span class="text-sm text-muted-foreground">
                    ({{ consultation.pet.species }} - {{ consultation.pet.breed }})
                  </span>
                </div>

                <div class="flex items-center gap-2 mb-3 text-sm text-muted-foreground">
                  <Users class="h-4 w-4" />
                  <span>Client: {{ consultation.pet.client.name }}</span>
                  <span class="mx-2">•</span>
                  <Calendar class="h-4 w-4" />
                  <span>{{ formatDate(consultation.consultation_date) }}</span>
                </div>

                <!-- Consultation Details -->
                <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4 mb-3">
                  <div v-if="consultation.weight" class="flex items-center gap-2 text-sm">
                    <span class="font-medium">Weight:</span>
                    <span>{{ consultation.weight }} kg</span>
                  </div>
                  <div v-if="consultation.temperature" class="flex items-center gap-2 text-sm">
                    <span class="font-medium">Temp:</span>
                    <span>{{ consultation.temperature }}°C</span>
                  </div>
                  <div v-if="consultation.labtests && consultation.labtests.length > 0" class="flex items-center gap-2 text-sm">
                    <FlaskConical class="h-4 w-4 text-blue-500" />
                    <span>{{ consultation.labtests.length }} Lab Test(s)</span>
                  </div>
                  <div v-if="consultation.treatments && consultation.treatments.length > 0" class="flex items-center gap-2 text-sm">
                    <Pill class="h-4 w-4 text-green-500" />
                    <span>{{ consultation.treatments.length }} Treatment(s)</span>
                  </div>
                </div>

                <!-- Complaint -->
                <div v-if="consultation.complaint" class="mb-2">
                  <p class="text-sm font-medium">Complaint:</p>
                  <p class="text-sm text-muted-foreground">{{ consultation.complaint }}</p>
                </div>

                <!-- Diagnosis -->
                <div v-if="consultation.diagnosis" class="mb-2">
                  <p class="text-sm font-medium">Diagnosis:</p>
                  <p class="text-sm text-muted-foreground">{{ consultation.diagnosis }}</p>
                </div>

                <!-- Lab Tests Details -->
                <div v-if="consultation.labtests && consultation.labtests.length > 0" class="mt-3 pt-3 border-t">
                  <p class="text-sm font-medium mb-2">Lab Tests:</p>
                  <div class="flex flex-wrap gap-2">
                    <span
                      v-for="labtest in consultation.labtests"
                      :key="labtest.id"
                      class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700"
                    >
                      {{ getLabTestLabel(labtest.lab_types) }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="ml-4 flex flex-col gap-2">
                <Button @click="viewDetails(consultation)" size="sm" variant="outline">
                  View Details
                </Button>
                <Button @click="confirmDelete(consultation)" size="sm" variant="destructive">
                  Delete
                </Button>
              </div>
            </div>
          </div>
        </div>

        <!-- Lab Tests List -->
        <div v-else-if="viewMode === 'labtests'" class="space-y-4">
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
                <Button @click="confirmDeleteLabtest(labtest)" size="sm" variant="destructive">
                  Delete
                </Button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination Info -->
        <div v-if="viewMode === 'consultations' && consultations.length > 0" class="mt-4 text-sm text-muted-foreground text-center">
          Showing {{ consultations.length }} consultation(s)
        </div>
        <div v-if="viewMode === 'labtests' && labtests.length > 0" class="mt-4 text-sm text-muted-foreground text-center">
          Showing {{ labtests.length }} lab test(s)
        </div>
      </CardContent>
    </Card>

    <!-- Lab Test Delete Confirmation Modal -->
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

    <!-- Delete Confirmation Modal -->
    <div
      v-if="selectedConsultation"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      @click.self="selectedConsultation = null"
    >
      <Card class="max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <CardHeader>
          <div class="flex items-center justify-between">
            <CardTitle>Consultation Details</CardTitle>
            <button
              @click="selectedConsultation = null"
              class="rounded-md p-2 hover:bg-accent"
            >
              <X class="h-5 w-5" />
            </button>
          </div>
        </CardHeader>
        <CardContent class="space-y-4">
          <!-- Pet Info -->
          <div class="rounded-md bg-muted p-4">
            <h3 class="font-semibold mb-2">Pet Information</h3>
            <div class="grid gap-2 md:grid-cols-2">
              <div><span class="font-medium">Name:</span> {{ selectedConsultation.pet.name }}</div>
              <div><span class="font-medium">Species:</span> {{ selectedConsultation.pet.species }}</div>
              <div><span class="font-medium">Breed:</span> {{ selectedConsultation.pet.breed }}</div>
              <div><span class="font-medium">Owner:</span> {{ selectedConsultation.pet.client.name }}</div>
            </div>
          </div>

          <!-- Consultation Info -->
          <div class="rounded-md bg-muted p-4">
            <h3 class="font-semibold mb-2">Consultation Information</h3>
            <div class="grid gap-2 md:grid-cols-2">
              <div><span class="font-medium">Date:</span> {{ formatDate(selectedConsultation.consultation_date) }}</div>
              <div v-if="selectedConsultation.weight"><span class="font-medium">Weight:</span> {{ selectedConsultation.weight }} kg</div>
              <div v-if="selectedConsultation.temperature"><span class="font-medium">Temperature:</span> {{ selectedConsultation.temperature }}°C</div>
            </div>
            <div v-if="selectedConsultation.complaint" class="mt-2">
              <span class="font-medium">Complaint:</span>
              <p class="mt-1">{{ selectedConsultation.complaint }}</p>
            </div>
            <div v-if="selectedConsultation.diagnosis" class="mt-2">
              <span class="font-medium">Diagnosis:</span>
              <p class="mt-1">{{ selectedConsultation.diagnosis }}</p>
            </div>
          </div>

          <!-- Lab Tests -->
          <div v-if="selectedConsultation.labtests && selectedConsultation.labtests.length > 0" class="rounded-md bg-muted p-4">
            <h3 class="font-semibold mb-2 flex items-center gap-2">
              <FlaskConical class="h-5 w-5" />
              Lab Tests ({{ selectedConsultation.labtests.length }})
            </h3>
            <div class="space-y-2">
              <div
                v-for="labtest in selectedConsultation.labtests"
                :key="labtest.id"
                class="rounded-md bg-background p-3"
              >
                <div class="font-medium">{{ getLabTestLabel(labtest.lab_types) }}</div>
                <div v-if="labtest.notes" class="text-sm text-muted-foreground mt-1">{{ labtest.notes }}</div>
                <div v-if="labtest.photo_result && labtest.photo_result.length > 0" class="mt-2">
                  <p class="text-sm font-medium mb-1">Attachments: {{ labtest.photo_result.length }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Treatments -->
          <div v-if="selectedConsultation.treatments && selectedConsultation.treatments.length > 0" class="rounded-md bg-muted p-4">
            <h3 class="font-semibold mb-2 flex items-center gap-2">
              <Pill class="h-5 w-5" />
              Treatments ({{ selectedConsultation.treatments.length }})
            </h3>
            <div class="space-y-2">
              <div
                v-for="treatment in selectedConsultation.treatments"
                :key="treatment.id"
                class="rounded-md bg-background p-3 text-sm"
              >
                {{ treatment.treatment_type || 'Treatment details' }}
              </div>
            </div>
          </div>

          <!-- Prescriptions -->
          <div v-if="selectedConsultation.prescriptions && selectedConsultation.prescriptions.length > 0" class="rounded-md bg-muted p-4">
            <h3 class="font-semibold mb-2">Prescriptions ({{ selectedConsultation.prescriptions.length }})</h3>
            <div class="space-y-2">
              <div
                v-for="prescription in selectedConsultation.prescriptions"
                :key="prescription.id"
                class="rounded-md bg-background p-3 text-sm"
              >
                {{ prescription.medication || 'Prescription details' }}
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="consultationToDelete"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      @click.self="consultationToDelete = null"
    >
      <Card class="max-w-md w-full">
        <CardHeader>
          <CardTitle class="text-destructive">Delete Consultation</CardTitle>
          <CardDescription>Are you sure you want to delete this consultation?</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="rounded-md bg-muted p-4">
            <p class="text-sm"><span class="font-medium">Pet:</span> {{ consultationToDelete.pet.name }}</p>
            <p class="text-sm"><span class="font-medium">Client:</span> {{ consultationToDelete.pet.client.name }}</p>
            <p class="text-sm"><span class="font-medium">Date:</span> {{ formatDate(consultationToDelete.consultation_date) }}</p>
            <p v-if="consultationToDelete.diagnosis" class="text-sm"><span class="font-medium">Diagnosis:</span> {{ consultationToDelete.diagnosis }}</p>
          </div>

          <div class="rounded-md bg-destructive/15 p-3 text-sm text-destructive">
            ⚠️ This action cannot be undone. This will permanently delete the consultation and all associated lab tests, treatments, and prescriptions.
          </div>

          <div class="flex gap-3">
            <Button 
              @click="deleteConsultation" 
              variant="destructive" 
              :disabled="deleting"
              class="flex-1"
            >
              {{ deleting ? 'Deleting...' : 'Yes, Delete' }}
            </Button>
            <Button 
              @click="consultationToDelete = null" 
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
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { Button } from './ui/button'
import { Label } from './ui/label'
import { Stethoscope, Dog, Users, Calendar, FlaskConical, Pill, X } from 'lucide-vue-next'
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

interface Labtest {
  id: number
  consultation_id: number
  lab_types: string
  notes: string | null
  photo_result: string[] | null
  consultation: Consultation
}

interface Labtest {
  id: number
  lab_types: string
  notes: string | null
  photo_result: string[] | null
}

interface Treatment {
  id: number
  treatment_type: string | null
}

interface Prescription {
  id: number
  medication: string | null
}

interface Consultation {
  id: number
  pet_id: number
  consultation_date: string
  weight: number | null
  temperature: number | null
  complaint: string | null
  diagnosis: string | null
  pet: Pet
  labtests?: Labtest[]
  treatments?: Treatment[]
  prescriptions?: Prescription[]
}

const consultations = ref<Consultation[]>([])
const labtests = ref<Labtest[]>([])
const clients = ref<Client[]>([])
const pets = ref<Pet[]>([])
const filterClientId = ref('')
const filterPetId = ref('')
const viewMode = ref<'consultations' | 'labtests'>('consultations')
const loading = ref(false)
const error = ref('')
const selectedConsultation = ref<Consultation | null>(null)
const consultationToDelete = ref<Consultation | null>(null)
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
    // Load pets for all clients
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

const loadConsultations = async () => {
  // Don't load if no client is selected
  if (!filterClientId.value) {
    consultations.value = []
    labtests.value = []
    return
  }

  loading.value = true
  error.value = ''

  try {
    if (filterPetId.value) {
      // Load consultations for specific pet
      const response = await api.get(
        API_ENDPOINTS.ADMIN.CLIENT_PET_CONSULTATIONS(
          Number(filterClientId.value),
          Number(filterPetId.value)
        )
      )
      if (response.data.success) {
        consultations.value = response.data.consultations
        
        // Load lab tests for these consultations
        await loadLabtestsForConsultations(response.data.consultations)
      }
    } else {
      // Load all consultations for all pets of the selected client
      const allConsultations: Consultation[] = []
      
      for (const pet of filteredPets.value) {
        try {
          const response = await api.get(
            API_ENDPOINTS.ADMIN.CLIENT_PET_CONSULTATIONS(pet.client_id, pet.id)
          )
          if (response.data.success && response.data.consultations.length > 0) {
            // Add pet and client info to consultations
            const consultationsWithPetInfo = response.data.consultations.map((c: any) => ({
              ...c,
              pet: pet
            }))
            allConsultations.push(...consultationsWithPetInfo)
          }
        } catch (err) {
          console.error(`Error loading consultations for pet ${pet.id}:`, err)
        }
      }
      
      // Sort by date descending
      allConsultations.sort((a, b) => 
        new Date(b.consultation_date).getTime() - new Date(a.consultation_date).getTime()
      )
      
      consultations.value = allConsultations
      
      // Load lab tests for all consultations
      await loadLabtestsForConsultations(allConsultations)
    }
  } catch (err: any) {
    console.error('Error loading consultations:', err)
    error.value = err.response?.data?.message || 'Failed to load consultations'
  } finally {
    loading.value = false
  }
}

const loadLabtestsForConsultations = async (consultationsList: Consultation[]) => {
  const allLabtests: Labtest[] = []
  
  for (const consultation of consultationsList) {
    try {
      const response = await api.get(
        API_ENDPOINTS.ADMIN.CLIENT_PET_CONSULTATION_LABTESTS(
          consultation.pet.client_id,
          consultation.pet_id,
          consultation.id
        )
      )
      
      if (response.data.success && response.data.labtests.length > 0) {
        const labtestsWithInfo = response.data.labtests.map((lt: any) => ({
          ...lt,
          consultation: {
            ...consultation,
            pet: consultation.pet
          }
        }))
        allLabtests.push(...labtestsWithInfo)
      }
    } catch (err) {
      console.error(`Error loading lab tests for consultation ${consultation.id}:`, err)
    }
  }
  
  labtests.value = allLabtests
}

const resetFilters = () => {
  filterClientId.value = ''
  filterPetId.value = ''
  consultations.value = []
  labtests.value = []
  viewMode.value = 'consultations'
}

const viewDetails = (consultation: Consultation) => {
  selectedConsultation.value = consultation
}

const confirmDelete = (consultation: Consultation) => {
  consultationToDelete.value = consultation
}

const confirmDeleteLabtest = (labtest: Labtest) => {
  labtestToDelete.value = labtest
}

const viewPhoto = (photoUrl: string) => {
  viewingPhoto.value = photoUrl
}

const deleteConsultation = async () => {
  if (!consultationToDelete.value) return

  deleting.value = true
  error.value = ''

  try {
    const consultation = consultationToDelete.value
    const response = await api.delete(
      API_ENDPOINTS.ADMIN.CLIENT_PET_CONSULTATION(
        consultation.pet.client_id,
        consultation.pet_id,
        consultation.id
      )
    )

    if (response.data.success) {
      // Remove from list
      consultations.value = consultations.value.filter(c => c.id !== consultation.id)
      
      // Close modal
      consultationToDelete.value = null
      
      // Show success message briefly
      const successMessage = response.data.message || 'Consultation deleted successfully'
      console.log(successMessage)
    }
  } catch (err: any) {
    console.error('Delete consultation error:', err)
    error.value = err.response?.data?.message || 'Failed to delete consultation'
    consultationToDelete.value = null
  } finally {
    deleting.value = false
  }
}

const deleteLabtest = async () => {
  if (!labtestToDelete.value) return

  deleting.value = true
  error.value = ''

  try {
    const labtest = labtestToDelete.value
    const response = await api.delete(
      API_ENDPOINTS.ADMIN.CLIENT_PET_CONSULTATION_LABTEST(
        labtest.consultation!.pet.client_id,
        labtest.consultation!.pet_id,
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

onMounted(async () => {
  await loadClients()
  await loadAllPets()
  // Don't load consultations until a client is selected
})
</script>
