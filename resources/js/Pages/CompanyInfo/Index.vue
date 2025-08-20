<script setup>
import { ref, watch } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";

const { companyInfo } = defineProps({
  companyInfo: Object,
});

const previewLogoUrl = ref(null);

const form = useForm({
  _method: "put",   // important if your route is PUT/PATCH
  name: "",
  address: "",
  phone: "",
  email: "",
  website: "",
  logo: null,       // must be File or null (never a string URL)
});

// Only update preview with string URL; do NOT assign it to form.logo
watch(
  () => companyInfo,
  (ci) => {
    if (ci) {
      form.name = ci.name || "";
      form.address = ci.address || "";
      form.phone = ci.phone || "";
      form.email = ci.email || "";
      form.website = ci.website || "";
      form.logo = null; // keep null unless a new file is chosen
      previewLogoUrl.value = ci.logo ? `${ci.logo}` : null;
    } else {
      form.reset();
      previewLogoUrl.value = null;
    }
  },
  { immediate: true }
);

const handleImageUpload = (e) => {
  const file = e.target.files?.[0] || null;
  form.logo = file;                            // File for submit
  previewLogoUrl.value = file ? URL.createObjectURL(file) : previewLogoUrl.value; // temp preview
};

const submit = () => {
  if (!companyInfo) return;

  // Remove 'logo' from payload if no new file selected
  form.transform((data) => {
    const d = { ...data };
    if (!(d.logo instanceof File)) delete d.logo;
    return d;
  });

  form.post(`/company-info/${companyInfo.id}`, {
    forceFormData: true, // make sure it's multipart (safe to include)
    onSuccess: () => {
      // toast or banner already handled server-side
    },
  });
};
</script>

<template>
  <Head title="CompanyInfo" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 px-36">
    <Header />

    <div class="w-5/6 py-12 space-y-24">
      <!-- header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" alt="Back" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">Company Info</p>
        </div>
      </div>

      <!-- form -->
      <div class="bg-white p-8 rounded-lg shadow-lg">
        <form @submit.prevent="submit">
          <div class="space-y-6">
            <!-- name -->
            <div class="flex flex-col">
              <label for="name" class="text-xl font-medium text-gray-700">Company Name</label>
              <input id="name" v-model="form.name" type="text"
                     class="mt-2 p-4 border border-gray-300 rounded-lg text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="Enter company name"
                     :class="{'border-red-500': form.errors.name}" />
              <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
            </div>

            <!-- address -->
            <div class="flex flex-col">
              <label for="address" class="text-xl font-medium text-gray-700">Company Address</label>
              <input id="address" v-model="form.address" type="text"
                     class="mt-2 p-4 border border-gray-300 rounded-lg text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="Enter company address"
                     :class="{'border-red-500': form.errors.address}" />
              <p v-if="form.errors.address" class="text-red-500 text-sm mt-1">{{ form.errors.address }}</p>
            </div>

            <!-- phone -->
            <div class="flex flex-col">
              <label for="phone" class="text-xl font-medium text-gray-700">Company Phone</label>
              <input id="phone" v-model="form.phone" type="text"
                     class="mt-2 p-4 border border-gray-300 rounded-lg text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="Enter company phone"
                     :class="{'border-red-500': form.errors.phone}" />
              <p v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</p>
            </div>

            <!-- email -->
            <div class="flex flex-col">
              <label for="email" class="text-xl font-medium text-gray-700">Company Email</label>
              <input id="email" v-model="form.email" type="text"
                     class="mt-2 p-4 border border-gray-300 rounded-lg text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="Enter company email"
                     :class="{'border-red-500': form.errors.email}" />
              <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
            </div>

            <!-- website -->
            <div class="flex flex-col">
              <label for="website" class="text-xl font-medium text-gray-700">Company Website</label>
              <input id="website" v-model="form.website" type="text"
                     class="mt-2 p-4 border border-gray-300 rounded-lg text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="Enter company website"
                     :class="{'border-red-500': form.errors.website}" />
              <p v-if="form.errors.website" class="text-red-500 text-sm mt-1">{{ form.errors.website }}</p>
            </div>

            <!-- logo -->
            <div class="w-full md:w-6/12">
              <label for="logo" class="block text-xl font-medium text-gray-700">Logo</label>

              <div class="mt-2">
                <img v-if="previewLogoUrl" :src="previewLogoUrl" alt="Company logo" class="rounded-lg max-h-40 object-contain" />
                <p v-else class="text-sm text-gray-500">No logo available</p>
              </div>

              <input type="file" id="logo" accept="image/*"
                     @change="handleImageUpload"
                     class="w-full px-4 py-2 mt-2 text-white bg-gray-800 rounded-md focus:outline-none focus:ring focus:ring-blue-600" />
              <span v-if="form.errors.logo" class="mt-2 text-red-500">{{ form.errors.logo }}</span>
            </div>

            <!-- submit -->
            <div class="flex justify-center">
              <button class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700" type="submit">
                Update Info
              </button>
            </div>
          </div>
        </form>
      </div>

    </div>
  </div>

  <Footer />
</template>
