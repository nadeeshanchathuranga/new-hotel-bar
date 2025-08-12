<style>
/* General DataTables Pagination Container Style */
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}

/* Style the filter container */
#OwnerTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px; /* Add spacing below the filter */
}

/* Style the label and input field inside the filter */
#OwnerTable_filter label {
  font-size: 17px;
  color: #000000; /* Match text color of the table header */
  display: flex;
  align-items: center;
}

/* Style the input field */
#OwnerTable_filter input[type="search"] {
  font-weight: 400;
  padding: 9px 15px;
  font-size: 14px;
  color: #000000cc;
  border: 1px solid rgb(209 213 219);
  border-radius: 5px;
  background: #fff;
  outline: none;
  transition: all 0.5s ease;
}
#OwnerTable_filter input[type="search"]:focus {
  outline: none; /* Removes the default outline */
  border: 1px solid #4b5563;
  box-shadow: none; /* Removes any focus box-shadow */
}

.dataTables_wrapper {
  margin-bottom: 10px;
}
</style>

<template>
  <Head title="Owners" />
  <Banner />
  <div
    class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 px-36"
  >
    <!-- Include the Header -->
    <Header />

    <div class="w-5/6 py-12 space-y-24">
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-center space-x-4"></div>
        <p class="text-3xl italic font-bold text-black">
          <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">
            {{ allOwners.length }}
          </span>
          <span class="text-xl">/ Total Owners</span>
        </p>
      </div>

      <div class="flex w-full">
        <div class="flex items-center w-full h-16 space-x-4 rounded-2xl">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Owners
          </p>
        </div>

        <div class="flex justify-end w-full">
          <p
            @click="
              () => {
                if (HasRole(['Admin'])) {
                  isCreateModalOpen = true;
                }
              }
            "
            :class="
              HasRole(['Admin'])
                ? 'px-12 py-4 text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-xl'
                : 'px-12 py-4 text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 cursor-not-allowed rounded-xl'
            "
            :title="
              HasRole(['Admin'])
                ? ''
                : 'You do not have permission to add owners'
            "
          >
            <i class="pr-4 ri-add-circle-fill"></i> Add a New Owner
          </p>
        </div>
      </div>

      <template v-if="allOwners && allOwners.length > 0">
        <div class="overflow-x-auto">
          <table
            id="OwnerTable"
            class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto"
          >
            <thead>
  <tr class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-[16px] text-white border-b border-blue-700">
    <th class="p-4 font-semibold tracking-wide uppercase">#</th>
    <th class="p-4 font-semibold tracking-wide uppercase">Name</th>
    <th class="p-4 font-semibold tracking-wide uppercase">Code</th>
    <th class="p-4 font-semibold tracking-wide uppercase">Month</th>
    <th class="p-4 font-semibold tracking-wide uppercase">Discount</th>
    <th class="p-4 font-semibold tracking-wide uppercase">Current</th>
    <th class="p-4 font-semibold tracking-wide uppercase">Balance</th> <!-- NEW -->
    <th class="p-4 font-semibold tracking-wide uppercase">Status</th>
  </tr>
</thead>

           <tbody>
  <tr v-for="(owner, index) in allOwners" :key="owner.id" class="hover:bg-gray-200">
    <td class="px-6 py-3">{{ index + 1 }}</td>
    <td class="px-6 py-3">{{ owner.name }}</td>
    <td class="px-6 py-3">{{ owner.code }}</td>

    <!-- Month -->
    <td class="px-6 py-3">{{ owner.items?.[0]?.month || '-' }}</td>

    <!-- Discount / Current as numbers -->
    <td class="px-6 py-3">
      {{ toLkr(owner.items?.[0]?.discount_value) }}
    </td>
    <td class="px-6 py-3">
      {{ toLkr(owner.items?.[0]?.current_discount) }}
    </td>

    <!-- Balance = Discount - Current -->
    <td class="px-6 py-3">
      <span
        :class="balance(owner) > 0
          ? 'px-3 py-1 rounded-lg text-white bg-emerald-600'
          : 'px-3 py-1 rounded-lg text-white bg-gray-500'"
      >
        {{ toLkr(balance(owner)) }}
      </span>
    </td>

    <td class="px-6 py-3">
      <span
        :class="owner.status === 'active'
          ? 'px-3 py-1 rounded-lg text-white bg-green-600'
          : 'px-3 py-1 rounded-lg text-white bg-gray-500'"
      >
        {{ owner.status }}
      </span>


    <button
  class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700"
  @click="openOwnerMonthHistory(owner)"
>
  View
</button>

    </td>
  </tr>
</tbody>

          </table>
        </div>
      </template>

      <template v-else>
        <div class="col-span-4 text-center text-blue-500">
          <p class="text-center text-red-500 text-[17px]">
            No owners available
          </p>
        </div>
      </template>
    </div>
  </div>

  <!-- Modal Components (optional) -->
  <!-- Add your OwnerCreate/Update/Delete modals here if needed -->

  <Footer />

<!-- Owner Month History Modal -->
<div
  v-if="ownerHistoryModal.open"
  class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
>
  <div class="w-full max-w-3xl p-6 bg-white rounded-2xl shadow-xl">
    <!-- Header -->
    <div class="flex items-start justify-between mb-4">
      <div>
        <h3 class="text-xl font-bold text-gray-900">
          {{ ownerHistoryModal.owner?.name }} ({{ ownerHistoryModal.owner?.code }})
        </h3>
        <p class="text-sm text-gray-500">Month-wise discount history</p>
      </div>
      <button @click="ownerHistoryModal.open = false" class="text-2xl leading-none">×</button>
    </div>

    <!-- Body -->
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-gray-800 border border-gray-200 rounded-lg">
        <thead>
          <tr class="bg-gray-100 text-left">
            <th class="p-3 border-b">#</th>
            <th class="p-3 border-b">Month</th>
            <th class="p-3 border-b">Discount</th>
            <th class="p-3 border-b">Current</th>
            <th class="p-3 border-b">Balance</th>
            <th class="p-3 border-b">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(it, idx) in ownerHistoryModal.items"
            :key="`${it.owner_id}-${it.month}-${idx}`"
            class="hover:bg-gray-50"
          >
            <td class="p-3 border-b">{{ idx + 1 }}</td>
            <td class="p-3 border-b">{{ it.month }}</td>
            <td class="p-3 border-b">{{ toLkr(it.discount_value) }} LKR</td>
            <td class="p-3 border-b">{{ toLkr(it.current_discount) }} LKR</td>
            <td class="p-3 border-b">
              <span
                :class="(n(it.discount_value) - n(it.current_discount)) > 0
                  ? 'px-2 py-0.5 rounded text-white bg-emerald-600'
                  : 'px-2 py-0.5 rounded text-white bg-gray-500'"
              >
                {{ toLkr(n(it.discount_value) - n(it.current_discount)) }} LKR
              </span>
            </td>
            <td class="p-3 border-b">
              <span
                class="px-2 py-0.5 rounded text-white"
                :class="it.status === 'active' ? 'bg-green-600' : 'bg-gray-500'"
              >
                {{ it.status }}
              </span>
            </td>
          </tr>

          <tr v-if="!ownerHistoryModal.items?.length">
            <td colspan="6" class="p-4 text-center text-gray-500">No history available</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Footer -->
    <div class="mt-6 flex justify-end gap-2">
      <button
        class="px-4 py-2 text-sm font-semibold text-gray-800 bg-gray-100 rounded-md hover:bg-gray-200"
        @click="ownerHistoryModal.open = false"
      >
        Close
      </button>
    </div>
  </div>
</div>

</template>

<script setup>
import { ref, onMounted } from "vue";
import { Link, Head } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

defineProps({
  allOwners: Array,
  totalOwners: Number,
});

const isCreateModalOpen = ref(false);




// Safely parse number (null/undefined/'' -> 0)
const n = (v) => Number(v ?? 0) || 0;

// LKR display (no decimals? change toFixed(2) if needed)
const toLkr = (v) => {
  const val = n(v);
  return val.toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

// Balance calculator: (discount_value - current_discount), clamp to >= 0
const balance = (owner) => {
  const item = owner?.items?.[0] || {};
  const dv = n(item.discount_value);
  const cd = n(item.current_discount);
  const remain = dv - cd;
  return remain > 0 ? remain : 0;
};




// ---------- modal state ----------
const ownerHistoryModal = ref({
  open: false,
  owner: null,
  items: [], // full month history for this owner
});

// ---------- open handler ----------
const openOwnerMonthHistory = (owner) => {
  // Expecting owner.items to already be loaded (you used with('items'))
  // If not sorted, sort here (desc by month)
  const items = Array.isArray(owner.items)
    ? [...owner.items].sort((a, b) => (a.month < b.month ? 1 : a.month > b.month ? -1 : 0))
    : [];

  ownerHistoryModal.value = {
    open: true,
    owner,
    items,
  };
};



onMounted(() => {
  $("#OwnerTable").DataTable({
    dom: "Bfrtip",
    pageLength: 10,
    buttons: [],
    // No actions column now, so no need to disable ordering on any column.
    initComplete: function () {
      const searchInput = $("div.dataTables_filter input");
      searchInput.attr("placeholder", "Search ...");
      searchInput.off("keyup");
      searchInput.on("keypress", function (e) {});
    },
    language: { search: "" },
  });
});
</script>
