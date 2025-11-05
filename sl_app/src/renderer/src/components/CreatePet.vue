<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { Button } from './ui/button'
import { Input } from './ui/input'
import { Label } from './ui/label'
import api, { API_ENDPOINTS } from '../services/api'

const emit = defineEmits(['pet-created', 'cancel'])

const props = defineProps<{
  clientId?: number
}>()

const formData = ref({
  client_id: '',
  name: '',
  age: '',
  species: '',
  breed: '',
  colormark: ''
})

const clients = ref<any[]>([])
const loading = ref(false)
const loadingClients = ref(false)
const error = ref('')
const success = ref('')

// Fetch clients list
const fetchClients = async () => {
  loadingClients.value = true
  try {
    const response = await api.get(API_ENDPOINTS.ADMIN.CLIENTS)
    if (response.data.success) {
      clients.value = response.data.clients
      
      // If clientId prop is provided, pre-select it
      if (props.clientId) {
        formData.value.client_id = props.clientId.toString()
      }
    }
  } catch (err) {
    console.error('Failed to fetch clients:', err)
  } finally {
    loadingClients.value = false
  }
}

onMounted(() => {
  fetchClients()
})

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  success.value = ''

  try {
    const clientId = formData.value.client_id
    const response = await api.post(API_ENDPOINTS.ADMIN.CLIENT_PETS(Number(clientId)), {
      name: formData.value.name,
      age: Number(formData.value.age),
      species: formData.value.species,
      breed: formData.value.breed,
      colormark: formData.value.colormark
    })

    if (response.data.success) {
      success.value = 'Pet created successfully!'
      // Reset form
      formData.value = {
        client_id: props.clientId ? props.clientId.toString() : '',
        name: '',
        age: '',
        species: '',
        breed: '',
        colormark: ''
      }
      // Emit success event
      setTimeout(() => {
        emit('pet-created', response.data.pet)
      }, 1500)
    } else {
      error.value = response.data.message || 'Failed to create pet'
    }
  } catch (err: any) {
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      error.value = Object.values(errors).flat().join(', ')
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'An error occurred while creating the pet'
    }
    console.error('Create pet error:', err)
  } finally {
    loading.value = false
  }
}

const handleCancel = () => {
  formData.value = {
    client_id: props.clientId ? props.clientId.toString() : '',
    name: '',
    age: '',
    species: '',
    breed: '',
    colormark: ''
  }
  error.value = ''
  success.value = ''
  emit('cancel')
}
</script>

<template>
  <Card class="mx-auto max-w-2xl">
    <CardHeader>
      <CardTitle>Create New Pet</CardTitle>
      <CardDescription>Add a new pet for a client</CardDescription>
    </CardHeader>
    <CardContent>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Success Message -->
        <div v-if="success" class="rounded-md border-l-4 border-green-500 bg-green-50 p-4">
          <p class="text-sm text-green-700">{{ success }}</p>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="rounded-md border-l-4 border-destructive bg-destructive/10 p-4">
          <p class="text-sm text-destructive">{{ error }}</p>
        </div>

        <!-- Client Selection -->
        <div class="space-y-2">
          <Label for="client_id">Client *</Label>
          <select
            id="client_id"
            v-model="formData.client_id"
            required
            :disabled="loading || loadingClients || !!clientId"
            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <option value="" disabled>{{ loadingClients ? 'Loading clients...' : 'Select a client' }}</option>
            <option v-for="client in clients" :key="client.id" :value="client.id">
              {{ client.name }} ({{ client.email }})
            </option>
          </select>
        </div>

        <!-- Pet Name -->
        <div class="space-y-2">
          <Label for="name">Pet Name *</Label>
          <Input
            id="name"
            v-model="formData.name"
            type="text"
            placeholder="Enter pet's name"
            required
            :disabled="loading"
          />
        </div>

        <!-- Age -->
        <div class="space-y-2">
          <Label for="age">Age *</Label>
          <Input
            id="age"
            v-model="formData.age"
            type="number"
            min="0"
            placeholder="Enter pet's age"
            required
            :disabled="loading"
          />
        </div>

        <!-- Species -->
        <div class="space-y-2">
          <Label for="species">Species *</Label>
          <select
            id="species"
            v-model="formData.species"
            required
            :disabled="loading"
            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <option value="" disabled>Select species</option>
            <option value="canine">Canine (Dog)</option>
            <option value="feline">Feline (Cat)</option>
          </select>
        </div>

        <!-- Breed -->
        <div class="space-y-2">
          <Label for="breed">Breed *</Label>
          <Input
            id="breed"
            v-model="formData.breed"
            type="text"
            placeholder="Enter pet's breed"
            required
            :disabled="loading"
          />
        </div>

        <!-- Color/Markings -->
        <div class="space-y-2">
          <Label for="colormark">Color/Markings *</Label>
          <Input
            id="colormark"
            v-model="formData.colormark"
            type="text"
            placeholder="Enter color and markings"
            required
            :disabled="loading"
          />
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4">
          <Button type="submit" class="flex-1" :disabled="loading || loadingClients">
            {{ loading ? 'Creating...' : 'Create Pet' }}
          </Button>
          <Button type="button" variant="outline" @click="handleCancel" :disabled="loading">
            Cancel
          </Button>
        </div>
      </form>
    </CardContent>
  </Card>
</template>
