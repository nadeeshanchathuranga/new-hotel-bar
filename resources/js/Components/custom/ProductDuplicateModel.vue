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
            class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl w-5/6 lg:w-3/6 p-10 text-center"
          >
            <!-- Close Button -->

            <!-- Modal Title -->
            <DialogTitle class="text-xl font-bold text-white"
              >{{ form.name }} - Variation Set</DialogTitle
            >
            <form @submit.prevent="submit">
  <!-- Modal Form -->
  <div class="mt-6 space-y-4 text-left">

    <!-- Product Name & Category -->
    <div class="flex items-center gap-8 mt-6">
      <div class="w-full">
        <label class="block text-sm font-medium text-gray-300">
          Product Name:
        </label>
        <input
          v-model="form.name"
          type="text"
          id="name"
          required
          placeholder="Enter Product Name"
          class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
        />
        <span v-if="form.errors.name" class="mt-2 text-red-500">
          {{ form.errors.name }}
        </span>
      </div>

      <div class="w-full">
        <label class="block text-sm font-medium text-gray-300">
          Category Name:
        </label>
        <select
          required
          v-model="form.category_id"
          id="category_id"
          class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
        >
          <option value="">Select a Category</option>
          <option
            v-for="category in categories"
            :key="category.id"
            :value="category.id"
          >
            {{ category.hierarchy_string ? category.hierarchy_string + ' ----> ' + category.name : category.name }}
          </option>
        </select>
        <span v-if="form.errors.category_id" class="mt-2 text-red-500">
          {{ form.errors.category_id }}
        </span>
      </div>
    </div>

    <!-- Size & Base -->
    <div class="flex items-center gap-8 mt-6">
      <div class="w-full">
        <label class="block text-sm font-medium text-gray-300">Size:</label>
        <select
          v-model="form.size_id"
          id="size_id"
          class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
        >
          <option value="">Select a Size</option>
          <option v-for="size in sizes" :key="size.id" :value="size.id">
            {{ size.name }}
          </option>
        </select>
        <span v-if="form.errors.size_id" class="mt-2 text-red-500">
          {{ form.errors.size_id }}
        </span>
      </div>

      <div class="w-full">
        <label class="block text-sm font-medium text-gray-300">Base:</label>
        <select
          v-model="form.color_id"
          id="color_id"
          class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
        >
          <option value="">Select a Base</option>
          <option v-for="color in colors" :key="color.id" :value="color.id">
            {{ color.name }}
          </option>
        </select>
        <span v-if="form.errors.color_id" class="mt-2 text-red-500">
          {{ form.errors.color_id }}
        </span>
      </div>
    </div>

    <!-- Cost, Selling, Discount -->
    <div class="flex items-center gap-8 mt-6">




<div class="w-full">
       <label
                      for="doller_price"
                      class="block text-sm font-medium text-gray-300"
                      >Doller Price ($) :</label
                    >
        <input
          type="number"
          step="0.01"
          v-model="form.doller_price"
          id="doller_price"
          placeholder="Enter Doller Price"
          class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
          
        />
        <span v-if="form.errors.doller_price" class="mt-2 text-red-500">
          {{ form.errors.doller_price }}
        </span>
      </div>








      <div class="w-full">
        <label class="block text-sm font-medium text-gray-300">Cost Price:</label>
        <input
          type="number"
          step="0.01"
          v-model="form.cost_price"
          id="cost_price"
          placeholder="Enter cost price"
          class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
          required
        />
        <span v-if="form.errors.cost_price" class="mt-2 text-red-500">
          {{ form.errors.cost_price }}
        </span>
      </div>

      <div class="w-full">
        <label class="block text-sm font-medium text-gray-300">Selling Price:</label>
        <input
          type="text"
          v-model="form.selling_price"
          id="selling_price"
          @blur="updateDiscountedPrice"
          placeholder="Enter selling price"
          class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
          required
        />
        <span v-if="form.errors.selling_price" class="mt-2 text-red-500">
          {{ form.errors.selling_price }}
        </span>
      </div>
    </div>

    <div class="flex items-center gap-8 mt-6">
      <div class="w-full">
        <label class="block text-sm font-medium text-gray-300">Discount (%):</label>
        <input
          type="text"
          id="discount"
          v-model="form.discount"
          @blur="updateDiscountedPrice"
          placeholder="Enter discount percentage"
          min="0"
          max="100"
          class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
        />
        <span v-if="form.errors.discount" class="mt-2 text-red-500">
          {{ form.errors.discount }}
        </span>
      </div>

      <div class="w-full">
        <label class="block text-sm font-medium text-gray-300">Discounted Price:</label>
        <input
          type="text"
          v-model="form.discounted_price"
          id="discounted_price"
          @blur="updateDiscount"
          placeholder="Discounted price"
          class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
        />
        <span v-if="form.errors.discounted_price" class="mt-2 text-red-500">
          {{ form.errors.discounted_price }}
        </span>
      </div>
    </div>

    <!-- Stock & Supplier -->
    <div class="flex items-center gap-8 mt-6">
      <div class="w-full">
        <label class="block text-sm font-medium text-gray-300">Stock Quantity:</label>
        <input
          type="number"
          v-model="form.stock_quantity"
          id="stock_quantity"
          placeholder="Enter stock quantity"
          class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
          required
        />
        <span v-if="form.errors.stock_quantity" class="mt-2 text-red-500">
          {{ form.errors.stock_quantity }}
        </span>
      </div>

      <div class="w-full">
        <label class="block text-sm font-medium text-gray-300">Supplier:</label>
        <select
          v-model="form.supplier_id"
          id="supplier_id"
          class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
        >
          <option value="">Select a Supplier</option>
          <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
            {{ supplier.name }}
          </option>
        </select>
        <span v-if="form.errors.supplier_id" class="mt-2 text-red-500">
          {{ form.errors.supplier_id }}
        </span>
      </div>
    </div>

    <!-- Description -->
    <div class="mt-6">
      <label class="block text-sm font-medium text-gray-300">Description:</label>
      <textarea
        v-model="form.description"
        id="description"
        placeholder="Enter Description"
        class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
        rows="2"
      ></textarea>
      <span v-if="form.errors.description" class="mt-2 text-red-500">
        {{ form.errors.description }}
      </span>
    </div>

    <!-- Image Upload -->
    <div class="flex items-center gap-8 mt-6">
      <div class="w-full">
        <label for="image" class="block text-sm font-medium text-gray-300">
          Product Image:
        </label>

        <!-- Current Image Preview -->
        <div class="w-full md:w-6/12 mt-2">
          <label class="block text-sm font-medium text-white">Current Image</label>
          <div class="mt-2">
            <img
              v-if="selectedProduct.image"
              :src="`/${selectedProduct.image}`"
              alt="Product Image"
              class="rounded-lg"
              style="width: 80px; height: 80px"
            />
            <p v-else class="text-sm text-gray-500">No image available</p>
          </div>
        </div>

        <!-- Upload New -->
        <input
          type="file"
          id="image"
          @change="handleImageUpload"
          class="w-full px-4 py-2 mt-2 text-white bg-gray-800 rounded-md focus:outline-none focus:ring focus:ring-blue-600"
        />
        <span v-if="form.errors.image" class="mt-2 text-red-500">
          {{ form.errors.image }}
        </span>
      </div>
    </div>
  </div>

  <!-- Modal Buttons -->
  <div class="mt-6 space-x-4">
    <button
      class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
      type="submit"
      @click="() => { playClickSound(); }"
    >
      Save
    </button>
    <button
      type="button"
      class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
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
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { ref, watch, computed } from "vue";
import { useForm } from "@inertiajs/vue3";

// Emit event to toggle modal visibility
const emit = defineEmits(["update:open"]);

// Play click sound function
const playClickSound = () => {
  const clickSound = new Audio("/sounds/click-sound.mp3");
  clickSound.play();
};

// Define props
const { open, categories, colors, suppliers, sizes, selectedProduct } =
  defineProps({
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
    suppliers: {
      type: Array,
      required: true,
    },
    sizes: {
      type: Array,
      required: true,
    },
    selectedProduct: {
      type: Object,
      default: null,
    },
  });

const form = useForm({
  category_id: "",
  supplier_id: "",
  name: "",
  code: "",
  size_id: "",
  color_id: "",
  cost_price: null,
  discount: 0,
  discounted_price: null,
  selling_price: null,
  doller_price: null,
  stock_quantity: null,
  barcode: "",
  image: null, // For file upload
  description: "",
});

// Handle file upload for images
const handleImageUpload = (event) => {
  form.image = event.target.files[0];
};

function limitToTwoDecimals(value) {
  if (value === null || value === undefined) return value;
  const strValue = value.toString();
  const match = strValue.match(/^(\d+)(\.\d{0,2})?/); // Match up to 2 decimal places
  return match ? parseFloat(match[0]) : value;
}

// Function to update discounted price based on selling price and discount
function updateDiscountedPrice() {
  if (form.selling_price && form.discount) {
    const discountAmount = (form.selling_price * form.discount) / 100;
    form.discounted_price = limitToTwoDecimals(
      form.selling_price - discountAmount
    );
  }
}

// Function to update discount based on selling price and discounted price
function updateDiscount() {
  if (form.selling_price && form.discounted_price) {
    const discountAmount = form.selling_price - form.discounted_price;
    form.discount = limitToTwoDecimals(
      (discountAmount / form.selling_price) * 100
    );
  }
}

// Utility function to limit to 2 decimal points
// function limitToTwoDecimals(value) {
//     if (value === null || value === undefined) return value;
//     const strValue = value.toString();
//     const match = strValue.match(/^(\d+)(\.\d{0,2})?/); // Match up to 2 decimal places
//     return match ? parseFloat(match[0]) : value;
// }

// // Computed property for dynamically calculating the discounted price
// const discountedPrice = computed(() => {
//     if (form.selling_price && form.discount) {
//         const discountAmount = (form.selling_price * form.discount) / 100;
//         return limitToTwoDecimals(form.selling_price - discountAmount);
//     }
//     return form.selling_price || 0;
// });

// // Watch the computed discounted price and update the form's discounted_price field
// watch(discountedPrice, (newValue) => {
//     form.discounted_price = limitToTwoDecimals(newValue);
// });

// // Watch the discounted_price field to dynamically calculate the discount percentage
// watch(() => form.discounted_price, (newDiscountedPrice) => {
//     if (form.selling_price && newDiscountedPrice) {
//         const discountAmount = form.selling_price - parseFloat(newDiscountedPrice);
//         form.discount = limitToTwoDecimals((discountAmount / form.selling_price) * 100);
//     }
// });

// Watch for changes in selectedProduct and populate form
watch(
  () => selectedProduct,
  (newValue) => {
    if (newValue) {
      form.category_id = newValue.category_id || "";
      form.name = newValue.name || "";
      form.code = newValue.code || "";
      form.supplier_id = newValue.supplier_id || "";
      form.stock_quantity = newValue.stock_quantity || null;
      form.size_id = newValue.size_id || "";
      form.color_id = newValue.color_id || "";
      form.cost_price = newValue.cost_price || null;
      form.discount = newValue.discount || 0;
      form.selling_price = newValue.selling_price || null;
      form.doller_price = newValue.doller_price || null;
      form.discounted_price = newValue.discounted_price || null;
      form.barcode = newValue.barcode || "";
      form.image = newValue.image;
    }
  },
  { immediate: true }
);

const submit = () => {
  form.post("/products-variant", {
    preserveScroll: true,
    onSuccess: () => {
      console.log("Product Variation successfully created!");
      form.reset(); // Reset form fields after successful submission
      emit("update:open", false);
    },
    onError: (errors) => {
      console.error("Form submission failed:", errors);
    },
  });
};
</script>
