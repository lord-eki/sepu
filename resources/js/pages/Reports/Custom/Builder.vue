<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { Database, Filter, Play } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    data_sources: Record<string, any>;
    templates: Record<string, any>;
    saved_reports: Array<any>;
}>();

/* ----------------------------------------
   STATE
---------------------------------------- */

const reportName = ref('');
const selectedSources = ref<string[]>([]);
const selectedFields = ref<string[]>([]);
const groupBy = ref('');
const sortBy = ref('');
const sortDirection = ref('asc');
const saveReport = ref(false);
const results = ref<any[]>([]);
const loading = ref(false);

/* ----------------------------------------
   COMPUTED
---------------------------------------- */

const availableFields = computed(() => {
    const fields: string[] = [];
    selectedSources.value.forEach((source) => {
        props.data_sources[source]?.fields?.forEach((f: string) => {
            fields.push(`${source}.${f}`);
        });
    });
    return fields;
});

/* ----------------------------------------
   METHODS
---------------------------------------- */

const toggleSource = (key: string) => {
    if (selectedSources.value.includes(key)) {
        selectedSources.value = selectedSources.value.filter((s) => s !== key);
    } else {
        selectedSources.value.push(key);
    }
};

const toggleField = (field: string) => {
    if (selectedFields.value.includes(field)) {
        selectedFields.value = selectedFields.value.filter((f) => f !== field);
    } else {
        selectedFields.value.push(field);
    }
};

const generateReport = async () => {
    loading.value = true;
    results.value = [];

    try {
        const response = await axios.post(route('reports.custom.generate'), {
            report_name: reportName.value,
            data_sources: selectedSources.value,
            selected_fields: selectedFields.value,
            group_by: groupBy.value,
            sort_by: sortBy.value,
            sort_direction: sortDirection.value,
            save_report: saveReport.value,
            format: 'table',
        });

        if (response.data.success) {
            results.value = response.data.data;
        }
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const breadcrumbs = [{ title: 'Reports', href: route('reports.index') }, { title: 'Custom Report' }];
</script>

<template>
    <Head title="Custom Reports Builder" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-5 mt-5 max-w-7xl space-y-10">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Custom Report Builder</h1>
                    <p class="mt-1 text-gray-500 dark:text-gray-400">Build dynamic reports from multiple data sources</p>
                </div>

                <button
                    @click="generateReport"
                    :disabled="loading"
                    class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-900 to-blue-600 px-5 py-2.5 text-white shadow-lg transition hover:shadow-xl disabled:opacity-50"
                >
                    <Play class="h-4 w-4" />
                    {{ loading ? 'Generating...' : 'Generate Report' }}
                </button>
            </div>

            <!-- Templates -->
            <div>
                <h2 class="mb-4 text-sm font-semibold tracking-wider text-gray-500 uppercase">Templates</h2>

                <div class="grid gap-5 md:grid-cols-4">
                    <div
                        v-for="(template, key) in templates"
                        :key="key"
                        class="group cursor-pointer rounded-2xl border bg-white p-6 transition-all duration-300 hover:shadow-xl dark:bg-gray-900"
                        @click="selectedSources = template.data_sources"
                    >
                        <h3 class="font-semibold text-gray-900 dark:text-white">
                            {{ template.name }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            {{ template.description }}
                        </p>

                        <div class="mt-4 text-xs font-medium text-blue-900 opacity-0 transition group-hover:opacity-100">Use Template →</div>
                    </div>
                </div>
            </div>

            <!-- Builder Grid -->
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Data Sources -->
                <div class="space-y-4 rounded-2xl border bg-white p-6 shadow-sm lg:col-span-3 dark:bg-gray-900">
                    <h3 class="flex items-center gap-2 font-semibold text-gray-800 dark:text-gray-200">
                        <Database class="h-4 w-4" />
                        Data Sources
                    </h3>

                    <div class="space-y-3">
                        <label
                            v-for="(source, key) in data_sources"
                            :key="key"
                            class="flex cursor-pointer items-center justify-between rounded-xl p-3 transition hover:bg-gray-50 dark:hover:bg-gray-800"
                        >
                            <div class="flex items-center gap-3">
                                <input type="checkbox" :value="key" v-model="selectedSources" class="rounded text-blue-900" />
                                <span class="text-sm font-medium">
                                    {{ source.name }}
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Fields -->
                <div class="rounded-2xl border bg-white p-6 shadow-sm lg:col-span-4 dark:bg-gray-900">
                    <h3 class="mb-4 font-semibold text-gray-800 dark:text-gray-200">Fields</h3>

                    <div class="flex max-h-80 flex-wrap gap-2 overflow-y-auto">
                        <button
                            v-for="field in availableFields"
                            :key="field"
                            @click="toggleField(field)"
                            :class="[
                                'rounded-full border px-3 py-1.5 text-xs transition',
                                selectedFields.includes(field)
                                    ? 'border-blue-900 bg-blue-900 text-white'
                                    : 'bg-gray-50 hover:bg-gray-100 dark:bg-gray-800',
                            ]"
                        >
                            {{ field }}
                        </button>
                    </div>
                </div>

                <!-- Settings -->
                <div class="space-y-5 rounded-2xl border bg-white p-6 shadow-sm lg:col-span-5 dark:bg-gray-900">
                    <h3 class="flex items-center gap-2 font-semibold text-gray-800 dark:text-gray-200">
                        <Filter class="h-4 w-4" />
                        Report Settings
                    </h3>

                    <input
                        v-model="reportName"
                        type="text"
                        placeholder="Report Name"
                        class="w-full rounded-xl border px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:bg-gray-800"
                    />

                    <div class="grid grid-cols-2 gap-4">
                        <select v-model="groupBy" class="rounded-xl border px-4 py-2 dark:bg-gray-800">
                            <option value="">No Grouping</option>
                            <option v-for="field in selectedFields" :key="field" :value="field">
                                {{ field }}
                            </option>
                        </select>

                        <select v-model="sortBy" class="rounded-xl border px-4 py-2 dark:bg-gray-800">
                            <option value="">No Sorting</option>
                            <option v-for="field in selectedFields" :key="field" :value="field">
                                {{ field }}
                            </option>
                        </select>
                    </div>

                    <select v-model="sortDirection" class="w-full rounded-xl border px-4 py-2 dark:bg-gray-800">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>

                    <label class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                        <input type="checkbox" v-model="saveReport" class="rounded text-blue-900" />
                        Save this report
                    </label>
                </div>
            </div>

            <!-- Results -->
            <div v-if="results.length" class="overflow-hidden rounded-2xl border bg-white shadow-sm dark:bg-gray-900">
                <div class="border-b px-6 py-4 font-semibold">Results ({{ results.length }} rows)</div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    v-for="(value, key) in results[0]"
                                    :key="key"
                                    class="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300"
                                >
                                    {{ key }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, index) in results"
                                :key="index"
                                class="border-t transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
                            >
                                <td v-for="(value, key) in row" :key="key" class="px-6 py-3">
                                    {{ value }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
