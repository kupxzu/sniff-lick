<template>
  <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-500 to-purple-600 p-8">
    <Card class="w-full max-w-md shadow-2xl">
      <CardHeader class="space-y-1 text-center">
        <CardTitle class="text-4xl font-bold">Sniff & Lick</CardTitle>
        <CardDescription class="text-base">
          Veterinary Management System
        </CardDescription>
      </CardHeader>
      <CardContent class="pt-6">
        <form @submit.prevent="handleLogin" class="space-y-5">
          <div v-if="error" class="rounded-md border-l-4 border-destructive bg-destructive/10 p-4">
            <p class="text-sm text-destructive">{{ error }}</p>
          </div>

          <div class="space-y-2">
            <Label for="login" class="text-base">Username or Email</Label>
            <Input
              id="login"
              v-model="credentials.login"
              type="text"
              placeholder="Enter your username or email"
              required
              :disabled="loading"
              class="h-11"
            />
          </div>

          <div class="space-y-2">
            <Label for="password" class="text-base">Password</Label>
            <Input
              id="password"
              v-model="credentials.password"
              type="password"
              placeholder="Enter your password"
              required
              :disabled="loading"
              class="h-11"
            />
          </div>

          <Button type="submit" class="w-full h-11 text-base" :disabled="loading">
            {{ loading ? 'Logging in...' : 'Login' }}
          </Button>
        </form>

        <div class="mt-6 border-t pt-6 text-center text-sm text-muted-foreground">
          <p>
            Don't have an account? 
            <a href="#" @click.prevent="$emit('show-register')" class="font-medium text-primary hover:underline">
              Register
            </a>
          </p>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { authService } from '../services/auth'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card'
import { Button } from './ui/button'
import { Input } from './ui/input'
import { Label } from './ui/label'

const emit = defineEmits(['login-success', 'show-register'])

const credentials = ref({
  login: '',
  password: ''
})

const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await authService.login(credentials.value)
    
    if (response.success) {
      emit('login-success', response.user)
    } else {
      error.value = response.message || 'Login failed'
    }
  } catch (err: any) {
    if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else if (err.message) {
      error.value = err.message
    } else {
      error.value = 'An error occurred during login. Please check your connection.'
    }
    console.error('Login error:', err)
  } finally {
    loading.value = false
  }
}
</script>
