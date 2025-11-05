<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Login from './components/Login.vue'
import Dashboard from './components/Dashboard.vue'
import { authService } from './services/auth'

const isAuthenticated = ref(false)

onMounted(() => {
  isAuthenticated.value = authService.isAuthenticated()
})

const handleLoginSuccess = (user: any) => {
  console.log('Login successful:', user)
  isAuthenticated.value = true
}

const handleLogout = () => {
  console.log('Logged out')
  isAuthenticated.value = false
}
</script>

<template>
  <div class="app">
    <Dashboard 
      v-if="isAuthenticated" 
      @logout="handleLogout"
    />
    <Login 
      v-else 
      @login-success="handleLoginSuccess"
    />
  </div>
</template>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100vh;
  overflow: hidden;
}

#app {
  width: 100%;
  height: 100vh;
  overflow: auto;
}

.app {
  width: 100%;
  min-height: 100vh;
}
</style>
