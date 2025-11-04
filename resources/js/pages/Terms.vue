<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Users, CreditCard, Shield, RefreshCcw, FileWarning, Phone } from 'lucide-vue-next'

// Terms data
const sections = [
  {
    title: '1. Membership Eligibility',
    icon: Users,
    intro:
      'Membership is open to individuals who meet the SACCO’s eligibility criteria. All members must:',
    points: [
      'Provide accurate personal and financial details during registration.',
      'Maintain only one active membership account.',
      'Promptly update any changes in their personal information.',
    ],
  },
  {
    title: '2. Savings and Loan Policies',
    icon: CreditCard,
    intro:
      'Members agree to comply with SACCO policies regarding savings contributions and loan management. This includes:',
    points: [
      'Making regular contributions as per agreed schedules.',
      'Submitting truthful loan applications.',
      'Repaying loans on time to avoid penalties or suspension.',
      'Accepting SACCO’s right to recover overdue loans through guarantors or other means.',
    ],
  },
  {
    title: '3. Data Privacy & Security',
    icon: Shield,
    intro: 'SEPU SACCO values your privacy. We commit to:',
    points: [
      'Safeguarding your personal and financial information.',
      'Not sharing data with third parties without your consent, except where required by law.',
      'Using secure systems to protect against unauthorized access.',
    ],
  },
  {
    title: '4. Amendments to Terms',
    icon: RefreshCcw,
    intro:
      'We may revise these terms to reflect policy changes, legal requirements, or service improvements. Continued use constitutes acceptance of new terms.',
  },
  {
    title: '5. Termination & Disputes',
    icon: FileWarning,
    intro:
      'SEPU SACCO reserves the right to suspend or terminate membership in cases of:',
    points: [
      'Fraudulent activities or misrepresentation.',
      'Persistent default on loan obligations.',
      'Violation of SACCO’s by-laws and policies.',
      'Disputes will first be resolved internally; unresolved cases may be referred to the relevant cooperative tribunal.',
    ],
  },
]
</script>

<template>
  <PublicLayout>
    <Head title="Terms of Service" />

    <div class="max-w-5xl mx-auto px-3 sm:px-6 py-5 sm:py-10 space-y-10">

      <!-- Header -->
      <div class="text-center bg-gradient-to-r from-blue-900 to-blue-800 p-8 rounded-3xl shadow-lg text-white">
        <h1 class="text-3xl sm:text-4xl font-extrabold mb-2">Terms of Service</h1>
        <p class="text-sm sm:text-base text-gray-200 max-w-3xl mx-auto">
          These terms govern your use of SEPU SACCO’s services. Please review them carefully
          to understand your rights and obligations as a member.
        </p>
      </div>

      <!-- Introduction -->
      <section class="bg-white border border-gray-100 shadow-md rounded-2xl p-8 leading-relaxed text-gray-700">
        <p class="text-base sm:text-lg">
          By joining SEPU SACCO and using our services, you agree to abide by the following
          terms and conditions. These are designed to protect both our members and the SACCO as a whole.
          If you do not agree, you may choose not to use our services.
        </p>
      </section>

      <!-- Terms Sections -->
      <div class="space-y-10">
        <section v-for="(item, index) in sections" :key="index" class="bg-white shadow-md rounded-2xl p-8 border border-gray-100">
          <div class="flex items-center gap-3 mb-4">
            <component :is="item.icon" class="w-6 h-6 text-orange-500" />
            <h2 class="text-xl font-semibold text-blue-900">{{ item.title }}</h2>
          </div>
          <p class="text-gray-700 text-sm sm:text-base leading-relaxed mb-3">{{ item.intro }}</p>
          <ul v-if="item.points" class="list-disc ml-6 space-y-1 text-gray-600 text-sm sm:text-base">
            <li v-for="(point, i) in item.points" :key="i">{{ point }}</li>
          </ul>
        </section>
      </div>

      <!-- Contact Section -->
      <section class="bg-gradient-to-r from-blue-900 to-blue-800 text-white rounded-3xl shadow-lg p-10 space-y-4">
        <div class="flex items-center justify-center gap-2">
          <Phone class="w-6 h-6 text-orange-400" />
          <h2 class="text-2xl font-bold">Contact Information</h2>
        </div>
        <p class="max-w-2xl mx-auto text-base text-gray-200">
          For any questions or clarifications regarding these terms, please reach out to us:
        </p>
        <ul class="text-sm sm:text-base space-y-2">
          <li>
            Email:
            <a href="mailto:support@sepusacco.org" class="underline text-orange-400 hover:text-orange-300">
              support@sepusacco.co.ke
            </a>
          </li>
          <li>Phone: +254 712 345 678</li>
          <li>Office: SEPU SACCO HQ, Nairobi, Kenya</li>
        </ul>
      </section>

      <!-- Footer Note -->
      <p class="text-xs text-center text-gray-500 italic mt-6">
        Last updated: July 2025
      </p>

    </div>
  </PublicLayout>
</template>
