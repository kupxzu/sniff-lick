<script setup lang="ts">
import { ref } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { Button } from './ui/button'
import { Input } from './ui/input'
import { Label } from './ui/label'
import api, { API_ENDPOINTS } from '../services/api'

const emit = defineEmits(['client-created', 'cancel'])

const formData = ref({
  name: '',
  email: ''
})

const loading = ref(false)
const error = ref('')
const success = ref('')

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  success.value = ''

  try {
    const response = await api.post(API_ENDPOINTS.AUTH.REGISTER, {
      ...formData.value,
      role: 'client'
    })

    if (response.data.success) {
      success.value = 'Client created successfully!'
      // Reset form
      formData.value = {
        name: '',
        email: ''
      }
      // Emit success event
      setTimeout(() => {
        emit('client-created', response.data.user)
      }, 1500)
    } else {
      error.value = response.data.message || 'Failed to create client'
    }
  } catch (err: any) {
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      error.value = Object.values(errors).flat().join(', ')
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'An error occurred while creating the client'
    }
    console.error('Create client error:', err)
  } finally {
    loading.value = false
  }
}

const handleCancel = () => {
  formData.value = {
    name: '',
    email: ''
  }
  error.value = ''
  success.value = ''
  emit('cancel')
}
</script>

<template>
  <Card class="mx-auto max-w-2xl">
    <CardHeader>
      <CardTitle>Create New Client</CardTitle>
      <CardDescription>Add a new client to the veterinary management system</CardDescription>
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

        <!-- Name -->
        <div class="space-y-2">
          <Label for="name">Full Name *</Label>
          <Input
            id="name"
            v-model="formData.name"
            type="text"
            placeholder="Enter client's full name"
            required
            :disabled="loading"
          />
        </div>

        <!-- Email -->
        <div class="space-y-2">
          <Label for="email">Email Address *</Label>
          <Input
            id="email"
            v-model="formData.email"
            type="email"
            placeholder="Enter email address"
            required
            :disabled="loading"
          />
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4">
          <Button type="submit" class="flex-1" :disabled="loading">
            {{ loading ? 'Creating...' : 'Create Client' }}
          </Button>
          <Button type="button" variant="outline" @click="handleCancel" :disabled="loading">
            Cancel
          </Button>
        </div>
      </form>
    </CardContent>
  </Card>
</template>
