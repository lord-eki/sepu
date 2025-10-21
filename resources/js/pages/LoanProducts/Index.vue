<template>
  <AppLayout :breadcrumbs="[{ title: 'Loan Products' }]">
    <!-- Flash Messages -->
    <transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="opacity-0 -translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0 -translate-y-2"
    >
    <div
        v-if="showFlash"
        class="relative mb-4 p-4 mx-auto rounded-xl flex justify-between items-center"
        :class="flash.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
    >
        <div class="pr-8">
        {{ flash.success || flash.error }}
        </div>

        <!-- Close Button -->
        <button
        @click="showFlash = false"
        class="absolute top-2 right-3 text-lg text-current hover:opacity-70"
        aria-label="Close"
        >
        X
        </button>
    </div>
    </transition>


    <!-- Header -->
    <div
      class="flex flex-col m-4 sm:flex-row justify-between items-start sm:items-center bg-[#0B2B40] text-white rounded-xl p-5 shadow-md mb-6"
    >
      <div>
        <h1 class="text-2xl font-semibold mb-1">Loan Products</h1>
        <p class="text-sm opacity-90">
          Manage and view all available loan products.
        </p>
      </div>
      <Link
        :href="route('loan-products.create')"
        class="mt-4 sm:mt-0 bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-lg font-medium transition"
      >
        + Add New Product
      </Link>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-xl m-4 shadow-sm overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-blue-50">
          <tr>
            <th
              v-for="header in headers"
              :key="header"
              class="px-6 py-3 text-left text-xs font-semibold text-[#0B2B40] uppercase tracking-wider"
            >
              {{ header }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="(product, index) in loanProducts"
            :key="product.id"
            class="hover:bg-blue-50 transition"
          >
            <td class="px-6 py-4 text-sm text-gray-800">{{ index + 1 }}</td>
            <td class="px-6 py-4 text-sm text-gray-800 font-medium">
              {{ product.name }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ product.code }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">
              {{ product.interest_rate }}%
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">
              {{ product.min_amount.toLocaleString() }} - {{ product.max_amount.toLocaleString() }}
            </td>
            <td class="px-6 py-4 text-sm">
              <span
                class="px-3 py-1 rounded-full text-xs font-semibold"
                :class="product.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
              >
                {{ product.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600 space-x-2 flex">
              <Link
                :href="route('loan-products.show', product.id)"
                class="text-blue-600 hover:text-blue-800 transition"
              >
                View
              </Link>
              <span>|</span>
              <Link
                :href="route('loan-products.edit', product.id)"
                class="text-orange-600 hover:text-orange-800 transition"
              >
                Edit
              </Link>
              <span>|</span>
              <button
                @click="toggleStatus(product)"
                class="text-yellow-600 hover:text-yellow-800 transition"
              >
                {{ product.is_active ? 'Deactivate' : 'Activate' }}
              </button>
              <span>|</span>
              <button
                @click="confirmDelete(product)"
                class="text-red-600 hover:text-red-800 transition"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div
        v-if="loanProducts.length === 0"
        class="p-8 text-center text-gray-500 italic"
      >
        No loan products found.
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <transition name="fade">
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50"
      >
        <div
          class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4 text-center"
        >
          <h3 class="text-lg font-semibold text-[#0B2B40]">
            Confirm Delete
          </h3>
          <p class="text-gray-600">
            Are you sure you want to delete <strong>{{ selectedProduct?.name }}</strong>?
          </p>
          <div class="flex justify-center space-x-4 mt-4">
            <button
              @click="deleteProduct"
              class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg font-medium"
            >
              Yes, Delete
            </button>
            <button
              @click="showDeleteModal = false"
              class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2 rounded-lg font-medium"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </transition>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";

const props = defineProps({
  loanProducts: Array,
});

const page = usePage();
const flash = page.props.flash;
const showDeleteModal = ref(false);
const selectedProduct = ref<any>(null);
const showFlash = ref(!!(flash.success || flash.error));

const headers = [
  "#",
  "Name",
  "Code",
  "Interest Rate",
  "Range (Ksh)",
  "Status",
  "Actions",
];

onMounted(() => {
  if (flash.success || flash.error) {
    showFlash.value = true;

    setTimeout(() => {
      showFlash.value = false;
      flash.success = null;
      flash.error = null;
    }, 3000);
  }
});

watch(
  () => page.props.flash,
  (newFlash) => {
    if (newFlash.success || newFlash.error) {
      showFlash.value = true;
      setTimeout(() => {
        showFlash.value = false;
        newFlash.success = null;
        newFlash.error = null;
      }, 5000);
    }
  },
  { deep: true, immediate: true }
);


function confirmDelete(product: any) {
  selectedProduct.value = product;
  showDeleteModal.value = true;
}

function deleteProduct() {
  if (!selectedProduct.value) return;
  router.delete(route("loan-products.destroy", selectedProduct.value.id), {
    onFinish: () => (showDeleteModal.value = false),
  });
}

function toggleStatus(product: any) {
  router.post(route("loan-products.toggleStatus", product.id));
}
</script>


<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
