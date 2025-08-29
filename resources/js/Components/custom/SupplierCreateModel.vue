<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="$emit('update:open', false)">
      <!-- Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
      </TransitionChild>

      <!-- Panel -->
      <div class="fixed inset-0 z-10 flex items-center justify-center">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel
            class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl w-5/6 lg:w-3/6 p-10 text-center"
          >
            <DialogTitle class="text-xl font-bold text-white">Add Supplier</DialogTitle>

            <form @submit.prevent="submit" enctype="multipart/form-data" novalidate>
              <div class="grid grid-cols-2 gap-6 mt-6 text-left">
                <!-- Supplier Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-300" for="name">Supplier Name:</label>
                  <input
                    v-model.trim="form.name"
                    type="text"
                    id="name"
                    required
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.name" class="mt-2 block text-red-500">{{ form.errors.name }}</span>
                </div>

                <!-- Contact (digits only) -->
                <div>
                  <label class="block text-sm font-medium text-gray-300" for="contact">Contact:</label>
                 <input
  v-model="form.contact"
  type="tel"
  inputmode="numeric"
  minlength="10"
  maxlength="10"
  pattern="[0-9]{10}"
  title="Enter exactly 10 digits"
   
  class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
/>
                  <span v-if="form.errors.contact" class="mt-2 block text-red-500">{{ form.errors.contact }}</span>
                </div>

                <!-- Email (optional) -->
                <div>
                  <label class="block text-sm font-medium text-gray-300" for="email">Email:</label>
                 <input
  v-model.trim="form.email"
  type="email"
  id="email"
  autocomplete="email"
  inputmode="email"
  maxlength="255"
  pattern="^[^\s@]+@[^\s@]+\.[^\s@]{2,}$"
  title="Enter a valid email like name@example.com"
  class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
/>

                  <span v-if="form.errors.email" class="mt-2 block text-red-500">{{ form.errors.email }}</span>
                </div>

                <!-- Address -->
                <div>
                  <label class="block text-sm font-medium text-gray-300" for="address">Address:</label>
                  <input
                    v-model.trim="form.address"
                    type="text"
                    id="address"
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.address" class="mt-2 block text-red-500">{{ form.errors.address }}</span>
                </div>

                <!-- Image -->
                <div class="col-span-2">
                  <label class="block text-sm font-medium text-gray-300" for="image">Supplier Image:</label>
                  <input
                    ref="imageInput"
                    type="file"
                    id="image"
                    accept="image/*"
                    @change="handleImageUpload"
                    class="w-full px-4 py-2 mt-2 text-white bg-gray-800 rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.image" class="mt-2 block text-red-500">{{ form.errors.image }}</span>
                </div>
              </div>

              <!-- Buttons -->
              <div class="mt-6 space-x-4 text-center">
                <button
                  @click="playClickSound"
                  class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
                  type="submit"
                >
                  Save
                </button>
                <button
                  class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                  type="button"
                  @click="() => { playClickSound(); emit('update:open', false); }"
                >
                  Cancel
                </button>
              </div>
            </form>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot,
} from "@headlessui/vue";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const playClickSound = () => new Audio("/sounds/click-sound.mp3").play();

const emit = defineEmits(["update:open"]);
defineProps({ open: { type: Boolean, required: true } });

const imageInput = ref(null);

const form = useForm({
  name: "",
  contact: "",
  email: "",
  address: "",
  image: null,
});

const onContactInput = (e) => {
  const digits = (e.target.value || "").replace(/\D/g, "");
  form.contact = digits;
};

const handleImageUpload = (event) => {
  const file = event.target.files?.[0] || null;
  form.image = file;
};

const emailIsValid = (val) => {
  if (!val) return true; // allow empty (optional email)
  const v = String(val).trim();
  // Simple, robust email check (good enough for UI; server stays authoritative)
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
};

const submit = () => {
  form.clearErrors();

  // Front-end validations
  if (!form.name?.trim()) {
    form.setError("name", "Supplier name is required.");
  }

  if (!emailIsValid(form.email)) {
    form.setError("email", "Please enter a valid email address.");
  }

  // Stop if any client-side errors
  if (Object.keys(form.errors).length) return;

  // Post (ensure multipart for file uploads)
  form.post("/suppliers", {
    forceFormData: true,
    onSuccess: () => {
      form.reset();
      if (imageInput.value) imageInput.value.value = ""; // clear file input
      emit("update:open", false);
    },
  });
};
</script>
