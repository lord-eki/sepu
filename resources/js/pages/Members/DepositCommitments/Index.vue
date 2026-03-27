<template>
  <AppLayout
    :breadcrumbs="[
      { title: 'Members', href: route('members.index') },
      { title: 'Finance setup' }
    ]"
  >
    <Head title="Deposit Commitments" />

    <div class="p-6 space-y-6 w-full">

      <!-- HEADER -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">
            Deposit Commitments
          </h1>
          <p class="text-gray-500 text-sm">
            {{ member.first_name }} {{ member.last_name }}
          </p>
        </div>

        <button
          @click="createNew"
          class="bg-[#0a2342] hover:bg-[#06172c] text-white px-5 py-2.5 rounded-xl shadow"
        >
          + New Commitment
        </button>
      </div>

      <!-- TABLE -->
      <div class="bg-white shadow-xl rounded-2xl overflow-hidden border">

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
              <tr>
                <th class="px-4 py-3 text-left">Account</th>
                <th class="px-4 py-3 text-left">Type</th>
                <th class="px-4 py-3 text-left">Amount</th>
                <th class="px-4 py-3 text-left">Day</th>
                <th class="px-4 py-3 text-left">From</th>
                <th class="px-4 py-3 text-left">To</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="c in commitments"
                :key="c.id"
                class="border-t hover:bg-gray-50 transition"
              >
                <td class="px-4 py-3 font-medium text-gray-800">
                  {{ c.account_number || '-' }}
                </td>

                <td class="px-4 py-3">{{ c.account_type }}</td>

                <td class="px-4 py-3 font-semibold text-[#0a2342]">
                  KES {{ Number(c.monthly_amount).toLocaleString() }}
                </td>

                <td class="px-4 py-3">{{ c.deduction_day }}</td>

                <td class="px-4 py-3">{{ c.effective_from }}</td>

                <td class="px-4 py-3">{{ c.effective_to || '-' }}</td>

                <td class="px-4 py-3">
                  <span
                    class="px-3 py-1 rounded-full text-xs font-semibold"
                    :class="c.is_active
                      ? 'bg-green-100 text-green-700'
                      : 'bg-gray-200 text-gray-500'"
                  >
                    {{ c.is_active ? 'Active' : 'Paused' }}
                  </span>
                </td>

                <td class="px-4 py-3 text-right space-x-2">
                  <button @click="editItem(c)" class="text-blue-600 hover:underline">
                    Edit
                  </button>

                  <button @click="toggle(c)" class="text-yellow-600 hover:underline">
                    Toggle
                  </button>

                  <button @click="deleteItem(c)" class="text-red-600 hover:underline">
                    Delete
                  </button>
                </td>
              </tr>

              <tr v-if="commitments.length === 0">
                <td colspan="8" class="text-center py-6 text-gray-500">
                  No commitments found
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- MODAL -->
      <div v-if="showForm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl p-6 space-y-5">

          <h2 class="text-xl font-bold text-gray-800">
            {{ editing ? 'Edit Commitment' : 'New Commitment' }}
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- ACCOUNT -->
            <div>
              <label class="text-sm text-gray-600">Savings Account</label>
              <select v-model="form.account_id" class="w-full border rounded-lg p-2 mt-1">
                <option value="">Select Account</option>
                <option v-for="a in savingsAccounts" :key="a.id" :value="a.id">
                  {{ a.account_number }} ({{ a.account_type }})
                </option>
              </select>
            </div>

            <!-- TYPE -->
            <div>
              <label class="text-sm text-gray-600">Account Type</label>
              <select v-model="form.account_type" class="w-full border rounded-lg p-2 mt-1">
                <option value="">Select Type</option>
                <option v-for="(label, key) in accountTypes" :key="key" :value="key">
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- AMOUNT -->
            <div>
              <label class="text-sm text-gray-600">Monthly Amount</label>
              <input v-model="form.monthly_amount" type="number"
                class="w-full border rounded-lg p-2 mt-1" />
            </div>

            <!-- DAY -->
            <div>
              <label class="text-sm text-gray-600">Deduction Day</label>
              <input v-model="form.deduction_day" type="number" min="1" max="28"
                class="w-full border rounded-lg p-2 mt-1" />
            </div>

            <!-- FROM -->
            <div>
              <label class="text-sm text-gray-600">Effective From</label>
              <input v-model="form.effective_from" type="date"
                class="w-full border rounded-lg p-2 mt-1" />
            </div>

            <!-- TO -->
            <div>
              <label class="text-sm text-gray-600">Effective To</label>
              <input v-model="form.effective_to" type="date"
                class="w-full border rounded-lg p-2 mt-1" />
            </div>

          </div>

          <!-- NOTES -->
          <div>
            <label class="text-sm text-gray-600">Notes</label>
            <textarea v-model="form.notes"
              class="w-full border rounded-lg p-2 mt-1"
              rows="3"></textarea>
          </div>

          <!-- ACTIONS -->
          <div class="flex justify-end gap-3 pt-3 border-t">
            <button
              @click="showForm = false"
              class="px-4 py-2 rounded-lg border hover:bg-gray-100"
            >
              Cancel
            </button>

            <button
              @click="submit"
              class="bg-[#0a2342] hover:bg-[#06172c] text-white px-5 py-2 rounded-lg shadow"
            >
              Save Commitment
            </button>
          </div>

        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from "vue"
import { Head, router } from "@inertiajs/vue3"
import AppLayout from "@/layouts/AppLayout.vue";

const props = defineProps({
  member: Object,
  commitments: Array,
  savingsAccounts: Array,
  accountTypes: Object,
})

const showForm = ref(false)
const editing = ref(false)

const form = ref({
  id: null,
  account_id: "",
  account_type: "",
  monthly_amount: "",
  deduction_day: "",
  effective_from: "",
  effective_to: "",
  notes: "",
})

const createNew = () => {
  editing.value = false
  form.value = {
    id: null,
    account_id: "",
    account_type: "",
    monthly_amount: "",
    deduction_day: "",
    effective_from: "",
    effective_to: "",
    notes: "",
  }
  showForm.value = true
}

const editItem = (c) => {
  editing.value = true
  form.value = { ...c }
  showForm.value = true
}

const submit = () => {
  if (editing.value) {
    router.put(
      route("members.deposit-commitments.update", {
        member: props.member.id,
        commitment: form.value.id,
      }),
      form.value,
      { onSuccess: () => (showForm.value = false) }
    )
  } else {
    router.post(
      route("members.deposit-commitments.store", props.member.id),
      form.value,
      { onSuccess: () => (showForm.value = false) }
    )
  }
}

const deleteItem = (c) => {
  if (!confirm("Delete commitment?")) return

  router.delete(
    route("members.deposit-commitments.destroy", {
      member: props.member.id,
      commitment: c.id,
    })
  )
}

const toggle = (c) => {
  router.post(
    route("members.deposit-commitments.toggle", {
      member: props.member.id,
      commitment: c.id,
    })
  )
}
</script>