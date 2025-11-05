<template>
  <Card class="mx-auto max-w-2xl">
    <CardHeader>
      <CardTitle>Create New Lab Test</CardTitle>
      <CardDescription>Add a lab test result for a pet consultation</CardDescription>
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
            v-model="selectedPetId"
            @change="loadPetConsultations"
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

        <!-- Consultation Selection -->
        <div class="space-y-2">
          <Label for="consultation">Consultation</Label>
          <select
            id="consultation"
            v-model="formData.consultation_id"
            :disabled="!selectedPetId || loadingConsultations"
            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            required
          >
            <option value="">{{ loadingConsultations ? 'Loading consultations...' : 'Select a consultation...' }}</option>
            <option v-for="consultation in consultations" :key="consultation.id" :value="consultation.id">
              {{ formatDate(consultation.consultation_date) }} - {{ consultation.diagnosis || 'No diagnosis' }}
            </option>
          </select>
        </div>

        <!-- Lab Type -->
        <div class="space-y-2">
          <Label for="labType">Lab Test Type</Label>
          <select
            id="labType"
            v-model="formData.lab_types"
            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            required
          >
            <option value="">Select lab test type...</option>
            <option value="cbc">CBC (Complete Blood Count)</option>
            <option value="microscopy">Microscopy</option>
            <option value="bloodchem">Blood Chemistry</option>
            <option value="ultrasound">Ultrasound</option>
            <option value="xray">X-Ray</option>
          </select>
        </div>

        <!-- Notes -->
        <div class="space-y-2">
          <Label for="notes">Notes / Results</Label>
          <textarea
            id="notes"
            v-model="formData.notes"
            class="flex min-h-[120px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            placeholder="Enter lab test results, findings, or notes..."
          />
        </div>

        <!-- Photo/Attachment Upload -->
        <div class="space-y-2">
          <Label for="photos">Photos / Attachments</Label>
          <div class="space-y-3">
            <input
              id="photos"
              type="file"
              @change="handleFileSelect"
              accept="image/*"
              multiple
              class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            />
            
            <!-- Selected Files Preview -->
            <div v-if="selectedFiles.length > 0" class="space-y-2">
              <p class="text-sm text-muted-foreground">Selected files ({{ selectedFiles.length }}):</p>
              <div class="grid gap-2 grid-cols-2 sm:grid-cols-3">
                <div
                  v-for="(file, index) in selectedFiles"
                  :key="index"
                  class="relative group"
                >
                  <div class="relative aspect-square overflow-hidden rounded-md border bg-muted">
                    <img
                      v-if="file.preview"
                      :src="file.preview"
                      :alt="file.name"
                      class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full items-center justify-center text-muted-foreground">
                      <span class="text-xs">📄</span>
                    </div>
                  </div>
                  <button
                    type="button"
                    @click="removeFile(index)"
                    class="absolute -top-2 -right-2 h-6 w-6 rounded-full bg-destructive text-destructive-foreground hover:bg-destructive/90 flex items-center justify-center text-xs font-bold shadow-md"
                  >
                    ×
                  </button>
                  <p class="mt-1 text-xs truncate text-muted-foreground">{{ file.name }}</p>
                </div>
              </div>
            </div>
            
            <p class="text-xs text-muted-foreground">
              💡 Upload lab test results, X-rays, ultrasound images, etc. (Max 10 files)
            </p>
          </div>
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
            {{ loading ? 'Creating...' : 'Create Lab Test' }}
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
import { Label } from './ui/label'
import api, { API_ENDPOINTS } from '../services/api'

const emit = defineEmits(['labtest-created', 'cancel'])

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

interface Consultation {
  id: number
  consultation_date: string
  diagnosis: string | null
  pet_id: number
}

const clients = ref<Client[]>([])
const pets = ref<Pet[]>([])
const consultations = ref<Consultation[]>([])
const selectedClientId = ref<string>('')
const selectedPetId = ref<string>('')
const loading = ref(false)
const loadingPets = ref(false)
const loadingConsultations = ref(false)
const error = ref('')
const success = ref('')

const formData = ref({
  consultation_id: '',
  lab_types: '',
  notes: '',
  photo_result: [] as string[]
})

interface FileWithPreview {
  file: File
  name: string
  preview: string | null
}

const selectedFiles = ref<FileWithPreview[]>([])

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
    consultations.value = []
    selectedPetId.value = ''
    formData.value.consultation_id = ''
    return
  }

  loadingPets.value = true
  error.value = ''
  
  try {
    const response = await api.get(API_ENDPOINTS.ADMIN.CLIENT_PETS(Number(selectedClientId.value)))
    if (response.data.success) {
      pets.value = response.data.pets
      selectedPetId.value = ''
      consultations.value = []
      formData.value.consultation_id = ''
    }
  } catch (err: any) {
    console.error('Error loading pets:', err)
    error.value = err.response?.data?.message || 'Failed to load pets'
    pets.value = []
  } finally {
    loadingPets.value = false
  }
}

const loadPetConsultations = async () => {
  if (!selectedPetId.value || !selectedClientId.value) {
    consultations.value = []
    formData.value.consultation_id = ''
    return
  }

  loadingConsultations.value = true
  error.value = ''
  
  try {
    const response = await api.get(
      API_ENDPOINTS.ADMIN.CLIENT_PET_CONSULTATIONS(
        Number(selectedClientId.value),
        Number(selectedPetId.value)
      )
    )
    if (response.data.success) {
      consultations.value = response.data.consultations
      formData.value.consultation_id = ''
    }
  } catch (err: any) {
    console.error('Error loading consultations:', err)
    error.value = err.response?.data?.message || 'Failed to load consultations'
    consultations.value = []
  } finally {
    loadingConsultations.value = false
  }
}

const handleFileSelect = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (!input.files) return

  const files = Array.from(input.files)
  
  // Limit to 10 files total
  if (selectedFiles.value.length + files.length > 10) {
    error.value = 'Maximum 10 files allowed'
    return
  }

  files.forEach(file => {
    // Check file size (max 5MB per file)
    if (file.size > 5 * 1024 * 1024) {
      error.value = `File ${file.name} is too large. Max 5MB per file.`
      return
    }

    // Create preview for images
    let preview: string | null = null
    if (file.type.startsWith('image/')) {
      preview = URL.createObjectURL(file)
    }

    selectedFiles.value.push({
      file,
      name: file.name,
      preview
    })
  })

  // Clear the input so the same file can be selected again
  input.value = ''
}

const removeFile = (index: number) => {
  // Revoke the preview URL to free memory
  const fileToRemove = selectedFiles.value[index]
  if (fileToRemove.preview) {
    URL.revokeObjectURL(fileToRemove.preview)
  }
  selectedFiles.value.splice(index, 1)
}

const convertFilesToBase64 = async (): Promise<string[]> => {
  const base64Files: string[] = []
  
  for (const fileItem of selectedFiles.value) {
    const base64 = await new Promise<string>((resolve, reject) => {
      const reader = new FileReader()
      reader.onload = () => {
        const result = reader.result as string
        resolve(result)
      }
      reader.onerror = reject
      reader.readAsDataURL(fileItem.file)
    })
    base64Files.push(base64)
  }
  
  return base64Files
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  success.value = ''

  try {
    // Convert files to base64
    let photoResults: string[] = []
    if (selectedFiles.value.length > 0) {
      photoResults = await convertFilesToBase64()
    }

    const submitData: any = {
      lab_types: formData.value.lab_types,
      notes: formData.value.notes || null,
      photo_result: photoResults.length > 0 ? photoResults : null
    }

    // Use the hierarchical endpoint for creating lab test
    const clientId = Number(selectedClientId.value)
    const petId = Number(selectedPetId.value)
    const consultationId = Number(formData.value.consultation_id)
    
    const response = await api.post(
      API_ENDPOINTS.ADMIN.CLIENT_PET_CONSULTATION_LABTESTS(clientId, petId, consultationId),
      submitData
    )

    if (response.data.success) {
      success.value = 'Lab test created successfully! 🔬✨'
      emit('labtest-created', response.data.labtest)
      
      // Cleanup file previews
      selectedFiles.value.forEach(fileItem => {
        if (fileItem.preview) {
          URL.revokeObjectURL(fileItem.preview)
        }
      })
      
      // Reset form after a delay
      setTimeout(() => {
        formData.value = {
          consultation_id: '',
          lab_types: '',
          notes: '',
          photo_result: []
        }
        selectedFiles.value = []
        selectedClientId.value = ''
        selectedPetId.value = ''
        pets.value = []
        consultations.value = []
        success.value = ''
      }, 2000)
    }
  } catch (err: any) {
    console.error('Lab test creation error:', err)
    error.value = err.response?.data?.message || 'Failed to create lab test'
    
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
})
</script>
