<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h3>Register</h3>
          </div>
          <div class="card-body">
            <form @submit.prevent="handleRegister">
              <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  v-model="form.name"
                  required
                />
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                  type="email"
                  class="form-control"
                  id="email"
                  v-model="form.email"
                  required
                />
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input
                  type="password"
                  class="form-control"
                  id="password"
                  v-model="form.password"
                  required
                />
              </div>
              <div class="mb-3">
                <label for="password_confirmation" class="form-label"
                  >Confirm Password</label
                >
                <input
                  type="password"
                  class="form-control"
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  required
                />
              </div>
              <button type="submit" class="btn btn-primary w-100">
                Register
              </button>
            </form>
            <div class="mt-3 text-center">
              <p>
                Already have account?
                <router-link to="/login">Login</router-link>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const form = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const handleRegister = async () => {
  // Temporary - just simulate registration
  localStorage.setItem("token", "dummy-token");
  router.push("/");
  console.log("Register attempt:", form);
};
</script>
