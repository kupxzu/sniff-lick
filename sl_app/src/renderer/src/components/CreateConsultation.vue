<template>
  <Card class="mx-auto max-w-2xl">
    <CardHeader>
      <CardTitle>Create New Consultation</CardTitle>
      <CardDescription>Record a new consultation for a pet</CardDescription>
    </CardHeader>
    <CardContent>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Client Selection -->
        <div class="space-y-2">
          <Label for="client">Client</Label>
          <select
            id="client"
            v-model="selectedClientId"
            @change="loadClientPets"
            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            required
          >
            <option value="">Select a client...</option>
            <option v-for="client in clients" :key="client.id" :value="client.id">
              {{ client.name }} ({{ client.email }})
            </option>
          </select>
        </div>

        <!-- Pet Selection -->
        <div class="space-y-2">
          <Label for="pet">Pet</Label>
          <select
            id="pet"
            v-model="formData.pet_id"
            :disabled="!selectedClientId || loadingPets"
            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            required
          >
            <option value="">{{ loadingPets ? 'Loading pets...' : 'Select a pet...' }}</option>
            <option v-for="pet in pets" :key="pet.id" :value="pet.id">
              {{ pet.name }} - {{ pet.species }} ({{ pet.breed }})
            </option>
          </select>
        </div>

        <!-- Consultation Date -->
        <div class="space-y-2">
          <Label for="date">Consultation Date & Time</Label>
          <Input
            id="date"
            v-model="formData.consultation_date"
            type="datetime-local"
            required
          />
        </div>

        <!-- Weight -->
        <div class="space-y-2">
          <Label for="weight">Weight (kg)</Label>
          <Input
            id="weight"
            v-model="formData.weight"
            type="number"
            step="0.01"
            min="0"
            max="999.99"
            placeholder="e.g., 15.50"
          />
        </div>

        <!-- Temperature -->
        <div class="space-y-2">
          <Label for="temperature">Temperature (°C)</Label>
          <Input
            id="temperature"
            v-model="formData.temperature"
            type="number"
            step="0.01"
            min="0"
            max="99.99"
            placeholder="e.g., 38.50"
          />
        </div>

        <!-- Complaint -->
        <div class="space-y-2">
          <Label for="complaint">Complaint</Label>
          <textarea
            id="complaint"
            v-model="formData.complaint"
            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            placeholder="Describe the pet's symptoms or complaints..."
          />
        </div>

        <!-- Diagnosis -->
        <div class="space-y-2">
          <Label for="diagnosis">Diagnosis</Label>
          <textarea
            id="diagnosis"
            v-model="formData.diagnosis"
            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            placeholder="Enter the diagnosis..."
          />
        </div>

        <!-- Error Message -->
        <div v-if="error" class="rounded-md bg-destructive/15 p-3 text-sm text-destructive">
          {{ error }}
        </div>

        <!-- Success Message -->
        <div v-if="success" class="rounded-md bg-green-50 p-3 text-sm text-green-700">
          {{ success }}
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
          <Button type="submit" :disabled="loading" class="flex-1">
            {{ loading ? 'Creating...' : 'Create Consultation' }}
          </Button>
          <Button type="button" variant="outline" @click="emit('cancel')">
            Cancel
          </Button>
        </div>
      </form>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { Button } from './ui/button'
import { Input } from './ui/input'
import { Label } from './ui/label'
import api, { API_ENDPOINTS } from '../services/api'

const emit = defineEmits(['consultation-created', 'cancel'])

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
}

const clients = ref<Client[]>([])
const pets = ref<Pet[]>([])
const selectedClientId = ref<string>('')
const loading = ref(false)
const loadingPets = ref(false)
const error = ref('')
const success = ref('')

const formData = ref({
  pet_id: '',
  consultation_date: '',
  weight: '',
  temperature: '',
  complaint: '',
  diagnosis: ''
})

// Set default date to now
const setDefaultDate = () => {
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')
  const hours = String(now.getHours()).padStart(2, '0')
  const minutes = String(now.getMinutes()).padStart(2, '0')
  formData.value.consultation_date = `${year}-${month}-${day}T${hours}:${minutes}`
}

const loadClients = async () => {
  try {
    const response = await api.get(API_ENDPOINTS.ADMIN.CLIENTS)
    if (response.data.success) {
      clients.value = response.data.clients
    }
  } catch (err: any) {
    console.error('Error loading clients:', err)
    error.value = err.response?.data?.message || 'Failed to load clients'
  }
}

const loadClientPets = async () => {
  if (!selectedClientId.value) {
    pets.value = []
    formData.value.pet_id = ''
    return
  }

  loadingPets.value = true
  error.value = ''
  
  try {
    const response = await api.get(API_ENDPOINTS.ADMIN.CLIENT_PETS(Number(selectedClientId.value)))
    if (response.data.success) {
      pets.value = response.data.pets
      // Reset pet selection when client changes
      formData.value.pet_id = ''
    }
  } catch (err: any) {
    console.error('Error loading pets:', err)
    error.value = err.response?.data?.message || 'Failed to load pets'
    pets.value = []
  } finally {
    loadingPets.value = false
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  success.value = ''

  try {
    // Prepare data for submission
    const submitData: any = {
      pet_id: Number(formData.value.pet_id),
      consultation_date: formData.value.consultation_date
    }

    // Add optional fields only if they have values
    if (formData.value.weight) {
      submitData.weight = Number(formData.value.weight)
    }
    if (formData.value.temperature) {
      submitData.temperature = Number(formData.value.temperature)
    }
    if (formData.value.complaint) {
      submitData.complaint = formData.value.complaint
    }
    if (formData.value.diagnosis) {
      submitData.diagnosis = formData.value.diagnosis
    }

    // Use the hierarchical endpoint for creating consultation
    const clientId = Number(selectedClientId.value)
    const petId = Number(formData.value.pet_id)
    
    const response = await api.post(
      API_ENDPOINTS.ADMIN.CLIENT_PET_CONSULTATIONS(clientId, petId),
      submitData
    )

    if (response.data.success) {
      success.value = 'Consultation created successfully! ✨'
      emit('consultation-created', response.data.consultation)
      
      // Reset form after a delay
      setTimeout(() => {
        formData.value = {
          pet_id: '',
          consultation_date: '',
          weight: '',
          temperature: '',
          complaint: '',
          diagnosis: ''
        }
        selectedClientId.value = ''
        pets.value = []
        setDefaultDate()
        success.value = ''
      }, 2000)
    }
  } catch (err: any) {
    console.error('Consultation creation error:', err)
    error.value = err.response?.data?.message || 'Failed to create consultation'
    
    // Show validation errors if available
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      error.value = Object.values(errors).flat().join(', ')
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadClients()
  setDefaultDate()
})
</script>
