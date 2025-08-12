<template>
  <Head title="POS" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-4 bg-gray-100 px-36">
    <!-- Include the Header -->
    <Header />

    <div class="w-5/6 py-12 space-y-16">
      <div class="flex items-center justify-between space-x-4">
        <div class="flex w-full space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="pt-3 text-4xl font-bold tracking-wide text-black uppercase">
            PoS
          </p>
        </div>
        <div class="flex items-center justify-end w-full space-x-4">
          <p v-if="selectedTable?.orderId" class="text-3xl font-bold tracking-wide text-black">
            Order ID : #{{ selectedTable.orderId }}
          </p>
          <p class="text-3xl text-black cursor-pointer">
            <i @click="refreshData" class="ri-restart-line"></i>
          </p>
        </div>
      </div>

      <div class="flex w-full gap-4">
        <!-- Left: Tables + Customer -->
        <div class="flex flex-col w-1/2">
          <div class="p-8 w-full border-4 border-black rounded-3xl mb-8">
            <!-- Header -->
            <div class="flex items-center justify-between p-4">
              <h1 class="text-xl font-bold">
                <span class="text-3xl font-bold tracking-wide text-black mb-4">Tables</span>
              </h1>
              <!-- Add More Tables Button -->
              <button
                @click="addTable"
                class="flex items-center px-4 py-2 bg-blue-600 tracking-wide text-white text-xl font-semibold rounded-lg hover:bg-blue-700"
              >
                <p><i class="pr-4 ri-add-circle-fill"></i></p>
                Add More Tables
              </button>
            </div>

            <!-- Tables -->
            <div class="grid grid-cols-4 gap-4">
              <div
                v-for="(table, index) in tables"
                :key="table.id"
                :class="[
                  'w-full flex flex-col justify-center items-center rounded-xl px-2 py-6 border border-[#2563EB] text-center',
                  table.id === selectedTable.id ? 'bg-blue-100' : '',
                  'hover:bg-blue-100',
                ]"
                @click="selectTable(table)"
              >
                <div v-if="table.id === 'default'" class="text-2xl text-black font-bold">
                  Live Bill
                </div>
                <div v-else>
                  <button
                    v-if="table.id !== 'default'"
                    @click.stop="removeTable(index)"
                    class="text-3xl text-red-500 hover:text-red-700"
                  >
                    ✖
                  </button>
                  <div class="text-2xl text-black font-bold">Table</div>
                  <div class="text-6xl text-black font-bold">
                    {{ table.number - 1 }}
                  </div>

                  <button
                    @click.stop="sendKOT(table)"
                    class="mt-4 px-4 py-2 bg-green-600 tracking-wide text-white text-lg font-semibold rounded-lg hover:bg-green-700"
                  >
                    KOT
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="flex flex-col w-full">
            <div class="p-16 space-y-8 bg-black shadow-lg rounded-3xl">
              <p class="mb-4 text-5xl font-bold text-white">Customer Details</p>
              <div class="mb-3">
                <input
                  v-model="customer.name"
                  type="text"
                  placeholder="Enter Customer Name"
                  class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div class="flex gap-2 mb-3 text-black">
                <div class="relative w-full">
                  <input
                    v-model="customer.contactNumber"
                    type="text"
                    placeholder="Enter Customer Contact Number"
                    class="w-full h-12 px-4 pr-16 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  <button
                    @click="searchCustomer"
                    class="absolute top-0 right-0 h-12 px-4 text-white bg-blue-600 rounded-r-md hover:bg-blue-700"
                  >
                    Search
                  </button>
                </div>
              </div>
              <div class="text-black">
                <input
                  v-model="customer.email"
                  type="email"
                  placeholder="Enter Customer Email"
                  class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div class="text-black">
                <input
                  v-model="customer.bdate"
                  type="date"
                  placeholder="Customer Birthdate"
                  class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Bill -->
        <div class="flex w-1/2 h-full p-8 border-4 border-black rounded-3xl">
          <div class="flex flex-col items-start justify-center w-full px-12">
            <div class="flex items-center justify-between w-full mb-4">
              <h2 class="text-5xl font-bold text-black">
                {{
                  selectedTable?.id === 'default'
                    ? 'Live Bill'
                    : `Table ${selectedTable?.number - 1}`
                }}
              </h2>

              <span class="flex cursor-pointer" @click="isSelectModalOpen = true">
                <p class="text-xl text-blue-600 font-bold">Food Menu</p>
                <img src="/images/selectpsoduct.svg" class="w-6 h-6 ml-2" />
              </span>
            </div>

            <div class="w-full px-12">
              <div
                v-if="selectedTable?.id === 'default'"
                class="w-full flex justify-center items-center mb-4 space-x-4"
              >
                <select
                  id="order_type"
                  v-model="selectedTable.order_type"
                  class="w-full text-center p-2 border-2 border-black rounded cursor-pointer"
                >
                  <option value="" disabled>Select an Order Type</option>
                  <option value="takeaway">Takeaway</option>
                  <option value="pickup">Delivery</option>
                </select>
              </div>
            </div>

            <div class="w-full text-center" v-if="!selectedTable || selectedTable.products.length === 0">
              <p class="text-2xl text-red-500">No Products to show</p>
            </div>

            <div
              class="flex flex-col w-full space-y-4 py-4 border-b border-gray-200"
              v-for="item in selectedTable.products"
              :key="item.id"
            >
              <div class="flex items-start space-x-4">
                <!-- Product Image -->
                <div class="w-20 h-20 flex-shrink-0">
                  <img
                    :src="item.image ? `/${item.image}` : '/images/placeholder.jpg'"
                    alt="Product Image"
                    class="object-cover w-full h-full rounded-lg border border-gray-300"
                  />
                </div>

                <!-- Product Details -->
                <div class="flex-1 flex flex-col space-y-2">
                  <div class="flex justify-between items-start">
                    <h3 class="text-lg font-semibold text-gray-900">
                      {{ item.name }}
                    </h3>
                    <button
                      @click="removeProduct(item.id)"
                      class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-300 hover:bg-gray-400 transition-colors"
                    >
                      <i class="ri-close-line text-xl text-red-600 font-semibold"></i>
                    </button>
                  </div>

                  <p class="text-lg font-medium text-gray-700">
                    {{ item.selling_price }} LKR
                  </p>

                  <!-- Quantity Controls -->
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                      <button
                        @click="decrementQuantity(item.id)"
                        class="flex items-center justify-center w-8 h-8 text-white bg-gray-800 hover:bg-gray-900 rounded-full transition-colors"
                      >
                        <i class="ri-subtract-line"></i>
                      </button>
                      <span class="text-lg font-medium w-8 text-center">
                        {{ item.quantity }}
                      </span>
                      <button
                        @click="incrementQuantity(item.id)"
                        class="flex items-center justify-center w-8 h-8 text-white bg-gray-800 hover:bg-gray-900 rounded-full transition-colors"
                      >
                        <i class="ri-add-line"></i>
                      </button>
                    </div>

                    <!-- Discount and Total -->
                    <div class="flex flex-col items-end">
                      <div class="mb-1">
                        <button
                          v-if="item.discount && item.discount > 0 && item.apply_discount == false && !appliedCoupon"
                          @click="applyDiscount(item.id)"
                          class="text-md py-1 px-3 bg-green-500 hover:bg-green-600 rounded-full font-medium text-white transition-colors"
                        >
                          Apply {{ item.discount }}% off
                        </button>
                        <button
                          v-if="item.discount && item.discount > 0 && item.apply_discount == true && !appliedCoupon"
                          @click="removeDiscount(item.id)"
                          class="text-md py-1 px-3 bg-red-500 hover:bg-red-600 rounded-full font-medium text-white transition-colors"
                        >
                          Remove {{ item.discount }}% Off
                        </button>
                      </div>
                      <p class="text-lg font-bold text-gray-900">
                        {{
                          item.apply_discount
                            ? (item.selling_price * item.quantity * (100 - item.discount)) / 100
                            : item.selling_price * item.quantity
                        }} LKR
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Summary -->
            <div class="w-full pt-6 space-y-2">
              <div class="flex items-center justify-between w-full px-16">
                <p class="text-xl">Sub Total</p>
                <p class="text-xl">{{ subtotal }} LKR</p>
              </div>

              <div class="flex items-center justify-between w-full px-16 py-2 pb-4 border-b border-black">
                <p class="text-xl">Discount</p>
                <p class="text-xl">( {{ totalDiscount }} LKR )</p>
              </div>

              <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                <p class="text-xl text-black">Custom Discount</p>
                <span class="flex items-center">
                  <CurrencyInput
                    v-model="selectedTable.custom_discount"
                    placeholder="Enter value"
                    class="rounded-md px-2 py-1 text-black text-md"
                  />
                  <select
                    v-model="selectedTable.custom_discount_type"
                    class="ml-2 px-8 border-black rounded-md text-black py-1 text-md"
                  >
                    <option value="percent">%</option>
                    <option value="fixed">Rs</option>
                  </select>
                </span>
              </div>

              <div
                v-if="selectedTable.order_type === 'pickup'"
                class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black"
              >
                <select
                  v-model="selectedTable.delivery_charge"
                  class="w-full py-3 text-xl font-bold tracking-wider text-black bg-white rounded-lg cursor-pointer"
                >
                  <option value="">Select Delivery Charge</option>
                  <option v-for="charge in delivery" :key="charge.id" :value="charge.delivery_charge">
                    {{ charge.delivery_charge }} LKR
                  </option>
                </select>
              </div>

              <div
                v-if="selectedTable && selectedTable.id !== 'default' && selectedTable.order_type !== 'pickup'"
                class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black"
              >
                <select
                  v-model="selectedTable.serviceCharge"
                  class="w-full py-3 text-xl font-bold tracking-wider text-black bg-white rounded-lg cursor-pointer"
                >
                  <option value="">Select Service Charge</option>
                  <option
                    v-for="charge in serviceCharge"
                    :key="charge.id"
                    :value="parseFloat(charge.service_charge)"
                  >
                    {{ charge.service_charge }}%{{ charge.service_check === true || charge.service_check === 'true' ? ' (Default)' : '' }}
                  </option>
                </select>
              </div>

              <div class="flex items-center justify-between w-full px-16 pt-4">
                <p class="text-3xl text-black">Total</p>
                <p class="text-3xl text-black">{{ total }} LKR</p>
              </div>

              <div
                v-if="selectedPaymentMethod === 'cash'"
                class="flex items-center justify-between w-full px-16 pt-4 pb-4 border-b border-black"
              >
                <p class="text-xl text-black">Cash</p>
                <span>
                  <CurrencyInput v-if="selectedTable" v-model="selectedTable.cash" :options="{ currency: 'EUR' }" />
                  <span class="ml-2">LKR</span>
                </span>
              </div>

              <div v-if="selectedPaymentMethod === 'card'" class="w-full px-16 pt-4 pb-4 border-b border-black mt-4">
                <div class="flex items-center justify-between w-full mt-4">
                  <p class="text-xl text-black">Select Bank</p>
                  <Combobox v-model="selectedTable.bank_name">
                    <div class="relative w-[150px]">
                      <ComboboxInput
                        class="w-full h-12 border border-gray-300 rounded-md py-2 px-3 text-black"
                        @change="query = $event.target.value"
                        placeholder="Search Bank"
                      />
                      <ComboboxOptions
                        v-if="filteredBanks.length"
                        class="absolute w-full bg-white border border-gray-300 shadow-md rounded-md mt-1 max-h-40 overflow-auto"
                      >
                        <ComboboxOption
                          v-for="bank in filteredBanks"
                          :key="bank"
                          :value="bank"
                          class="p-2 hover:bg-blue-100 cursor-pointer"
                        >
                          {{ bank }}
                        </ComboboxOption>
                      </ComboboxOptions>
                    </div>
                  </Combobox>
                </div>
              </div>

              <div
                v-if="selectedPaymentMethod === 'card'"
                class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black"
              >
                <select
                  v-model="selectedTable.bank_service_charge"
                  class="w-full py-3 text-xl font-bold tracking-wider text-black bg-white rounded-lg cursor-pointer"
                >
                  <option value="">Select Bank Service Charge</option>
                  <option
                    v-for="charge in bankCharge"
                    :key="charge.id"
                    :value="parseFloat(charge.bank_service_charge)"
                  >
                    {{ charge.bank_service_charge }}%{{ charge.service_check === true || charge.service_check === 'true' ? ' (Default)' : '' }}
                  </option>
                </select>
              </div>

              <div v-if="selectedPaymentMethod === 'card'" class="w-full px-16 pt-4 pb-4 border-b border-black mt-4">
                <div class="flex items-center justify-between w-full mt-4">
                  <p class="text-xl text-black">Last 4 Digits of Card</p>
                  <input
                    v-model="selectedTable.card_last4"
                    type="text"
                    maxlength="4"
                    pattern="[0-9]{4}"
                    placeholder="Last 4 Digits"
                    class="w-36 text-center border border-gray-300 rounded-lg py-2 text-x"
                  />
                </div>
              </div>

              <div class="flex items-center justify-between w-full px-16 pt-4 pb-4 border-b border-black">
                <p class="text-xl text-black">Balance</p>
                <p>{{ balance }} LKR</p>
              </div>
            </div>




<!-- Owner Discount -->
<div class="w-full my-5">
  <div class="flex flex-col gap-4 p-5 border border-gray-200 rounded-2xl bg-white shadow-sm">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900">Owner Discount</h3>
      <span
        v-if="ownerDiscountApplied"
        class="inline-flex items-center text-xs font-medium text-green-700 bg-green-50 px-2.5 py-1 rounded-full border border-green-200"
      >
        <i class="ri-check-line mr-1"></i> Applied
      </span>
    </div>

    <!-- Owner select + fetch -->
    <div class="flex items-end gap-3">
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">Owner</label>
        <select
          v-model="ownerForm.owner_id"
          class="w-full h-11 px-3 border border-gray-300 rounded-lg text-black bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">Select Owner</option>
          <option v-for="o in owners" :key="o.id" :value="o.id">
            {{ o.name }} ({{ o.code }})
          </option>
        </select>
        <p v-if="ownerCodeValue" class="mt-1 text-xs text-gray-500">
          Code: <span class="font-medium">{{ ownerCodeValue }}</span>
        </p>
      </div>

      <button
        type="button"
        @click="fetchOwnerDiscount"
        :disabled="!ownerForm.owner_id"
        class="h-11 px-5 rounded-lg font-semibold text-white transition
               disabled:opacity-50 disabled:cursor-not-allowed
               bg-blue-600 hover:bg-blue-700"
      >
        Fetch
      </button>
    </div>

    <!-- Fetched summary -->
    <div
      v-if="ownerFetch.owner_id"
      class="rounded-xl border border-gray-100 bg-gray-50 p-4"
    >
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="flex flex-col">
          <span class="text-xs text-gray-500">Monthly Allocation</span>
          <span class="text-base font-semibold text-gray-900">
            {{ Number(ownerFetch.discount_value || 0).toFixed(2) }} LKR
          </span>
        </div>
        <div class="flex flex-col">
          <span class="text-xs text-gray-500">Used This Month</span>
          <span class="text-base font-semibold text-gray-900">
            {{ Number(ownerFetch.current_discount || 0).toFixed(2) }} LKR
          </span>
        </div>
        <div class="flex flex-col">
          <span class="text-xs text-gray-500">Remaining</span>
          <span class="text-base font-semibold text-blue-700">
            {{ ownerBalance.toFixed(2) }} LKR
          </span>
        </div>
      </div>

      <!-- Progress bar -->
      <div class="mt-3">
        <div class="h-2 w-full bg-white border border-gray-200 rounded-full overflow-hidden">
          <div
            class="h-2 bg-blue-500"
            :style="{ width: Math.min(100, Math.round(((ownerFetch.current_discount || 0) / (ownerFetch.discount_value || 1)) * 100)) + '%'}"
          ></div>
        </div>
        <div class="mt-1.5 flex justify-between text-[11px] text-gray-500">
          <span>0</span>
          <span>{{ Number(ownerFetch.discount_value || 0).toFixed(0) }}</span>
        </div>
      </div>

      <!-- Availability / note -->
      <p
        v-if="!ownerFetch.available && ownerFetch.message"
        class="mt-3 text-sm text-amber-700 bg-amber-50 border border-amber-200 px-3 py-2 rounded-lg"
      >
        <i class="ri-alert-line mr-1"></i>{{ ownerFetch.message }}
      </p>

      <!-- Apply / Remove buttons -->
      <div class="mt-3 flex items-center gap-3">
        <button
          v-if="!ownerDiscountApplied"
          @click="applyOwnerDiscount"
          :disabled="!ownerFetch.available || ownerBalance <= 0"
          class="h-10 px-4 rounded-lg font-medium text-white bg-green-600 hover:bg-green-700
                 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Apply Owner Discount
        </button>

        <button
          v-else
          @click="removeOwnerDiscount"
          class="h-10 px-4 rounded-lg font-medium text-white bg-red-600 hover:bg-red-700"
        >
          Remove Owner Discount
        </button>
      </div>
    </div>

    <!-- Override input (only when applied) -->
    <div v-if="ownerDiscountApplied" class="flex flex-col sm:flex-row sm:items-center gap-3">
      <label class="text-sm font-medium text-gray-700">Override value (LKR)</label>

      <div class="flex items-center gap-2">
        <input
          type="number"
          step="0.01"
          min="0"
          :max="ownerBalance"
          v-model.number="ownerFetch.override_amount"
          class="w-44 h-11 px-3 border border-gray-300 rounded-lg text-black bg-white
                 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          placeholder="0.00"
        />
        <button
          type="button"
          @click="ownerFetch.override_amount = ownerBalance"
          class="h-11 px-3 text-sm font-medium rounded-lg border border-gray-300 bg-white hover:bg-gray-50"
        >
          Use Max
        </button>
      </div>

      <div class="sm:ml-auto text-sm">
        <span class="text-gray-600">
          Remaining:
          <span class="font-semibold">{{ ownerBalance.toFixed(2) }} LKR</span>
        </span>
        <span class="mx-2 text-gray-300">•</span>
        <span
          :class="[
            'font-semibold',
            Number(ownerFetch.override_amount || 0) > ownerBalance ? 'text-red-600' : 'text-green-700'
          ]"
        >
          Override: {{ Number(ownerFetch.override_amount || 0).toFixed(2) }} LKR
        </span>
        <p class="text-xs text-gray-500 mt-0.5">
          Tip: you can’t exceed the remaining amount. Click <span class="font-medium">Use Max</span> to auto-fill.
        </p>
      </div>
    </div>

  </div>
</div>





            <div class="w-full my-1">
              <div class="relative flex items-center">
                <input
                  id="coupon"
                  v-model="selectedTable.kitchen_note"
                  type="text"
                  placeholder="Kitchen Note"
                  class="w-full h-16 px-6 pr-40 text-lg text-gray-800 placeholder-gray-500 border-2 border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>

            <div class="flex flex-col w-full space-y-8">
              <div class="flex items-center justify-center w-full pt-8 space-x-8">
                <p class="text-xl text-black">Payment Method :</p>
                <div
                  @click="selectedPaymentMethod = 'cash'"
                  :class="[
                    'cursor-pointer w-[100px]  border border-black rounded-xl flex flex-col justify-center items-center text-center',
                    selectedPaymentMethod === 'cash' ? 'bg-yellow-500 font-bold' : 'text-black',
                  ]"
                >
                  <img src="/images/money-stack.png" alt="" class="w-24" />
                </div>
                <div
                  @click="selectedPaymentMethod = 'card'"
                  :class="[
                    'cursor-pointer w-[100px] border border-black rounded-xl flex flex-col justify-center items-center text-center',
                    selectedPaymentMethod === 'card' ? 'bg-yellow-500 font-bold' : 'text-black',
                  ]"
                >
                  <img src="/images/bank-card.png" alt="" class="w-24" />
                </div>
              </div>

              <div class="flex items-center justify-center w-full">
                <button
                  @click="submitOrder"
                  type="button"
                  :disabled="!selectedTable || selectedTable.products.length === 0"
                  :class="[
                    'w-full bg-black py-4 text-2xl font-bold tracking-wider text-center text-white uppercase rounded-xl',
                    !selectedTable || selectedTable.products.length === 0 ? 'cursor-not-allowed' : 'cursor-pointer',
                  ]"
                >
                  <i class="pr-4 ri-add-circle-fill"></i> Confirm Order
                </button>
              </div>
            </div>
          </div>
        </div>
        <!-- /Right -->
      </div>
    </div>
  </div>

  <PosSuccessModel
    :open="isSuccessModalOpen"
    @update:open="handleModalOpenUpdate"
    :products="selectedTable.products"
    :cashier="loggedInUser"
    :customer="customer"
    :orderId="selectedTable.orderId"
    :cash="selectedTable.cash"
    :balance="balance"
    :subTotal="subtotal"
    :totalDiscount="totalDiscount"
    :total="total"
    :custom_discount="customDiscCalculated"
    :delivery_charge="selectedTable.delivery_charge"
    :service_charge="selectedTable.service_charge"
    :bank_service_charge="selectedTable.bank_service_charge"
    :selectedTable="selectedTable"
    :kitchen_note="selectedTable.kitchen_note"
    :selectedPaymentMethod="selectedPaymentMethod"
    :order_type="selectedTable.order_type"
    :owner_discount_value="ownerDiscountValue"
    :owner_code="ownerCodeValue"
  />
  <AlertModel v-model:open="isAlertModalOpen" :message="message" />

  <SelectProductModel
    v-model:open="isSelectModalOpen"
    :allcategories="allcategories"
    :colors="colors"
    :sizes="sizes"
    @selected-products="handleSelectedProducts"
  />
  <Footer />
</template>

<script setup>
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import PosSuccessModel from "@/Components/custom/PosSuccessModel.vue";
import AlertModel from "@/Components/custom/AlertModel.vue";

import { useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, computed, watch } from "vue";
import { Head } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import CurrencyInput from "@/Components/custom/CurrencyInput.vue";
import SelectProductModel from "@/Components/custom/SelectProductModel.vue";
import ProductAutoComplete from "@/Components/custom/ProductAutoComplete.vue";
import { generateOrderId } from "@/Utils/Other.js";
import { Combobox, ComboboxInput, ComboboxOptions, ComboboxOption } from "@headlessui/vue";

/* =========================
   PROPS (declare FIRST)
========================= */
const props = defineProps({
  loggedInUser: Object,
  allcategories: Array,
  allemployee: Array,
  colors: Array,
  sizes: Array,
  delivery: Array,
  bankCharge: Array,
  serviceCharge: Array,
  owners: Array,
});

/* =========================
   STATE
========================= */
const product = ref(null);
const error = ref(null);
const products = ref([]);
const isSuccessModalOpen = ref(false);
const isAlertModalOpen = ref(false);
const message = ref("");
const appliedCoupon = ref(null);
const cash = ref(0);
const isSelectModalOpen = ref(false);
const order_type = ref("");
const kitchen_note = ref("");
const delivery_charge = ref("");
const service_charge = ref("");
const bank_service_charge = ref("");
const bankOptions = ref([
  "Alliance Finance Co PLC", "Amana Bank", "American Express Bank Ltd", "Asia Asset Finance PLC",
  "Bank of Ceylon", "Bank of China",
  "CDB", "Cargils Bank Ltd", "Central Bank of Sri Lanka", "Central Finance PLC", "City Bank", "Commercial Bank", "Commercial Credit", "Cooperative Regional Rural Bank LTD",
  "DFCC Bank PLC", "Deutsche Bank", "Dialog Finance PLC",
  "Fintrex Finance Limited",
  "HDFC Bank", "HNB Finance PLC", "HSBC", "Hatton National Bank",
  "Indian Bank", "Indian Overseas Bank", "Kanrich Finance Bank", "LB Finance", "LOLC Development Finance Plc", "LOLC Finance Plc", "Lanka Credit and Business Finance Limited",
  "MBSL", "MCB", "Mercantile Investment", "NDB Bank", "NSB", "Nations Trust Bank",
  "Peoples Leasing and Finance PLC", "Pan Asia Bank", "Peoples Bank", "Public Bank Berhad",
  "RDB", "Richard Pieris Finance", "SDB", "SENKADAGALA FINANCE", "SMIB", "Sampath Bank", "Sarvodaya Development Finace LTD", "Seylan Bank", "Singer Finance(Lanka) Bank", "Siyapatha Finance PLC", "Softlogic Finance PLC", "Standard Charted Bank", "State Bank of India", "Union Bank"
]);

const query = ref("");
const filteredBanks = computed(() =>
  query.value
    ? bankOptions.value.filter(bank => bank.toLowerCase().includes(query.value.toLowerCase()))
    : bankOptions.value
);

// Tables state - load/save LS
const savedTables = JSON.parse(localStorage.getItem("tables")) || [
  {
    id: "default",
    number: 1,
    orderId: generateOrderId(),
    products: [],
    balance: 0,
    custom_discount: 0.0,
    custom_discount_type: "percent",
    kitchen_note: "",
    order_type: "",
    delivery_charge: "",
    service_charge: "",
    bank_service_charge: "",
  },
];

const savedNextTableNumber = JSON.parse(localStorage.getItem("nextTableNumber")) || 2;
const savedSelectedTable = JSON.parse(localStorage.getItem("selectedTable")) || savedTables[0];

const tables = ref(savedTables);
const nextTableNumber = ref(savedNextTableNumber);
const selectedTable = ref(savedSelectedTable);

watch(
  tables,
  (newTables) => {
    localStorage.setItem("tables", JSON.stringify(newTables));
  },
  { deep: true }
);

watch(nextTableNumber, (newNextTableNumber) => {
  localStorage.setItem("nextTableNumber", JSON.stringify(newNextTableNumber));
});

watch(
  selectedTable,
  (newSelectedTable) => {
    localStorage.setItem("selectedTable", JSON.stringify(newSelectedTable));
  },
  { deep: true }
);

/* =========================
   OWNER DISCOUNT STATE
========================= */
const ownerForm = useForm({
  owner_id: "",
});

const ownerFetch = ref({
  owner_id: null,
  discount_value: 0,     // monthly allocation (LKR)
  current_discount: 0,   // already used (LKR)
  month: "",
  available: false,
  message: "",
  override_amount: 0,    // amount to apply now
});

const ownerDiscountApplied = ref(false);

const selectedOwner = computed(() => {
  return props.owners.find(o => o.id === ownerForm.owner_id) || null;
});

const ownerCodeValue = computed(() => {
  return selectedOwner.value ? selectedOwner.value.code : null;
});

const ownerBalance = computed(() => {
  const dv = Number(ownerFetch.value.discount_value || 0);
  const cd = Number(ownerFetch.value.current_discount || 0);
  return Math.max(0, dv - cd);
});

/* Clamp override to remaining balance, show alert if exceeded */
watch(
  () => ownerFetch.value.override_amount,
  (val) => {
    let n = Number(val);
    if (isNaN(n) || n < 0) {
      ownerFetch.value.override_amount = 0;
      return;
    }
    if (n > ownerBalance.value) {
      ownerFetch.value.override_amount = ownerBalance.value;
      isAlertModalOpen.value = true;
      message.value = `Override exceeds remaining balance. Max allowed is ${ownerBalance.value.toFixed(2)} LKR.`;
    }
  }
);

/* =========================
   OWNER DISCOUNT ACTIONS
========================= */
const fetchOwnerDiscount = async () => {
  if (!ownerForm.owner_id) {
    isAlertModalOpen.value = true;
    message.value = "Please select an owner.";
    return;
  }
  try {
    const { data } = await axios.post("/pos/get-owner-discount", {
      owner_id: ownerForm.owner_id,
    });
    ownerFetch.value = {
      owner_id: data.owner_id,
      discount_value: Number(data.discount_value || 0),
      current_discount: Number(data.current_discount || 0),
      override_amount: 0, // reset to 0 on fetch
      month: data.month || "",
      available: !!data.available,
      message: data.message || "",
    };
  } catch (err) {
    isAlertModalOpen.value = true;
    message.value = err.response?.data?.message || "Failed to get owner discount.";
    ownerFetch.value = {
      owner_id: null,
      discount_value: 0,
      current_discount: 0,
      month: "",
      available: false,
      message: "",
      override_amount: 0,
    };
  }
};

const applyOwnerDiscount = () => {
  if (!ownerFetch.value.available) {
    isAlertModalOpen.value = true;
    message.value = ownerFetch.value.message || "No discount found for this owner.";
    return;
  }
  ownerDiscountApplied.value = true;
};

const removeOwnerDiscount = () => {
  ownerDiscountApplied.value = false;
};

const resetOwnerState = () => {
  ownerForm.owner_id = "";
  ownerFetch.value = {
    owner_id: null,
    discount_value: 0,
    current_discount: 0,
    month: "",
    available: false,
    message: "",
    override_amount: 0,
  };
  ownerDiscountApplied.value = false;
};

/* This is the amount actually applied into totals */
const ownerDiscountValue = computed(() => {
  return ownerDiscountApplied.value ? Number(ownerFetch.value.override_amount || 0) : 0;
});

/* =========================
   POS LOGIC
========================= */
const discount = ref(0);

const customer = ref({
  name: "",
  countryCode: "",
  contactNumber: "",
  email: "",
  bdate: "",
});

const employee_id = ref("");
const selectedPaymentMethod = ref("cash");

const refreshData = async () => {
  if (selectedTable.value?.id === "default") {
    const today = new Date();
    const formattedDate = `${today.getFullYear().toString().slice(-2)}.${String(today.getMonth() + 1).padStart(2, "0")}.${String(today.getDate()).padStart(2, "0")}`;

    const lastDate = localStorage.getItem("lastOrderDate");
    let existingOrderId = selectedTable.value.orderId;

    if (lastDate !== formattedDate) {
      existingOrderId = generateOrderId();
    }

    const defaultTable = {
      id: "default",
      number: 1,
      orderId: existingOrderId,
      products: [],
      cash: 0.0,
      balance: 0.0,
      custom_discount: 0.0,
      custom_discount_type: "percent",
      kitchen_note: "",
      order_type: "",
      delivery_charge: "",
      service_charge: "",
      bank_service_charge: "",
      owner_discount_value: "",
    };

    selectedTable.value = defaultTable;

    const defaultIndex = tables.value.findIndex(table => table.id === "default");
    if (defaultIndex !== -1) {
      tables.value[defaultIndex] = defaultTable;
    }

    customer.value = {
      name: "",
      contactNumber: "",
      email: "",
      bdate: "",
    };

    appliedCoupon.value = null;
    cash.value = 0;
    selectedPaymentMethod.value = "cash";
    employee_id.value = "";

    localStorage.setItem("tables", JSON.stringify(tables.value));
    localStorage.setItem("selectedTable", JSON.stringify(selectedTable.value));

    try {
      await router.reload({
        only: ['loggedInUser', 'allcategories', 'allemployee', 'colors', 'sizes', 'delivery', 'serviceCharge', 'bankCharge'],
        preserveState: false,
        preserveScroll: false
      });
    } catch (error) {
      console.error("Error refreshing data:", error);
      window.location.reload();
    }

    resetOwnerState();
  }
};

const addTable = () => {
  const usedNumbers = tables.value.map((table) => table.number);
  let newNumber = 1;
  while (usedNumbers.includes(newNumber)) newNumber++;

  const newTable = {
    id: Date.now(),
    number: newNumber,
    orderId: generateOrderId(),
    products: [],
    cash: 0.0,
    balance: 0.0,
    custom_discount: 0.0,
    custom_discount_type: "percent",
    kitchen_note: "",
    order_type: "",
    delivery_charge: "",
    service_charge: "",
    bank_service_charge: "",
    kotStatus: "pending",
  };

  tables.value.push(newTable);
  selectedTable.value = newTable;

  localStorage.removeItem(`removedTable_${newNumber}`);
};

const selectTable = (table) => {
  selectedTable.value = table;
};

const removeTable = (index) => {
  const removedTable = tables.value.splice(index, 1)[0];

  if (tables.value.length > 0) {
    selectedTable.value = tables.value[tables.value.length - 1];
  } else {
    selectedTable.value = null;
  }

  localStorage.setItem(`removedTable_${removedTable.number}`, JSON.stringify(removedTable));
};

const removeSelectedTable = () => {
  if (!selectedTable.value) return;

  const index = tables.value.findIndex((table) => table.id === selectedTable.value.id);

  if (selectedTable.value.id === "default") {
    selectedTable.value = {
      id: "default",
      number: 1,
      orderId: generateOrderId(),
      products: [],
      cash: 0.0,
      balance: 0.0,
      custom_discount: 0.0,
      custom_discount_type: "percent",
      kitchen_note: "",
      order_type: "",
      delivery_charge: "",
      service_charge: "",
      bank_service_charge: "",
    };
    tables.value[index] = selectedTable.value;
    return;
  }

  if (index === -1) return;

  tables.value.splice(index, 1);
  selectedTable.value = tables.value.length > 0 ? tables.value[Math.min(index, tables.value.length - 1)] : null;
};

const handleModalOpenUpdate = (newValue) => {
  isSuccessModalOpen.value = newValue;
  if (!newValue) {
    removeSelectedTable();
    cash.value = 0;
    refreshData();
  }
};

const orderId = computed(() => {
  const characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  return Array.from({ length: 6 }, () => characters.charAt(Math.floor(Math.random() * characters.length))).join("");
});

const submitOrder = async () => {
  if (!total.value || parseFloat(total.value) <= 0) {
    isAlertModalOpen.value = true;
    message.value = "Total amount cannot be zero or less. Please check the bill.";
    return;
  }

  if (balance.value < 0) {
    isAlertModalOpen.value = true;
    message.value = "Cash is not enough";
    return;
  }

  try {
    const response = await axios.post("/pos/submit", {
      customer: customer.value,
      products: selectedTable.value.products,
      employee_id: employee_id.value,
      paymentMethod: selectedPaymentMethod.value,
      userId: props.loggedInUser.id,
      orderId: selectedTable.value.orderId,
      custom_discount: customDiscCalculated.value,
      cash: selectedTable.value.cash,
      bank_name: selectedTable.value.bank_name,
      card_last4: selectedTable.value.card_last4,
      kitchen_note: selectedTable.value.kitchen_note,
      delivery_charge: selectedTable.value.delivery_charge,
      service_charge: selectedTable.value.service_charge,
      bank_service_charge: selectedTable.value.bank_service_charge,
      order_type: selectedTable.value.order_type,
      total: total.value,

      owner_id: ownerForm.owner_id || null,
      owner_discount_value: ownerDiscountValue.value,       // applied discount (override)
      owner_override_amount: ownerFetch.value.override_amount || 0, // also send explicitly
    });

    isSuccessModalOpen.value = true;
    selectedTable.value.orderId = generateOrderId();
    customer.value = {
      name: "",
      countryCode: "",
      contactNumber: "",
      email: "",
    };
  } catch (error) {
    if (error.response?.status === 423) {
      isAlertModalOpen.value = true;
      message.value = error.response.data.message;
    }
    console.error("Error submitting customer details:", error.response?.data || error.message);
  }
};

const subtotal = computed(() => {
  if (!selectedTable.value) return "0.00";
  return selectedTable.value.products
    .reduce((t, item) => t + parseFloat(item.selling_price) * item.quantity, 0)
    .toFixed(2);
});

const totalDiscount = computed(() => {
  if (!selectedTable.value) return "0.00";

  const productDiscount = selectedTable.value.products.reduce((total, item) => {
    if (item.discount && item.discount > 0 && item.apply_discount === true) {
      const discountAmount = (parseFloat(item.selling_price) - parseFloat(item.discounted_price)) * item.quantity;
      return total + discountAmount;
    }
    return total;
  }, 0);

  const couponDiscount = appliedCoupon.value ? Number(appliedCoupon.value.discount) : 0;
  const ownerDisc = ownerDiscountValue.value || 0;

  return (productDiscount + couponDiscount + ownerDisc).toFixed(2);
});

const total = computed(() => {
  const subtotalValue = parseFloat(subtotal.value) || 0;
  const discountValue = parseFloat(totalDiscount.value) || 0;
  const customDiscount = parseFloat(selectedTable.value.custom_discount) || 0;

  let customValue = 0;
  if (selectedTable.value.custom_discount_type === "percent") {
    customValue = (subtotalValue * customDiscount) / 100;
  } else if (selectedTable.value.custom_discount_type === "fixed") {
    customValue = customDiscount;
  }

  let deliveryChargeValue = 0;
  if (selectedTable.value.order_type === "pickup") {
    deliveryChargeValue = parseFloat(selectedTable.value.delivery_charge) || 0;
  }

  const serviceChargeRate = parseFloat(selectedTable.value.service_charge) || 0;
  const serviceChargeValue = (subtotalValue * serviceChargeRate) / 100;

  const preBankTotal = subtotalValue - discountValue - customValue + deliveryChargeValue + serviceChargeValue;

  const bankServiceChargeRate = parseFloat(selectedTable.value.bank_service_charge) || 0;
  const bankServiceChargeAmount = (preBankTotal * bankServiceChargeRate) / 100;

  return (preBankTotal + bankServiceChargeAmount).toFixed(2);
});

const customDiscCalculated = computed(() => {
  const subtotalValue = parseFloat(subtotal.value) || 0;
  const customDiscount = parseFloat(selectedTable.value.custom_discount) || 0;

  let customValue = 0;
  if (selectedTable.value.custom_discount_type === "percent") {
    customValue = (subtotalValue * customDiscount) / 100;
  } else if (selectedTable.value.custom_discount_type === "fixed") {
    customValue = customDiscount;
  }

  return customValue.toFixed(2);
});

const balance = computed(() => {
  if (!selectedTable.value) return 0;
  if (selectedTable.value.cash == null || selectedTable.value.cash === 0) return 0;
  return (parseFloat(selectedTable.value.cash) - parseFloat(total.value)).toFixed(2);
});

/* =========================
   COUPON & BARCODE
========================= */
const form = useForm({
  employee_id: "",
  barcode: "",
});

const couponForm = useForm({
  code: "",
});

const submitCoupon = async () => {
  try {
    const response = await axios.post(route("pos.getCoupon"), {
      code: couponForm.code,
    });

    const { coupon: fetchedCoupon, error: fetchedError } = response.data;

    if (fetchedCoupon) {
      appliedCoupon.value = fetchedCoupon;
      products.value.forEach((product) => {
        product.apply_discount = false;
      });
    } else {
      isAlertModalOpen.value = true;
      message.value = fetchedError;
      error.value = fetchedError;
    }
  } catch (err) {
    if (err.response?.status === 422) {
      isAlertModalOpen.value = true;
      message.value = err.response.data.message;
    }
  }
};

// Barcode scan handling
let barcode = "";
let timeout;

const submitBarcode = async () => {
  try {
    const response = await axios.post(route("pos.getProduct"), {
      barcode: form.barcode,
    });

    const { product: fetchedProduct, error: fetchedError } = response.data;

    if (fetchedProduct) {
      if (fetchedProduct.stock_quantity < 1) {
        isAlertModalOpen.value = true;
        message.value = "Product is out of stock";
        return;
      }

      const existingProduct = products.value.find((item) => item.id === fetchedProduct.id);

      if (existingProduct) {
        existingProduct.quantity += 1;
      } else {
        products.value.push({
          ...fetchedProduct,
          quantity: 1,
          apply_discount: false,
        });
      }

      product.value = fetchedProduct;
      error.value = null;
    } else {
      isAlertModalOpen.value = true;
      message.value = fetchedError;
      error.value = fetchedError;
    }
  } catch (err) {
    if (err.response?.status === 422) {
      isAlertModalOpen.value = true;
      message.value = err.response.data.message;
    }
    error.value = "An unexpected error occurred. Please try again.";
  }
};

const handleScannerInput = (event) => {
  clearTimeout(timeout);
  if (event.key === "Enter") {
    form.barcode = barcode;
    submitBarcode();
    barcode = "";
  } else {
    barcode += event.key;
  }
  timeout = setTimeout(() => {
    barcode = "";
  }, 100);
};

onMounted(() => {
  document.addEventListener("keypress", handleScannerInput);
});

/* =========================
   ITEM DISCOUNTS & SELECT
========================= */
const applyDiscount = (id) => {
  if (!selectedTable.value) return;
  const p = selectedTable.value.products.find((item) => item.id === id);
  if (p) p.apply_discount = true;
};

const removeDiscount = (id) => {
  if (!selectedTable.value) return;
  const p = selectedTable.value.products.find((item) => item.id === id);
  if (p) p.apply_discount = false;
};

const handleSelectedProducts = (selectedProducts) => {
  if (!selectedTable.value) return;

  selectedProducts.forEach((fetchedProduct) => {
    const existingProduct = selectedTable.value.products.find((item) => item.id === fetchedProduct.id);
    if (existingProduct) {
      existingProduct.quantity += 1;
    } else {
      selectedTable.value.products.push({
        ...fetchedProduct,
        quantity: 1,
        apply_discount: false,
      });
    }
  });
};

const searchCustomer = async () => {
  let contact = customer.value.contactNumber;
  customer.value = {
    name: "",
    countryCode: "",
    contactNumber: contact,
    email: "",
  };
  try {
    const response = await axios.post("/api/check-customer", {
      contactNumber: contact,
    });

    if (response.data.customer) {
      const c = response.data.customer;
      customer.value.name = c.name;
      customer.value.email = c.email;
      customer.value.bdate = c.bdate;
    }
  } catch (error) {
    console.error("Error fetching customer:", error.response?.data || error.message);
  }
};
</script>
