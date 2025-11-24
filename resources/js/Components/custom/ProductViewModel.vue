<template>
   <TransitionRoot as="template" :show="open">
      <Dialog class="relative z-10" @close="$emit('update:open', false)">
         <!-- Modal Overlay -->
         <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="ease-in duration-200"
            leave-from="opacity-100"
            leave-to="opacity-0"
            >
            <div
               class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
               />
         </TransitionChild>
         <!-- Modal Content -->
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
                  class="bg-white text-black border-4 border-blue-600 rounded-[20px] shadow-xl w-5/6 lg:w-4/6 p-6"
                  >
                  <div
                     class="flex flex-col items-start justify-start w-full h-full px-2 pt-4"
                     >
                     <div
                        class="flex justify-center w-full h-full py-4 space-x-8 items-start-center"
                        >
                        <!-- Left Side: Image -->
                        <div class="w-1/2">
                           <img
                              :src="
                              selectedProduct.image
                              ? `/${selectedProduct.image}`
                              : '/images/placeholder.jpg'
                              "
                              alt="Product Image"
                              class="object-cover h-full rounded-2xl"
                              />
                        </div>
                        <!-- Right Side: Text Content -->
                       <div class="flex flex-col justify-between w-1/2 h-full space-y-8 bg-white p-6 rounded-2xl shadow-lg">
  <!-- Product Header -->
  <div class="flex items-start justify-between">
    <div>
      <h2 class="text-3xl font-bold text-gray-900 leading-tight">
        {{ selectedProduct.id }}
      </h2>
      <h2 class="text-3xl font-bold text-gray-900 leading-tight">
        {{ selectedProduct.name }}
      </h2>
      <p class="mt-1 text-lg italic text-gray-500">
        {{ selectedProduct.category?.name ?? "No Category" }}
      </p>
    </div>
    <span
      v-if="selectedProduct.discount && selectedProduct.discount > 0"
      class="px-3 py-1 text-sm font-semibold text-white bg-red-600 rounded-full shadow-sm"
    >
      {{ selectedProduct.discount }}% OFF
    </span>
  </div>

  <!-- Product Info -->
  <div class="grid grid-cols-2 gap-y-4 gap-x-8 text-xl">
    <div class="flex flex-col">
      <span class="text-gray-500">Code</span>
      <span class="font-semibold text-gray-900">
        {{ selectedProduct?.code ?? "N/A" }}
      </span>
    </div>

    <div class="flex flex-col">
      <span class="text-gray-500">Quantity</span>
      <span class="font-semibold text-gray-900">
        {{ selectedProduct?.stock_quantity ?? "N/A" }}
      </span>
    </div>

    <div class="flex flex-col">
      <span class="text-gray-500">Cost Price</span>
      <span class="font-semibold text-gray-900">
        {{ selectedProduct?.cost_price ?? "N/A" }} LKR
      </span>
    </div>

    <div class="flex flex-col">
      <span class="text-gray-500">LKR Price</span>
      <span class="font-semibold text-gray-900">
        {{ selectedProduct?.selling_price ?? "N/A" }} LKR
      </span>
    </div>

    <div class="flex flex-col">
      <span class="text-gray-500">Dollar Price</span>
      <span class="font-semibold text-gray-900">
        $ {{ selectedProduct?.doller_price ?? "N/A" }}
      </span>
    </div>
  </div>

  <!-- Created Date -->
  <div class="pt-4 border-t border-gray-200">
    <p class="text-xl text-gray-600">
      <span class="font-normal">Created On :</span>
      <span class="font-semibold text-gray-900">{{ formattedDate }}</span>
    </p>
  </div>
</div>

                     </div>
                     <div class="w-full">
                        <!-- Hidden container for printing -->
                        <div
                           :class="{ hidden: !isVisible }"
                           class="relative print-container"
                           id="printContainer"
                           >
                           <div
                              class="flex items-center justify-center w-full space-x-4"
                              >
                              <p class="text-md font-bold text-black">
                                 {{ selectedProduct.category?.name || "N/A" }}
                              </p>
                              <p class="text-md font-bold text-black">
                                 {{ selectedProduct?.selling_price ?? "N/A" }}
                                 LKR
                              </p>
                           </div>
                           <svg id="barcodePrint"></svg>
                           <!-- Barcode -->
                           <p
                              style="
                              color: #000;
                              text-align: center;
                              width: 100%;
                              padding-bottom: 5px;
                              "
                              >
                              {{ selectedProduct?.code ?? "N/A" }}
                           </p>
                           <p style="color: #000; text-align: center; width: 100%">
                              [{{ selectedProduct.size?.name || "N/A" }}] -
                              {{ selectedProduct.color?.name || "N/A" }}
                           </p>
                           <p style="color: #000; text-align: center; width: 100%">
                              {{ selectedProduct?.name ?? "N/A" }}
                           </p>
                        </div>
                     </div>
                  </div>
               </DialogPanel>
            </TransitionChild>
         </div>
      </Dialog>
   </TransitionRoot>
</template>
<script setup>
   import {
     Dialog,
     DialogPanel,
     DialogTitle,
     TransitionChild,
     TransitionRoot,
   } from "@headlessui/vue";
   import { ref, watch, computed } from "vue";
   import { useForm } from "@inertiajs/vue3";
   import dayjs from "dayjs";
   import { HasRole } from "@/Utils/Permissions";
   
   const playClickSound = () => {
     const clickSound = new Audio("/sounds/click-sound.mp3");
     clickSound.play();
   };
   
   // Extend Day.js for ordinal formatting
   import advancedFormat from "dayjs/plugin/advancedFormat";
   dayjs.extend(advancedFormat);
   
   const emit = defineEmits(["update:open"]);
   
   // The `open` prop controls the visibility of the modal
   const { selectedProduct } = defineProps({
     open: {
       type: Boolean,
       required: true,
     },
     categories: {
       type: Array,
       required: true,
     },
     colors: {
       type: Array,
       required: true,
     },
     sizes: {
       type: Array,
       required: true,
     },
     selectedProduct: {
       type: Object,
       default: null, // Ensure it defaults to null
     },
   });
   
   // Computed property to format the date
   const formattedDate = computed(() =>
     selectedProduct && selectedProduct.created_at
       ? dayjs(selectedProduct.created_at).format("Do MMMM YYYY")
       : ""
   );
   
   function generateAndPrintBarcode() {
     const input = document.getElementById("barcodeInput").value;
     const barcodePrintElement = document.getElementById("barcodePrint");
   
     if (input.trim() === "") {
       alert("Please enter text to generate and print a barcode.");
       return;
     }
   
     JsBarcode(barcodePrintElement, input, {
       format: "CODE128", // Code 128 is compact and ideal for small labels
       lineColor: "#000", // Black lines for high contrast
       width: 1.3, // Narrower lines to fit more content within the label
       height: 60, // Shorter height to fit within the 30mm space
       displayValue: false, // Disable text display if it overlaps with the barcode
       margin: 0, // Remove default margins to maximize space usage
     });
   
     // JsBarcode(barcodePrintElement, input, {
     //   format: "CODE128",
     //   // format: "EAN13",
     //   lineColor: "#000",
     //   width: 1.25,
     //   height: 100,
     //   displayValue: false,
     // });
   
     const printContents = document.getElementById("printContainer").innerHTML;
     const originalContents = document.body.innerHTML;
   
     document.body.innerHTML = printContents;
     window.print();
     document.body.innerHTML = originalContents;
   
     location.reload();
   }
</script>
<style>
   @media print {
   #barcodePrint {
   display: block;
   /* Ensure the SVG behaves like a block-level element */
   margin: 0 auto;
   /* Horizontally center using auto margins */
   /* margin-top: 10px; */
   }
   .print-container {
   display: flex;
   justify-content: center;
   /* Horizontally center content inside the container */
   align-items: center;
   /* Vertically center content inside the container */
   height: 100%;
   /* Ensure container takes full height for vertical centering */
   text-align: center;
   /* Center text within the container */
   }
   }
</style>