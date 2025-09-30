<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
  <div
    id="bg-banner"
    class="min-h-screen relative flex items-center justify-center p-4 sm:p-6 lg:p-8 text-[#1b1b18] dark:text-white"
  >
    <!-- Background overlay -->
    <div class="absolute inset-0 bg-black/30 z-0"></div>

    <!-- Layer 2: Center floating transparent card -->
    <div
      class="relative z-10 bg-white/10 backdrop-blur-md rounded-2xl shadow-4xl 
             w-full max-w-6xl min-h-[450px] flex flex-col lg:flex-row items-center justify-between 
             p-6 lg:p-10 gap-6"
    >
      <!-- Layer 3: Login panel -->
      <div
        class="bg-white/10 backdrop-blur-md shadow-2xl rounded-xl 
               w-full lg:w-2/5 h-auto lg:h-[580px] 
               p-6 sm:p-8 flex flex-col justify-center"
      >
        <AuthBase
          class="bg-transparent p-0 shadow-none"
          title="Xpro Farm Management"
          description=""
        >
          <Head title="Log in" />

          <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-400"
          >
            {{ status }}
          </div>

          <form @submit.prevent="submit" class="flex flex-col gap-6 w-full">
            <!-- Email -->
            <div class="grid gap-2">
              <Label for="email">Email address</Label>
              <Input
                id="email"
                type="email"
                required
                autofocus
                autocomplete="email"
                v-model="form.email"
                placeholder="email@example.com"
              />
              <InputError :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div class="grid gap-2">
              <div class="flex items-center justify-between">
                <Label for="password">Password</Label>
                <TextLink
                  v-if="canResetPassword"
                  :href="route('password.request')"
                  class="text-sm"
                >
                  Forgot password?
                </TextLink>
              </div>
              <Input
                id="password"
                type="password"
                required
                autocomplete="current-password"
                v-model="form.password"
                placeholder="Password"
              />
              <InputError :message="form.errors.password" />
            </div>

            <!-- Remember & Submit -->
            <div class="flex flex-col gap-4 mt-4">
              <Label for="remember" class="flex items-center space-x-3">
                <Checkbox id="remember" v-model="form.remember" />
                <span>Remember me</span>
              </Label>

              <Button
                type="submit"
                class="bg-chicken w-full"
                :disabled="form.processing"
              >
                <LoaderCircle
                  v-if="form.processing"
                  class="h-4 w-4 animate-spin"
                />
                Log in
              </Button>
            </div>
          </form>
        </AuthBase>
      </div>

      <!-- Right side transparent image -->
      <div
        class="w-full lg:w-3/5 h-56 lg:h-full flex items-center justify-center"
      >
        <img
          src="/transparent-chicks.png"
          alt="Decor"
          class="max-h-[220px] sm:max-h-[320px] lg:max-h-[500px] opacity-60 object-contain"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
#bg-banner {
  background-image: url('pghomebanner.jpg');
  background-image: url('bg.jpg');
  background-size: cover;
  background-position: center;
  position: relative;
}
</style>
