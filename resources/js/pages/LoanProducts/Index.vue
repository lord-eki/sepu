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
                class="relative mx-auto mb-4 flex items-center justify-between rounded-xl p-4"
                :class="flash.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
            >
                <div class="pr-8">
                    {{ flash.success || flash.error }}
                </div>

                <!-- Close Button -->
                <button @click="showFlash = false" class="absolute top-2 right-3 text-lg text-current hover:opacity-70" aria-label="Close">X</button>
            </div>
        </transition>

        <!-- Header -->
        <div class="m-4 mb-6 flex flex-col items-start justify-between rounded-xl bg-blue-950 p-5 text-white shadow-md sm:flex-row sm:items-center">
            <div>
                <h1 class="mb-1 text-xl font-semibold">Loan Products</h1>
                <p class="text-sm opacity-90">Manage and view all available loan products.</p>
            </div>
            <Link
                :href="route('loan-products.create')"
                class="mt-4 rounded-lg bg-orange-500 px-5 py-2 font-medium text-white transition hover:bg-orange-600 max-sm:text-sm sm:mt-0"
            >
                + Add New Product
            </Link>
        </div>

        <!-- Table Section -->
        <div class="m-4 overflow-hidden overflow-x-auto rounded-xl bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-50">
                    <tr>
                        <th
                            v-for="header in headers"
                            :key="header"
                            class="px-6 py-3 text-left text-xs font-semibold tracking-wider text-[#0B2B40] uppercase"
                        >
                            {{ header }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="(product, index) in loanProducts" :key="product.id" class="transition hover:bg-blue-50">
                        <td class="px-6 py-4 text-sm text-gray-800">{{ index + 1 }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-800">
                            {{ product.name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ product.code }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ product.interest_rate }}%</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ product.min_amount.toLocaleString() }} - {{ product.max_amount.toLocaleString() }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                :class="product.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                            >
                                {{ product.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="flex space-x-2 px-6 py-4 text-sm text-gray-600">
                            <Link :href="route('loan-products.show', product.id)" class="text-blue-600 transition hover:text-blue-800"> View </Link>
                            <span>|</span>
                            <Link
                                :href="route('loan-products.edit', product.id)"
                                class="text-orange-600 transition hover:cursor-pointer hover:text-orange-800"
                            >
                                Edit
                            </Link>
                            <span>|</span>
                            <button @click="toggleStatus(product)" class="text-yellow-600 transition hover:cursor-pointer hover:text-yellow-800">
                                {{ product.is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                            <span>|</span>
                            <button @click="confirmDelete(product)" class="text-red-600 transition hover:cursor-pointer hover:text-red-800">
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="loanProducts.length === 0" class="p-8 text-center text-gray-500 italic">No loan products found.</div>
        </div>

        <!-- Status Confirmation Modal -->
        <transition name="fade">
            <div v-if="showStatusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 max-sm:px-2">
                <div class="w-full max-w-md space-y-6 rounded-xl bg-white py-8 text-center shadow-lg">
                    <h3 class="text-lg font-semibold text-[#0B2B40]">Confirm {{ statusAction }}</h3>
                    <p class="text-gray-600">
                        Are you sure you want to {{ statusAction.toLowerCase() }} <strong>{{ statusProduct?.name }}</strong
                        >?
                    </p>
                    <div class="mt-4 flex justify-center space-x-8">
                        <button
                            @click="confirmStatusChange"
                            class="flex items-center justify-center gap-2 rounded-lg bg-yellow-600 px-5 py-2 font-medium text-white hover:bg-yellow-700"
                            :disabled="statusProcessing"
                        >
                            <span
                                v-if="statusProcessing"
                                class="loader-border h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"
                            ></span>
                            <span>
                                {{ statusProcessing ? (statusAction === 'Activate' ? 'Activating...' : 'Deactivating...') : 'Yes, ' + statusAction }}
                            </span>
                        </button>

                        <button @click="showStatusModal = false" class="rounded-lg bg-gray-200 px-5 py-2 font-medium text-gray-800 hover:bg-gray-300">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Delete Confirmation Modal -->
        <transition name="fade">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 max-sm:px-2">
                <div class="w-full max-w-md space-y-6 rounded-xl bg-white py-8 text-center shadow-lg">
                    <h3 class="text-lg font-semibold text-[#0B2B40]">Confirm Delete</h3>
                    <p class="text-gray-600">
                        Are you sure you want to delete <strong>{{ selectedProduct?.name }}</strong
                        >?
                    </p>
                    <div class="mt-4 flex justify-center space-x-8">
                        <button
                            @click="deleteProduct"
                            class="flex items-center justify-center gap-2 rounded-lg bg-red-600 px-5 py-2 font-medium text-white hover:cursor-pointer hover:bg-red-700"
                            :disabled="deleting"
                        >
                            <span
                                v-if="deleting"
                                class="loader-border h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"
                            ></span>
                            <span>{{ deleting ? 'Deleting...' : 'Yes, Delete' }}</span>
                        </button>

                        <button
                            @click="showDeleteModal = false"
                            class="rounded-lg bg-gray-200 px-5 py-2 font-medium text-gray-800 hover:cursor-pointer hover:bg-gray-300"
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
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
    loanProducts: Array,
});

const page = usePage();
const flash = page.props.flash;
const showDeleteModal = ref(false);
const selectedProduct = ref<any>(null);
const showFlash = ref(!!(flash.success || flash.error));
const deleting = ref(false);

const showStatusModal = ref(false);
const statusProduct = ref<any>(null);
const statusAction = ref(''); // 'Activate' or 'Deactivate'
const statusProcessing = ref(false);

const headers = ['#', 'Name', 'Code', 'Interest Rate', 'Range (Ksh)', 'Status', 'Actions'];

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

function showFlashMsg(message: string, isError = false) {
    flash.success = isError ? '' : message;
    flash.error = isError ? message : '';
    showFlash.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => (showFlash.value = false), 4000);
}

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
    { deep: true, immediate: true },
);

function confirmDelete(product: any) {
    selectedProduct.value = product;
    showDeleteModal.value = true;
}

function deleteProduct() {
    if (!selectedProduct.value) return;
    deleting.value = true;

    router.delete(route('loan-products.destroy', selectedProduct.value.id), {
        onSuccess: (page) => {
            showFlashMsg(page.props.flash.success || page.props.flash.error, !!page.props.flash.error);
            // remove deleted product from the list immediately
            const index = props.loanProducts.findIndex((p) => p.id === selectedProduct.value.id);
            if (index !== -1) props.loanProducts.splice(index, 1);
        },
        onError: () => showFlashMsg('Failed to delete product. Please try again.', true),
        onFinish: () => {
            deleting.value = false;
            showDeleteModal.value = false;
        },
    });
}

function toggleStatus(product: any) {
    statusProduct.value = product;
    statusAction.value = product.is_active ? 'Deactivate' : 'Activate';
    showStatusModal.value = true;
}

function confirmStatusChange() {
    if (!statusProduct.value) return;
    statusProcessing.value = true;

    router.post(
        route('loan-products.toggle-status', { loanProduct: statusProduct.value.id }),
        {},
        {
            preserveState: true,
            onSuccess: (page) => {
                const message = page.props.flash.success || page.props.flash.error || 'Action completed';
                const isError = !!page.props.flash.error;
                showFlashMsg(message, isError);

                // update UI immediately
                statusProduct.value.is_active = !statusProduct.value.is_active;
            },
            onError: () => showFlashMsg('Error updating status.', true),
            onFinish: () => {
                statusProcessing.value = false;
                showStatusModal.value = false;
                statusProduct.value = null;
                statusAction.value = '';
            },
        },
    );
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
