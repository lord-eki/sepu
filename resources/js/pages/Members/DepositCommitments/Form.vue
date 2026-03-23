<script setup lang="ts">
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  member: Object,
  editing: Object,
  accounts: Array,
  accountTypes: Object,
})

const emit = defineEmits(['close'])

const form = ref({
  account_id: '',
  account_type: '',
  monthly_amount: '',
  deduction_day: 1,
  effective_from: '',
  effective_to: '',
  notes: '',
  is_active: true,
})

watch(() => props.editing, (val) => {
  if (val) {
    form.value = { ...val }
  }
}, { immediate: true })

function submit() {
  if (props.editing) {
    router.put(
      route('members.deposit-commitments.update', [
        props.member.id,
        props.editing.id
      ]),
      form.value,
      { onSuccess: () => emit('close') }
    )
  } else {
    router.post(
      route('members.deposit-commitments.store', props.member.id),
      form.value,
      { onSuccess: () => emit('close') }
    )
  }
}
</script>

<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-full max-w-lg space-y-4">
      <h2 class="text-lg font-bold">
        {{ editing ? 'Edit Commitment' : 'New Commitment' }}
      </h2>

      <!-- Account -->
      <select v-model="form.account_id" class="w-full border p-2">
        <option value="">Select Account</option>
        <option v-for="a in accounts" :key="a.id" :value="a.id">
          {{ a.account_number }} ({{ a.account_type }})
        </option>
      </select>

      <!-- Account Type -->
      <select v-model="form.account_type" class="w-full border p-2">
        <option value="">Select Type</option>
        <option
          v-for="(label, key) in accountTypes"
          :key="key"
          :value="key"
        >
          {{ label }}
        </option>
      </select>

      <!-- Amount -->
      <input
        v-model="form.monthly_amount"
        type="number"
        placeholder="Amount"
        class="w-full border p-2"
      />

      <!-- Deduction Day -->
      <input
        v-model="form.deduction_day"
        type="number"
        min="1"
        max="28"
        class="w-full border p-2"
      />

      <!-- Dates -->
      <input v-model="form.effective_from" type="date" class="w-full border p-2" />
      <input v-model="form.effective_to" type="date" class="w-full border p-2" />

      <!-- Notes -->
      <textarea
        v-model="form.notes"
        placeholder="Notes"
        class="w-full border p-2"
      ></textarea>

      <!-- Actions -->
      <div class="flex justify-end space-x-2">
        <button @click="emit('close')" class="px-4 py-2 border">
          Cancel
        </button>

        <button
          @click="submit"
          class="px-4 py-2 bg-blue-600 text-white"
        >
          Save
        </button>
      </div>
    </div>
  </div>
</template>