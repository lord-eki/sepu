<template>
    <div>

        <Head :title="`Budget Utilization - ${budget.title}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">
                                    Budget Utilization Analysis
                                </h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ budget.title }} ({{ budget.budget_year }})
                                </p>
                            </div>
                            <div class="flex space-x-3">
                                <Link :href="route('budgets.show', budget.id)"
                                    class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">
                                Back to Budget
                                </Link>
                                <Link :href="route('budgets.variance', budget.id)"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                View Variance
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Utilization Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Budget
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            KES {{ formatCurrency(utilization.total_budgeted) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Spent
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            KES {{ formatCurrency(utilization.total_spent) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-orange-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Remaining
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            KES {{ formatCurrency(utilization.total_remaining) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Utilization %
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ utilization.utilization_percentage.toFixed(1) }}%
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Utilization Progress Bar -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Overall Budget Utilization</h3>
                        <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                            <div class="h-4 rounded-full transition-all duration-500"
                                :class="getUtilizationColorClass(utilization.utilization_percentage)"
                                :style="{ width: Math.min(utilization.utilization_percentage, 100) + '%' }"></div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>0%</span>
                            <span class="font-medium">{{ utilization.utilization_percentage.toFixed(1) }}%
                                utilized</span>
                            <span>100%</span>
                        </div>
                    </div>
                </div>

                <!-- Category Utilization -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Category-wise Utilization</h3>
                        <div class="space-y-6">
                            <div v-for="category in category_utilization" :key="category.category"
                                class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        <h4 class="text-md font-medium text-gray-900">
                                            {{ category.category }}
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            {{ category.items_count }} item(s)
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ category.utilization_percentage.toFixed(1) }}%
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            KES {{ formatCurrency(category.spent) }} / {{
                                            formatCurrency(category.budgeted) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                    <div class="h-3 rounded-full transition-all duration-500"
                                        :class="getUtilizationColorClass(category.utilization_percentage)"
                                        :style="{ width: Math.min(category.utilization_percentage, 100) + '%' }"></div>
                                </div>

                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>Remaining: KES {{ formatCurrency(category.remaining) }}</span>
                                    <span :class="getUtilizationTextColorClass(category.utilization_percentage)"
                                        class="font-medium">
                                        {{ getUtilizationStatus(category.utilization_percentage) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Spending Trends Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6"
                    v-if="spending_trends && spending_trends.length > 0">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Monthly Spending Trends</h3>
                        <div class="h-64">
                            <canvas ref="spendingChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Budget Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-indigo-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Budget Items
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ utilization.items_count }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-pink-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Categories
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ utilization.categories_count }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Budget Year
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ budget.budget_year }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'

const props = defineProps({
    budget: Object,
    utilization: Object,
    spending_trends: Array,
    category_utilization: Array,
})

const spendingChart = ref(null)

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount || 0)
}

const getUtilizationColorClass = (percentage) => {
    if (percentage <= 50) return 'bg-green-500'
    if (percentage <= 75) return 'bg-yellow-500'
    if (percentage <= 90) return 'bg-orange-500'
    if (percentage <= 100) return 'bg-red-500'
    return 'bg-red-700'
}

const getUtilizationTextColorClass = (percentage) => {
    if (percentage <= 50) return 'text-green-600'
    if (percentage <= 75) return 'text-yellow-600'
    if (percentage <= 90) return 'text-orange-600'
    if (percentage <= 100) return 'text-red-600'
    return 'text-red-700'
}

const getUtilizationStatus = (percentage) => {
    if (percentage <= 50) return 'On Track'
    if (percentage <= 75) return 'Good Progress'
    if (percentage <= 90) return 'High Utilization'
    if (percentage <= 100) return 'Fully Utilized'
    return 'Over Budget'
}

onMounted(() => {
    if (props.spending_trends && props.spending_trends.length > 0 && spendingChart.value) {
        initializeSpendingChart()
    }
})

const initializeSpendingChart = async () => {
    try {
        // Load Chart.js from CDN if not already loaded
        if (typeof Chart === 'undefined') {
            await loadChartJS()
        }

        const ctx = spendingChart.value.getContext('2d')

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: props.spending_trends.map(trend => trend.month),
                datasets: [{
                    label: 'Monthly Spending (KES)',
                    data: props.spending_trends.map(trend => trend.total_spent),
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return 'KES ' + value.toLocaleString()
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        })
    } catch (error) {
        console.error('Error loading Chart.js:', error)
    }
}

const loadChartJS = () => {
    return new Promise((resolve, reject) => {
        if (typeof Chart !== 'undefined') {
            resolve()
            return
        }

        const script = document.createElement('script')
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js'
        script.onload = () => resolve()
        script.onerror = () => reject(new Error('Failed to load Chart.js'))
        document.head.appendChild(script)
    })
}
</script>