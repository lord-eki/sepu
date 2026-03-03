<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Users, CreditCard, Shield, RefreshCcw, FileWarning, Phone } from 'lucide-vue-next'

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
      'Accepting SACCO’s right to recover overdue loans through guarantors or other lawful means.',
    ],
  },
  {
    title: '3. Data Privacy & Security',
    icon: Shield,
    intro: 'SEPU SACCO values your privacy. We commit to:',
    points: [
      'Safeguarding your personal and financial information.',
      'Not sharing data without your consent except where required by law.',
      'Using secure systems to prevent unauthorized access.',
    ],
  },
  {
    title: '4. Amendments to Terms',
    icon: RefreshCcw,
    intro:
      'We may revise these terms to reflect policy updates, legal requirements, or service improvements. Continued use of our services constitutes acceptance of the revised terms.',
  },
  {
    title: '5. Termination & Disputes',
    icon: FileWarning,
    intro:
      'SEPU SACCO reserves the right to suspend or terminate membership in cases of:',
    points: [
      'Fraudulent activities or misrepresentation.',
      'Persistent default on loan obligations.',
      'Violation of SACCO by-laws or policies.',
      'Unresolved disputes may be referred to the relevant cooperative tribunal.',
    ],
  },
]
</script>

<template>
  <PublicLayout>

    <Head title="Terms of Service" />

    <main
      class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">

      <!-- Soft Background Accent -->
      <div
        class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-blue-200 rounded-full blur-[120px] opacity-30 dark:bg-blue-800/30">
      </div>
      <div
        class="absolute bottom-0 -left-40 w-[500px] h-[500px] bg-orange-200 rounded-full blur-[120px] opacity-30 dark:bg-orange-700/30">
      </div>

      <div class="relative max-w-5xl mx-auto px-6 py-10 sm:py-16 lg:py-24 space-y-20">

        <!-- Header -->
        <section class="text-center space-y-6">
          <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white">
            Terms of
            <span
              class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-orange-500 dark:from-blue-400 dark:to-orange-400">
              Service
            </span>
          </h1>

          <p class="text-gray-600 sm:text-lg max-w-3xl mx-auto leading-relaxed dark:text-gray-300">
            These terms govern your use of SEPU SACCO’s services. Please review
            them carefully to understand your rights and obligations as a member.
          </p>
        </section>

        <!-- Introduction -->
        <section
          class="rounded-3xl bg-white/80 dark:bg-gray-800/60 backdrop-blur-xl
                        border border-gray-200 dark:border-gray-700 shadow-xl p-10 text-gray-600 dark:text-gray-300 leading-relaxed">
          <p>
            By joining SEPU SACCO and using our services, you agree to abide by
            the following terms and conditions. These provisions are designed to
            protect both our members and the SACCO as a whole.
            If you do not agree, you may choose not to use our services.
          </p>
        </section>

        <!-- Terms Sections -->
        <div class="space-y-10">
          <section v-for="(item, index) in sections" :key="index" class="group rounded-3xl bg-white/80 dark:bg-gray-800/60 backdrop-blur-lg
                   border border-gray-200 dark:border-gray-700 p-8 shadow-md
                   hover:shadow-xl hover:-translate-y-1
                   transition-all duration-500">
            <div class="flex items-center gap-3 mb-4">
              <component :is="item.icon" class="w-6 h-6 text-orange-500 group-hover:scale-110 transition" />
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ item.title }}
              </h2>
            </div>

            <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-4">
              {{ item.intro }}
            </p>

            <ul v-if="item.points" class="list-disc ml-6 space-y-2 text-gray-600 dark:text-gray-300">
              <li v-for="(point, i) in item.points" :key="i">
                {{ point }}
              </li>
            </ul>
          </section>
        </div>

        <!-- Contact Section -->
        <section class="relative overflow-hidden rounded-3xl
                        bg-gradient-to-r from-blue-900 to-blue-800
                        dark:from-blue-800 dark:to-blue-700
                        p-12 text-white shadow-2xl">

          <div
            class="absolute -top-20 -right-20 w-64 h-64 bg-orange-400 rounded-full blur-3xl opacity-20 dark:bg-orange-600/30">
          </div>

          <div class="relative text-center space-y-6">
            <div class="flex items-center justify-center gap-2">
              <Phone class="w-6 h-6 text-orange-400" />
              <h2 class="text-2xl font-bold">Need Clarification?</h2>
            </div>

            <p class="max-w-2xl mx-auto text-gray-200">
              If you have any questions regarding these terms,
              please contact us through the details below.
            </p>

            <div class="space-y-2 text-gray-200 text-sm sm:text-base">
              <p>
                Email:
                <a href="mailto:support@sepusacco.co.ke" class="underline text-orange-400 hover:text-orange-300">
                  support@sepusacco.co.ke
                </a>
              </p>
              <p>Phone: +254 712 345 678</p>
              <p>Office: SEPU SACCO HQ, Nairobi, Kenya</p>
            </div>
          </div>
        </section>

        <!-- Footer Note -->
        <p class="text-xs text-center text-gray-500 dark:text-gray-400 italic">
          Last updated: July 2025
        </p>

      </div>
    </main>
  </PublicLayout>
</template>